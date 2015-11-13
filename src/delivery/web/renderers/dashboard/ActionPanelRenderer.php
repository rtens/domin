<?php
namespace rtens\domin\delivery\web\renderers\dashboard;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\dashboard\types\ActionPanel;
use rtens\domin\delivery\web\renderers\dashboard\types\Panel;
use rtens\domin\delivery\web\WebRenderer;
use watoki\collections\Map;
use watoki\curir\protocol\Url;

class ActionPanelRenderer implements WebRenderer {

    /** @var RendererRegistry */
    private $renderers;

    /** @var ActionRegistry */
    private $actions;

    /** @var Url */
    private $baseUrl;

    private $results = [];

    /**
     * @param RendererRegistry $renderers
     * @param ActionRegistry $actions
     * @param Url $baseUrl
     */
    public function __construct(RendererRegistry $renderers, ActionRegistry $actions, Url $baseUrl) {
        $this->renderers = $renderers;
        $this->actions = $actions;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof ActionPanel;
    }

    /**
     * @param ActionPanel $value
     * @return mixed
     */
    public function render($value) {
        $heading = $this->actions->getAction($value->getActionId())->caption();
        return (string)(new Panel($heading, $this->getContent($value)))
            ->setMaxHeight($value->getMaxHeight())
            ->setRightHeading([new Element('a', [
                'href' => $this->baseUrl
                    ->appended($value->getActionId())
                    ->withParameters(new Map($value->getParameters()))
            ], [new Element('span', ['class' => 'glyphicon glyphicon-circle-arrow-right'])])])
            ->render($this->renderers);
    }

    /**
     * @param mixed $value
     * @return array|Element[]
     */
    public function headElements($value) {
        $content = $this->getContent($value);
        $renderer = $this->renderers->getRenderer($content);
        if ($renderer instanceof WebRenderer) {
            return $renderer->headElements($content);
        }
        return [];
    }

    private function getContent(ActionPanel $value) {
        $key = spl_object_hash($value);

        if (!isset($this->results[$key])) {
            $action = $this->actions->getAction($value->getActionId());
            $this->results[$key] = $action->execute($value->getParameters());
        }
        return $this->results[$key];
    }
}