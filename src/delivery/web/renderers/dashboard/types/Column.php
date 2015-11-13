<?php
namespace rtens\domin\delivery\web\renderers\dashboard\types;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\dashboard\DashboardItem;
use rtens\domin\delivery\web\WebRenderer;

class Column implements DashboardItem {

    /** @var mixed */
    public $content;

    /** @var int[] */
    private $widths;

    /**
     * @param mixed $content
     * @param int $width Out of 12
     */
    public function __construct($content, $width) {
        $this->content = $content;
        $this->widths = ['lg' => $width];
    }

    public function setMediumWidth($width) {
        $this->widths['md'] = $width;
        return $this;
    }

    public function setSmallWidth($width) {
        $this->widths['sm'] = $width;
        return $this;
    }

    public function setExtraSmallWidth($width) {
        $this->widths['xs'] = $width;
        return $this;
    }

    /**
     * @param RendererRegistry $renderers
     * @return Element
     * @throws \Exception
     */
    public function render(RendererRegistry $renderers) {
        return new Element('div',
            ['class' => $this->makeWidthClasses()],
            [$renderers->getRenderer($this->content)->render($this->content)]);
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

    private function makeWidthClasses() {
        $classes = [];
        foreach ($this->widths as $size => $width) {
            $classes[] = "col-$size-$width";
        }
        return implode(' ', $classes);
    }
}