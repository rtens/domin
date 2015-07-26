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
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function createLinkElements($object) {
        return array_map(function (Link $link) use ($object) {
            $action = $this->actions->getAction($link->actionId());

            $url = $this->baseUrl->appended($link->actionId())->withParameters(new Map($link->parameters($object)));
            $attributes = ['class' => 'btn btn-xs btn-primary', 'href' => $url];
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