<?php
namespace rtens\domin\web;

class Element {

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

        if ($this->children) {
            return
                "<{$this->name}$attributes>" .
                $this->makeChildren() .
                "</{$this->name}>";
        }
        return "<{$this->name}$attributes/>";
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

        if (count($children) == 1) {
            return $children[0];
        }

        return "\n" . implode("\n", $children) . "\n";
    }
}