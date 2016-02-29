<?php
namespace rtens\domin\delivery\web\home;

class ActionListItem {

    /** @var string */
    private $id;

    /** @var string */
    private $caption;

    /** @var string */
    private $description;

    /**
     * @param string $id
     * @param string $caption
     * @param string $description
     */
    public function __construct($id, $caption, $description) {
        $this->id = $id;
        $this->caption = $caption;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCaption() {
        return $this->caption;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
}