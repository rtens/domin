<?php
namespace rtens\domin\delivery\web\renderers\link;

use rtens\domin\delivery\web\Element;
use watoki\collections\Map;
use watoki\curir\protocol\Url;

class LinkPrinter {

    /** @var \watoki\curir\protocol\Url */
    private $baseUrl;

    /** @var LinkRegistry */
    private $links;

    public function __construct(Url $baseUrl, LinkRegistry $links) {
        $this->baseUrl = $baseUrl;
        $this->links = $links;
    }

    /**
     * @param object $object
     * @return array|\rtens\domin\delivery\web\Element[]
     */
    public function createLinkElements($object) {
        return array_map(function (Link $link) use ($object) {
            $url = $this->baseUrl->appended($link->actionId())->withParameters(new Map($link->parameters($object)));
            $attributes = ['class' => 'btn btn-xs btn-primary', 'href' => $url];
            if ($link->confirm() !== null) {
                $attributes['onclick'] = "return confirm('{$link->confirm()}');";
            }
            return new Element('a', $attributes, [
                $link->caption($object)
            ]);
        }, $this->links->getLinks($object));
    }
}