<?php
namespace rtens\domin\cli;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\Executor;
use rtens\domin\reflection\TypeFactory;
use watoki\factory\Factory;
use rtens\domin\cli\fields\ArrayField;
use rtens\domin\cli\fields\BooleanField;
use rtens\domin\cli\fields\DateTimeField;
use rtens\domin\cli\fields\EnumerationField;
use rtens\domin\cli\fields\FileField;
use rtens\domin\cli\fields\HtmlField;
use rtens\domin\cli\fields\IdentifierField;
use rtens\domin\cli\fields\MultiField;
use rtens\domin\cli\fields\NullableField;
use rtens\domin\cli\fields\ObjectField;
use rtens\domin\cli\fields\PrimitiveField;
use rtens\domin\cli\renderers\ArrayRenderer;
use rtens\domin\cli\renderers\BooleanRenderer;
use rtens\domin\cli\renderers\DateTimeRenderer;
use rtens\domin\cli\renderers\FileRenderer;
use rtens\domin\cli\renderers\HtmlRenderer;
use rtens\domin\cli\renderers\IdentifierRenderer;
use rtens\domin\cli\renderers\ObjectRenderer;
use rtens\domin\cli\renderers\PrimitiveRenderer;

class CliApplication {

    /** @var ActionRegistry */
    public $actions;

    /** @var FieldRegistry */
    public $fields;

    /** @var RendererRegistry */
    public $renderers;

    /** @var TypeFactory */
    public $types;

    /**
     * @param Factory $factory <-
     * @param ActionRegistry $actions <-
     * @param FieldRegistry $fields <-
     * @param RendererRegistry $renderers <-
     * @param TypeFactory $types <-
     */
    function __construct(Factory $factory, ActionRegistry $actions, FieldRegistry $fields,
                         RendererRegistry $renderers, TypeFactory $types) {
        $this->actions = $factory->setSingleton($actions);
        $this->fields = $factory->setSingleton($fields);
        $this->renderers = $factory->setSingleton($renderers);
        $this->types = $factory->setSingleton($types);
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
        /** @var self $app */
        $app = $factory->getInstance(self::class);
        $app->doRun($console ?: new Console());
    }

    private function doRun(Console $console) {
        $this->registerFields($console);
        $this->registerRenderers();

        $i = 1;
        $actionIds = [];
        foreach ($this->actions->getAllActions() as $id => $action) {
            $console->writeLine($i++ . ' - ' . $action->caption());
            $actionIds[] = $id;
        }

        $actionIndex = $console->read('Action:');

        $actionId = $actionIds[$actionIndex - 1];
        $action = $this->actions->getAction($actionId);
        $console->writeLine();
        $console->writeLine($action->caption());

        $reader = new CliParameterReader($this->fields, $console);
        $executor = new Executor($this->actions, $this->fields, $this->renderers, $reader);
        $console->write($executor->execute($actionId));
    }

    private function registerFields(Console $console) {
        $this->fields->add(new PrimitiveField());
        $this->fields->add(new BooleanField());
        $this->fields->add(new FileField());
        $this->fields->add(new HtmlField($console));
        $this->fields->add(new DateTimeField());
        $this->fields->add(new ArrayField($this->fields, $console));
        $this->fields->add(new NullableField($this->fields, $console));
        $this->fields->add(new ObjectField($this->types, $this->fields, $console));
        $this->fields->add(new MultiField($this->fields, $console));
        $this->fields->add(new IdentifierField($this->fields));
        $this->fields->add(new EnumerationField($this->fields));
    }

    private function registerRenderers() {
        $this->renderers->add(new BooleanRenderer());
        $this->renderers->add(new PrimitiveRenderer());
        $this->renderers->add(new DateTimeRenderer());
        $this->renderers->add(new HtmlRenderer());
        $this->renderers->add(new IdentifierRenderer());
        $this->renderers->add(new FileRenderer(''));
        $this->renderers->add(new ArrayRenderer($this->renderers));
        $this->renderers->add(new ObjectRenderer($this->renderers, $this->types));
    }
}