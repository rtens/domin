<?php
namespace rtens\domin\web\renderers\object;

class LinkRegistry {

    /** @var Link[] */
    private $links = [];

    public function getLinks($object) {
        return array_filter($this->links, function (Link $link) use ($object) {
            return $link->handles($object);
        });
    }

    public function add(Link $link) {
        $this->links[] = $link;
    }
}