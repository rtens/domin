<?php
namespace rtens\domin\delivery\web\renderers\link;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebCommentParser;
use watoki\collections\Map;
use watoki\curir\protocol\Url;

class LinkPrinter {

    /** @var \watoki\curir\protocol\Url */
    private $baseUrl;

    /** @var LinkRegistry */
    private $links;

    /** @var ActionRegistry */
    private $actions;

    /** @var WebCommentParser */
    private $parser;

    public function __construct(Url $baseUrl, LinkRegistry $links, ActionRegistry $actions, WebCommentParser $parser) {
        $this->baseUrl = $baseUrl;
        $this->links = $links;
        $this->actions = $actions;
        $this->parser = $parser;
    }

    /**
     * @param object $object
     * @return array
     */
    public function createLinkElements($object) {
        return $this->createLinks($object, 'btn btn-xs btn-primary');
    }

    /**
     * @param object $object
     * @param string|null $caption
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function createDropDown($object, $caption = null) {
        $links = $this->createLinks($object);
        if (empty($links)) {
            return [];
        }

        return [
            new Element('div', ['class' => 'dropdown'], [
                new Element('button', [
                    'class' => 'btn btn-xs btn-primary dropdown-toggle',
                    'type' => 'button',
                    'data-toggle' => 'dropdown',
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'false'
                ], [
                    $caption ?: (new \ReflectionClass($object))->getShortName(),
                    new Element('span', ['class' => 'caret'])
                ]),
                new Element('ul', ['class' => 'dropdown-menu'], array_map(function (Element $element) {
                    return new Element('li', [], [$element]);
                }, $links))
            ])
        ];
    }

    private function createLinks($object, $classes = '') {
        return array_map(function (Link $link) use ($object, $classes) {
            $action = $this->actions->getAction($link->actionId());

            $url = $this->baseUrl->appended($link->actionId())->withParameters(new Map($link->parameters($object)));
            $attributes = ['class' => $classes, 'href' => $url];
            if ($link->confirm() !== null) {
                $attributes['onclick'] = "return confirm('{$link->confirm()}');";
            }
            $description = $action->description();
            if (!is_null($description)) {
                $attributes['title'] = str_replace('"', "'", strip_tags($this->parser->shorten($description)));
            }
            return new Element('a', $attributes, [
                $action->caption()
            ]);
        }, $this->links->getLinks($object));
    }
}