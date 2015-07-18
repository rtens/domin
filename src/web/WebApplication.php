<?php
namespace rtens\domin\web;

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

class WebApplication {

    private $renderers;
    private $links;
    private $types;
    private $fields;
    private $identifiers;

    /**
     * @param FieldRegistry $fields <-
     * @param RendererRegistry $renderers <-
     * @param LinkRegistry $links <-
     * @param IdentifiersProvider $identifiers <-
     * @param TypeFactory $types <-
     */
    public function __construct(FieldRegistry $fields, RendererRegistry $renderers, LinkRegistry $links, IdentifiersProvider $identifiers, TypeFactory $types) {
        $this->renderers = $renderers;
        $this->links = $links;
        $this->types = $types;
        $this->fields = $fields;
        $this->identifiers = $identifiers;
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