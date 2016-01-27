<?php
namespace rtens\domin\delivery\web;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\fields\ActionField;
use rtens\domin\delivery\web\fields\ColorField;
use rtens\domin\delivery\web\fields\DateIntervalField;
use rtens\domin\delivery\web\fields\RangeField;
use rtens\domin\delivery\web\fields\TextField;
use rtens\domin\delivery\web\renderers\charting\ChartRenderer;
use rtens\domin\delivery\web\renderers\charting\ScatterChartRenderer;
use rtens\domin\delivery\web\renderers\ColorRenderer;
use rtens\domin\delivery\web\renderers\dashboard\ActionPanelRenderer;
use rtens\domin\delivery\web\renderers\dashboard\DashboardItemRenderer;
use rtens\domin\delivery\web\renderers\DateIntervalRenderer;
use rtens\domin\delivery\web\renderers\DelayedOutputRenderer;
use rtens\domin\delivery\web\renderers\ElementRenderer;
use rtens\domin\delivery\web\renderers\MapRenderer;
use rtens\domin\delivery\web\renderers\tables\DataTableRenderer;
use rtens\domin\delivery\web\renderers\tables\TableRenderer;
use rtens\domin\delivery\web\renderers\TextRenderer;
use rtens\domin\execution\access\AccessControl;
use rtens\domin\parameters\IdentifiersProvider;
use rtens\domin\reflection\types\TypeFactory;
use rtens\domin\delivery\web\fields\ImageField;
use rtens\domin\delivery\web\fields\NumberField;
use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\renderers\ListRenderer;
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
use watoki\factory\Factory;

class WebApplication {

    /** @var string */
    public $name = 'domin';

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

    /** @var MobileDetector */
    public $detector;

    /** @var WebCommentParser */
    public $parser;

    /** @var AccessControl */
    public $access;

    /**
     * @param Factory $factory <-
     * @param ActionRegistry $actions <-
     * @param FieldRegistry $fields <-
     * @param RendererRegistry $renderers <-
     * @param LinkRegistry $links <-
     * @param IdentifiersProvider $identifiers <-
     * @param TypeFactory $types <-
     * @param MobileDetector $detect <-
     * @param WebCommentParser $parser <-
     * @param AccessControl $access <-
     */
    public function __construct(Factory $factory, ActionRegistry $actions, FieldRegistry $fields,
                                RendererRegistry $renderers, LinkRegistry $links, IdentifiersProvider $identifiers,
                                TypeFactory $types, MobileDetector $detect, WebCommentParser $parser,
                                AccessControl $access) {
        $factory->setSingleton($this);

        $this->factory = $factory;
        $this->actions = $actions;
        $this->renderers = $renderers;
        $this->links = $links;
        $this->types = $types;
        $this->fields = $fields;
        $this->identifiers = $identifiers;
        $this->detector = $detect;
        $this->parser = $parser;
        $this->access = $access;
        $this->menu = new Menu($actions);
    }

    /**
     * @param callable $callback Receives the WebApplication instance
     * @param null|Factory $factory
     * @return Factory
     */
    public static function init(callable $callback, Factory $factory = null) {
        $factory = $factory ?: new Factory();

        /** @var self $instance */
        $instance = $factory->getInstance(self::class);
        $callback($factory->setSingleton($instance));
        $instance->prepare();

        return $factory;
    }

    /**
     * @param callable $accessControlFactory Received the current WebRequest
     */
    public function restrictAccess(callable $accessControlFactory) {
        $this->accessFactory = $accessControlFactory;
    }

    /**
     * @param string $name
     */
    public function setNameAndBrand($name) {
        $this->name = $name;
        $this->menu->setBrand($name);
    }

    public function prepare() {
        $this->registerRenderers();
        $this->registerFields();
    }

    private function registerRenderers() {
        $links = new LinkPrinter($this->links, $this->actions, $this->parser);

        $this->renderers->add(new ElementRenderer());
        $this->renderers->add(new BooleanRenderer());
        $this->renderers->add(new ColorRenderer());
        $this->renderers->add(new PrimitiveRenderer());
        $this->renderers->add(new DateTimeRenderer());
        $this->renderers->add(new DateIntervalRenderer());
        $this->renderers->add(new TextRenderer());
        $this->renderers->add(new HtmlRenderer());
        $this->renderers->add(new IdentifierRenderer($links));
        $this->renderers->add(new FileRenderer());
        $this->renderers->add(new ImageRenderer());
        $this->renderers->add(new ScatterChartRenderer());
        $this->renderers->add(new ChartRenderer());
        $this->renderers->add(new DelayedOutputRenderer());
        $this->renderers->add(new DashboardItemRenderer($this->renderers));
        $this->renderers->add(new ActionPanelRenderer($this->renderers, $this->actions, $this->fields));
        $this->renderers->add(new DataTableRenderer($this->renderers));
        $this->renderers->add(new TableRenderer($this->renderers, $links));
        $this->renderers->add(new ListRenderer($this->renderers, $links));
        $this->renderers->add(new MapRenderer($this->renderers, $links));
        $this->renderers->add(new ObjectRenderer($this->renderers, $this->types, $links));
    }

    private function registerFields() {
        $this->fields->add(new StringField());
        $this->fields->add(new NumberField());
        $this->fields->add(new RangeField($this->detector));
        $this->fields->add(new BooleanField());
        $this->fields->add(new ColorField());
        $this->fields->add(new FileField());
        $this->fields->add(new ImageField());
        $this->fields->add(new TextField());
        $this->fields->add(new HtmlField());
        $this->fields->add(new DateTimeField());
        $this->fields->add(new DateIntervalField());
        $this->fields->add(new ArrayField($this->fields, $this->detector));
        $this->fields->add(new NullableField($this->fields));
        $this->fields->add(new ActionField($this->fields, $this->actions));
        $this->fields->add(new ObjectField($this->types, $this->fields));
        $this->fields->add(new MultiField($this->fields));
        $this->fields->add(new IdentifierField($this->fields, $this->identifiers));
        $this->fields->add(new EnumerationField($this->fields));
    }
}