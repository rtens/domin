<?php
namespace rtens\domin\delivery\web\renderers\link;

class LinkRegistry {

    /** @var Link[] */
    private $links = [];

    /**
     * @param mixed $object
     * @return array|Link[]
     */
    public function getLinks($object) {
        return array_filter($this->links, function (Link $link) use ($object) {
            return $link->handles($object);
        });
    }

    public function add(Link $link) {
        $this->links[] = $link;
    }
}