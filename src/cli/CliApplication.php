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

    private $fields;
    private $renderers;
    private $types;

    /**
     * @param FieldRegistry $fields <-
     * @param RendererRegistry $renderers <-
     * @param TypeFactory $types <-
     */
    function __construct(FieldRegistry $fields, RendererRegistry $renderers, TypeFactory $types) {
        $this->fields = $fields;
        $this->renderers = $renderers;
        $this->types = $types;
    }

    public static function run(Factory $factory, callable $read, callable $write) {
        $app = $factory->getInstance(self::class);
        $app->registerFields($read);
        $app->registerRenderers();

        $actions = $factory->getInstance(ActionRegistry::class);

        $i = 1;
        $actionIds = [];
        foreach ($actions->getAllActions() as $id => $action) {
            $write($i++ . ' - ' . $action->caption() . PHP_EOL);
            $actionIds[] = $id;
        }

        $actionIndex = $read('Action:');

        $actionId = $actionIds[$actionIndex - 1];
        $action = $actions->getAction($actionId);
        $write(PHP_EOL . $action->caption() . PHP_EOL);

        $reader = $factory->getInstance(CliParameterReader::class, ['read' => $read]);
        $factory->setSingleton($reader, ParameterReader::class);

        $executor = $factory->getInstance(Executor::class);
        $write($executor->execute($actionId));
    }

    private function registerFields(callable $read) {
        $this->fields->add(new PrimitiveField());
        $this->fields->add(new BooleanField());
        $this->fields->add(new FileField());
        $this->fields->add(new HtmlField($read));
        $this->fields->add(new DateTimeField());
        $this->fields->add(new ArrayField($this->fields, $read));
        $this->fields->add(new NullableField($this->fields, $read));
        $this->fields->add(new ObjectField($this->types, $this->fields, $read));
        $this->fields->add(new MultiField($this->fields, $read));
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