<?php
namespace rtens\domin\files;

interface File {

    /**
     * @return string
     */
    public function getContent();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return int
     */
    public function getSize();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $content
     */
    public function setContent($content);

    /**
     * @param string $path
     */
    public function save($path);

}