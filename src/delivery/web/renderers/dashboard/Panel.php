<?php
namespace rtens\domin\delivery\web\renderers\dashboard;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebRenderer;

class Panel implements DashboardItem {

    /** @var string */
    private $heading;

    /** @var mixed */
    private $content;

    /** @var array|Element[] */
    public $rightHeading = [];

    /**
     * @param string $heading
     * @param mixed $content
     */
    public function __construct($heading, $content) {
        $this->heading = $heading;
        $this->content = $content;
    }

    /**
     * @param RendererRegistry $renderers
     * @return Element
     * @throws \Exception
     */
    public function render(RendererRegistry $renderers) {
        return new Element('div', ['class' => 'panel panel-default'], [
            new Element('div', ['class' => 'panel-heading'], [
                $this->heading,
                new Element('div', ['class' => 'pull-right'],
                    $this->rightHeading
                )
            ]),
            new Element('div', ['class' => 'panel-body'], [
                $renderers->getRenderer($this->content)->render($this->content)
            ])
        ]);
    }

    /**
     * @param RendererRegistry $renderers
     * @return \rtens\domin\delivery\web\Element[]
     */
    public function headElements(RendererRegistry $renderers) {
        $renderer = $renderers->getRenderer($this->content);
        if ($renderer instanceof WebRenderer) {
            return $renderer->headElements($this->content);
        }
        return [];
    }

    /**
     * @param array|Element[] $rightHeading
     * @return static
     */
    public function setRightHeading(array $rightHeading) {
        $this->rightHeading = $rightHeading;
        return $this;
    }
}