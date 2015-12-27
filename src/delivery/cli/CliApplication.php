<?php
namespace rtens\domin\delivery\cli;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\cli\fields\ArrayField;
use rtens\domin\delivery\cli\fields\BooleanField;
use rtens\domin\delivery\cli\fields\DateIntervalField;
use rtens\domin\delivery\cli\fields\DateTimeField;
use rtens\domin\delivery\cli\fields\EnumerationField;
use rtens\domin\delivery\cli\fields\FileField;
use rtens\domin\delivery\cli\fields\HtmlField;
use rtens\domin\delivery\cli\fields\IdentifierField;
use rtens\domin\delivery\cli\fields\MultiField;
use rtens\domin\delivery\cli\fields\NullableField;
use rtens\domin\delivery\cli\fields\ObjectField;
use rtens\domin\delivery\cli\fields\PrimitiveField;
use rtens\domin\delivery\cli\fields\RangeField;
use rtens\domin\delivery\cli\renderers\ArrayRenderer;
use rtens\domin\delivery\cli\renderers\BooleanRenderer;
use rtens\domin\delivery\cli\renderers\ChartRenderer;
use rtens\domin\delivery\cli\renderers\DateIntervalRenderer;
use rtens\domin\delivery\cli\renderers\DateTimeRenderer;
use rtens\domin\delivery\cli\renderers\DelayedOutputRenderer;
use rtens\domin\delivery\cli\renderers\FileRenderer;
use rtens\domin\delivery\cli\renderers\HtmlRenderer;
use rtens\domin\delivery\cli\renderers\IdentifierRenderer;
use rtens\domin\delivery\cli\renderers\ObjectRenderer;
use rtens\domin\delivery\cli\renderers\PrimitiveRenderer;
use rtens\domin\delivery\cli\renderers\tables\DataTableRenderer;
use rtens\domin\delivery\cli\renderers\tables\ObjectTableRenderer;
use rtens\domin\delivery\cli\renderers\tables\TableRenderer;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\execution\ExecutionResult;
use rtens\domin\execution\FailedResult;
use rtens\domin\execution\MissingParametersResult;
use rtens\domin\execution\NoResult;
use rtens\domin\execution\NotPermittedResult;
use rtens\domin\execution\RedirectResult;
use rtens\domin\execution\ValueResult;
use rtens\domin\Executor;
use rtens\domin\reflection\CommentParser;
use rtens\domin\reflection\types\TypeFactory;
use watoki\factory\Factory;
use watoki\reflect\ValuePrinter;

class CliApplication {

    const OK = 0;
    const ERROR = 1;

    /** @var Factory */
    public $factory;

    /** @var ActionRegistry */
    public $actions;

    /** @var FieldRegistry */
    public $fields;

    /** @var RendererRegistry */
    public $renderers;

    /** @var TypeFactory */
    public $types;

    /** @var CommentParser */
    public $parser;

    /**
     * @param Factory $factory <-
     * @param ActionRegistry $actions <-
     * @param FieldRegistry $fields <-
     * @param RendererRegistry $renderers <-
     * @param TypeFactory $types <-
     * @param CommentParser $parser <-
     */
    public function __construct(Factory $factory, ActionRegistry $actions, FieldRegistry $fields,
                                RendererRegistry $renderers, TypeFactory $types, CommentParser $parser) {
        $this->factory = $factory;

        $this->actions = $actions;
        $this->fields = $fields;
        $this->renderers = $renderers;
        $this->types = $types;
        $this->parser = $parser;
    }

    /**
     * @param callable $callback Receives the CliApplication instance
     * @param null|Factory $factory
     * @return Factory
     */
    public static function init(callable $callback, Factory $factory = null) {
        $factory = $factory ?: new Factory();
        $callback($factory->setSingleton($factory->getInstance(self::class)));
        return $factory;
    }

    public static function run(Factory $factory, Console $console = null) {
        global $argv;

        /** @var self $app */
        $app = $factory->getInstance(self::class);
        return $app->doRun($console ?: new Console($argv));
    }

    private function doRun(Console $console) {
        if ($console->getArguments()) {
            if ($console->getArguments()[0] == '!') {
                $actionId = $this->selectAction($console);
                $reader = new InteractiveCliParameterReader($this->fields, $console);

                $this->printActionHeader($console, $actionId);
            } else {
                $actionId = $console->getArguments()[0];
                $reader = new CliParameterReader($console);
            }
        } else {
            $this->printUsage($console);
            $this->printActions($console);
            return self::OK;
        }

        $this->registerFields($reader);
        $this->registerRenderers();

        $executor = new Executor($this->actions, $this->fields, $reader);
        return $this->printResult($console, $executor->execute($actionId));
    }

    private function printResult(Console $console, ExecutionResult $result) {
        if ($result instanceof ValueResult) {
            $value = $result->getValue();
            $console->writeLine((string)$this->renderers->getRenderer($value)->render($value));
            return self::OK;
        } else if ($result instanceof MissingParametersResult) {
            $console->writeLine();
            $console->writeLine("Missing parameters!");
            foreach ($result->getMissingNames() as $missing) {
                $console->writeLine('  ' . $missing . ': ' . $result->getException($missing)->getMessage());
            }
            return self::ERROR;
        } else if ($result instanceof NotPermittedResult) {
            $console->writeLine('Permission denied');
            return self::ERROR;
        } else if ($result instanceof FailedResult) {
            $console->writeLine("Error: " . $result->getMessage());

            $exception = $result->getException();
            $console->error(
                get_class($exception) . ': ' . $exception->getMessage() . ' ' .
                '[' . $exception->getFile() . ':' . $exception->getLine() . ']' . "\n" .
                $exception->getTraceAsString()
            );
            return $exception->getCode() ?: self::ERROR;
        } else if ($result instanceof NoResult || $result instanceof RedirectResult) {
            return self::OK;
        } else {
            $console->writeLine('Cannot print [' . (new \ReflectionClass($result))->getShortName() . ']');
            return self::OK;
        }
    }

    private function selectAction(Console $console) {
        $console->writeLine();
        $console->writeLine('Available Actions');
        $console->writeLine('~~~~~~~~~~~~~~~~~');

        $i = 1;
        $actionIds = [];
        foreach ($this->actions->getAllActions() as $id => $action) {
            $console->writeLine($i++ . " - " . $action->caption() . $this->shortDescription($action));
            $actionIds[] = $id;
        }

        $console->writeLine();
        $actionIndex = $console->read('Action: ');

        return $actionIds[$actionIndex - 1];
    }

    private function printActionHeader(Console $console, $actionId) {
        $action = $this->actions->getAction($actionId);
        $console->writeLine();
        $console->writeLine($action->caption());
        $console->writeLine(str_repeat('~', strlen($action->caption())));
        $console->writeLine();

        if ($action->description()) {
            $console->writeLine($action->description());
            $console->writeLine();
        }
    }

    private function printUsage(Console $console) {
        $console->writeLine();

        $console->writeLine("Interactive mode: php {$console->getScriptName()} !");
        $console->writeLine("Execute Action:   php {$console->getScriptName()} <actionId> --<parameterName> <parameterValue> ...");
        $console->writeLine();
    }

    private function printActions(Console $console) {
        $console->writeLine('Available Actions');
        $console->writeLine('~~~~~~~~~~~~~~~~~');

        foreach ($this->actions->getAllActions() as $id => $action) {
            $console->writeLine($id . ' - ' . $action->caption() . $this->shortDescription($action));
        }
    }

    private function registerFields(ParameterReader $reader) {
        $this->fields->add(new PrimitiveField());
        $this->fields->add(new RangeField());
        $this->fields->add(new BooleanField());
        $this->fields->add(new FileField());
        $this->fields->add(new HtmlField($reader));
        $this->fields->add(new DateTimeField());
        $this->fields->add(new DateIntervalField());
        $this->fields->add(new ArrayField($this->fields, $reader));
        $this->fields->add(new NullableField($this->fields, $reader));
        $this->fields->add(new ObjectField($this->types, $this->fields, $reader));
        $this->fields->add(new MultiField($this->fields, $reader));
        $this->fields->add(new IdentifierField($this->fields));
        $this->fields->add(new EnumerationField($this->fields));
    }

    private function registerRenderers() {
        $this->renderers->add(new BooleanRenderer());
        $this->renderers->add(new PrimitiveRenderer());
        $this->renderers->add(new DateTimeRenderer());
        $this->renderers->add(new DateIntervalRenderer());
        $this->renderers->add(new HtmlRenderer());
        $this->renderers->add(new IdentifierRenderer());
        $this->renderers->add(new FileRenderer(''));
        $this->renderers->add(new DelayedOutputRenderer());
        $this->renderers->add(new ObjectTableRenderer($this->renderers));
        $this->renderers->add(new DataTableRenderer($this->renderers));
        $this->renderers->add(new TableRenderer($this->renderers));
        $this->renderers->add(new ChartRenderer($this->renderers));
        $this->renderers->add(new ArrayRenderer($this->renderers));
        $this->renderers->add(new ObjectRenderer($this->renderers, $this->types));
    }

    private function shortDescription(Action $action) {
        $description = $this->parser->shorten($action->description());
        return $description ? " ($description)" : '';
    }
}
