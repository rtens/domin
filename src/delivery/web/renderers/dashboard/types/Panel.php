<?php
namespace rtens\domin\delivery\web\renderers\dashboard\types;

use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\renderers\dashboard\DashboardItem;
use rtens\domin\delivery\web\WebRenderer;

class Panel implements DashboardItem {

    /** @var string */
    private $heading;

    /** @var mixed */
    private $content;

    /** @var array|Element[] */
    public $rightHeading = [];

    /** @var null|string */
    private $maxHeight;

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
        $content = [
            new Element('div', ['class' => 'panel-heading'], [
                $this->heading,
                new Element('div', ['class' => 'pull-right'],
                    $this->rightHeading
                )
            ])
        ];

        if ($this->maxHeight) {
            $content[] = new Element('div', [
                'class' => 'panel-body',
                'style' => 'overflow: hidden; max-height: ' . $this->maxHeight,
                'data-maxheight' => $this->maxHeight
            ], [
                $renderers->getRenderer($this->content)->render($this->content)
            ]);
            $content[] = new Element('div', ['class' => 'panel-footer clearfix'], [
                new Element('div', ['class' => 'pull-right'], [
                    new Element('a', [
                        'href' => 'javascript:',
                        'onclick' => "var body = $(this).closest('.panel').find('.panel-body');" .
                            "body.css('max-height', 'none');" .
                            "body.css('overflow', 'hidden');" .
                            "$(this).hide(); " .
                            "$(this).next().show();",
                        'class' => 'show-all'
                    ], [
                        'Show all',
                        new Element('span', ['class' => 'glyphicon glyphicon-chevron-down'])
                    ]),
                    new Element('a', [
                        'href' => 'javascript:',
                        'onclick' => "var body = $(this).closest('.panel').find('.panel-body');" .
                            "body.css('max-height', body.data('maxheight'));" .
                            "body.css('overflow', 'visible');" .
                            "$(this).hide();" .
                            "$(this).prev().show();",
                        'class' => 'show-less',
                        'style' => 'display: none'
                    ], [
                        'Show less',
                        new Element('span', ['class' => 'glyphicon glyphicon-chevron-up'])
                    ])
                ])
            ]);
        } else {
            $content[] = new Element('div', ['class' => 'panel-body'], [
                $renderers->getRenderer($this->content)->render($this->content)
            ]);
        }

        return new Element('div', ['class' => 'panel panel-default'], $content);
    }

    /**
     * @param RendererRegistry $renderers
     * @return \rtens\domin\delivery\web\Element[]
     */
    public function headElements(RendererRegistry $renderers) {
        $elements = [HeadElements::jquery()];

        $renderer = $renderers->getRenderer($this->content);
        if ($renderer instanceof WebRenderer) {
            $elements = array_merge($elements, $renderer->headElements($this->content));
        }
        return $elements;
    }

    /**
     * @param array|Element[] $rightHeading
     * @return static
     */
    public function setRightHeading(array $rightHeading) {
        $this->rightHeading = $rightHeading;
        return $this;
    }

    /**
     * @param string $height e.g. "20px"
     * @return static
     */
    public function setMaxHeight($height) {
        $this->maxHeight = $height;
        return $this;
    }
}