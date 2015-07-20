<?php
namespace rtens\domin\web;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\reflection\IdentifiersProvider;
use rtens\domin\reflection\TypeFactory;
use rtens\domin\web\renderers\ArrayRenderer;
use rtens\domin\web\renderers\BooleanRenderer;
use rtens\domin\web\renderers\DateTimeRenderer;
use rtens\domin\web\renderers\FileRenderer;
use rtens\domin\web\renderers\HtmlRenderer;
use rtens\domin\web\renderers\IdentifierRenderer;
use rtens\domin\web\renderers\link\LinkPrinter;
use rtens\domin\web\renderers\link\LinkRegistry;
use rtens\domin\web\renderers\ObjectRenderer;
use rtens\domin\web\renderers\PrimitiveRenderer;
use rtens\domin\web\fields\ArrayField;
use rtens\domin\web\fields\BooleanField;
use rtens\domin\web\fields\DateTimeField;
use rtens\domin\web\fields\EnumerationField;
use rtens\domin\web\fields\FileField;
use rtens\domin\web\fields\HtmlField;
use rtens\domin\web\fields\IdentifierField;
use rtens\domin\web\fields\MultiField;
use rtens\domin\web\fields\NullableField;
use rtens\domin\web\fields\ObjectField;
use rtens\domin\web\fields\PrimitiveField;
use watoki\curir\protocol\Url;
use watoki\factory\Factory;

class WebApplication {

    /** @var ActionRegistry */
    public $actions;

    /** @var RendererRegistry */
    public $renderers;

    /** @var LinkRegistry */
    public $links;

    /** @var TypeFactory */
    public $types;

    /** @var FieldRegistry */
    public $fields;

    /** @var IdentifiersProvider */
    public $identifiers;

    /**
     * @param Factory $factory <-
     * @param ActionRegistry $actions <-
     * @param FieldRegistry $fields <-
     * @param RendererRegistry $renderers <-
     * @param LinkRegistry $links <-
     * @param IdentifiersProvider $identifiers <-
     * @param TypeFactory $types <-
     */
    public function __construct(Factory $factory, ActionRegistry $actions, FieldRegistry $fields,
                                RendererRegistry $renderers, LinkRegistry $links, IdentifiersProvider $identifiers,
                                TypeFactory $types) {
        $this->actions = $factory->setSingleton($actions);
        $this->renderers = $factory->setSingleton($renderers);
        $this->links = $factory->setSingleton($links);
        $this->types = $factory->setSingleton($types);
        $this->fields = $factory->setSingleton($fields);
        $this->identifiers = $factory->setSingleton($identifiers);
    }

    /**
     * @param callable $callback Receives the WebApplication instance
     * @param null|Factory $factory
     * @return Factory
     */
    public static function init(callable $callback, Factory $factory = null) {
        $factory = $factory ?: new Factory();
        $callback($factory->setSingleton($factory->getInstance(self::class)));
        return $factory;
    }

    public function registerRenderers(Url $baseUrl) {
        $links = new LinkPrinter($baseUrl, $this->links);

        $this->renderers->add(new BooleanRenderer());
        $this->renderers->add(new PrimitiveRenderer());
        $this->renderers->add(new DateTimeRenderer());
        $this->renderers->add(new HtmlRenderer());
        $this->renderers->add(new IdentifierRenderer($links));
        $this->renderers->add(new FileRenderer());
        $this->renderers->add(new ArrayRenderer($this->renderers));
        $this->renderers->add(new ObjectRenderer($this->renderers, $this->types, $links));
    }

    public function registerFields() {
        $this->fields->add(new PrimitiveField());
        $this->fields->add(new BooleanField());
        $this->fields->add(new FileField());
        $this->fields->add(new HtmlField());
        $this->fields->add(new DateTimeField());
        $this->fields->add(new ArrayField($this->fields));
        $this->fields->add(new NullableField($this->fields));
        $this->fields->add(new ObjectField($this->types, $this->fields));
        $this->fields->add(new MultiField($this->fields));
        $this->fields->add(new IdentifierField($this->fields, $this->identifiers));
        $this->fields->add(new EnumerationField($this->fields));
    }
}