<?php
namespace rtens\domin\delivery\web;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\parameters\IdentifiersProvider;
use rtens\domin\reflection\types\TypeFactory;
use rtens\domin\delivery\web\fields\ImageField;
use rtens\domin\delivery\web\fields\NumberField;
use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\renderers\ArrayRenderer;
use rtens\domin\delivery\web\renderers\BooleanRenderer;
use rtens\domin\delivery\web\renderers\DateTimeRenderer;
use rtens\domin\delivery\web\renderers\FileRenderer;
use rtens\domin\delivery\web\renderers\HtmlRenderer;
use rtens\domin\delivery\web\renderers\IdentifierRenderer;
use rtens\domin\delivery\web\renderers\ImageRenderer;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\link\LinkRegistry;
use rtens\domin\delivery\web\renderers\ObjectRenderer;
use rtens\domin\delivery\web\renderers\PrimitiveRenderer;
use rtens\domin\delivery\web\fields\ArrayField;
use rtens\domin\delivery\web\fields\BooleanField;
use rtens\domin\delivery\web\fields\DateTimeField;
use rtens\domin\delivery\web\fields\EnumerationField;
use rtens\domin\delivery\web\fields\FileField;
use rtens\domin\delivery\web\fields\HtmlField;
use rtens\domin\delivery\web\fields\IdentifierField;
use rtens\domin\delivery\web\fields\MultiField;
use rtens\domin\delivery\web\fields\NullableField;
use rtens\domin\delivery\web\fields\ObjectField;
use rtens\domin\delivery\web\fields\StringField;
use watoki\curir\protocol\Url;
use watoki\factory\Factory;

class WebApplication {

    /** @var Factory */
    public $factory;

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

    /** @var Menu */
    public $menu;

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
        $this->factory = $factory;
        $this->actions = $factory->setSingleton($actions);
        $this->renderers = $factory->setSingleton($renderers);
        $this->links = $factory->setSingleton($links);
        $this->types = $factory->setSingleton($types);
        $this->fields = $factory->setSingleton($fields);
        $this->identifiers = $factory->setSingleton($identifiers);
        $this->menu = $factory->setSingleton($factory->getInstance(Menu::class));
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
        $this->renderers->add(new ImageRenderer());
        $this->renderers->add(new ArrayRenderer($this->renderers));
        $this->renderers->add(new ObjectRenderer($this->renderers, $this->types, $links));
    }

    public function registerFields() {
        $this->fields->add(new StringField());
        $this->fields->add(new NumberField());
        $this->fields->add(new BooleanField());
        $this->fields->add(new FileField());
        $this->fields->add(new ImageField());
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