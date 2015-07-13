<?php
namespace rtens\domin\web;

class Element {

    private static $EMPTY_ELEMENTS = [
        'link',
        'track',
        'param',
        'area',
        'command',
        'col',
        'base',
        'meta',
        'hr',
        'source',
        'img',
        'keygen',
        'br',
        'wbr',
        'colgroup',
        'input',
    ];

    private $name;

    private $attributes;

    private $children;

    function __construct($name, $attributes = [], $children = []) {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    private function toString() {
        $attributes = $this->makeAttributes();

        if (in_array($this->name, self::$EMPTY_ELEMENTS)) {
            return "<{$this->name}$attributes/>";
        }

        return
            "<{$this->name}$attributes>" .
            $this->makeChildren() .
            "</{$this->name}>";
    }

    function __toString() {
        return $this->toString();
    }

    private function makeAttributes() {
        $attributes = [];
        foreach ($this->attributes as $key => $value) {
            $attributes[] = $key . '="' . $value . '"';
        }

        if (!$attributes) {
            return '';
        }

        return ' ' . implode(' ', $attributes);
    }

    private function makeChildren() {
        $children = [];
        foreach ($this->children as $child) {
            $children[] = (string)$child;
        }

        if (!$children) {
            return '';
        } else if (count($children) == 1 && strpos($children[0], "\n") === false) {
            return $children[0];
        } else {
            return "\n" . implode("\n", $children) . "\n";
        }
    }
}