<?php
namespace rtens\domin\delivery\web\renderers\link;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\ExecutionToken;
use rtens\domin\delivery\web\resources\ExecutionResource;
use rtens\domin\delivery\web\Url;
use rtens\domin\delivery\web\WebCommentParser;

class LinkPrinter {

    /** @var LinkRegistry */
    private $links;

    /** @var ActionRegistry */
    private $actions;

    /** @var WebCommentParser */
    private $parser;

    /** @var ExecutionToken */
    private $token;

    public function __construct(LinkRegistry $links, ActionRegistry $actions, WebCommentParser $parser,
                                ExecutionToken $token = null) {
        $this->links = $links;
        $this->actions = $actions;
        $this->parser = $parser;
        $this->token = $token;
    }

    /**
     * @param mixed $object
     * @return array
     */
    public function createLinkElements($object) {
        return $this->createLinks($object, 'btn btn-xs btn-primary');
    }

    /**
     * @param mixed $object
     * @param string|null $caption
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function createDropDown($object, $caption = null) {
        $links = $this->createLinks($object);
        if (empty($links)) {
            return [];
        }

        if (!$caption) {
            if (is_object($object)) {
                $caption = (new \ReflectionClass($object))->getShortName();
            } else {
                $caption = 'Actions';
            }
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
                    $caption,
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

            $parameters = $link->parameters($object);
            if ($action->isModifying() && $this->token) {
                $parameters[ExecutionResource::TOKEN_ARG] = $this->token->generate($link->actionId());
            }

            $url = (string)Url::relative($link->actionId(), $parameters);

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