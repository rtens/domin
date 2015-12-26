<?php
namespace rtens\domin\delivery\web;

class HeadElements {

    public static function jquery() {
        return self::script('//code.jquery.com/jquery-2.1.4.min.js');
    }

    public static function jqueryUi() {
        return self::script('//code.jquery.com/ui/1.11.4/jquery-ui.min.js');
    }

    public static function jqueryUiCss() {
        return self::style('//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
    }

    public static function bootstrap() {
        return self::style('//netdna.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
    }

    public static function bootstrapJs() {
        return self::script('//netdna.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
    }

    public static function fontAwesome() {
        return self::style('//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css');
    }

    public static function script($src) {
        return new Element('script', [
            'src' => $src
        ]);
    }

    public static function style($href) {
        return new Element('link', [
            'rel' => 'stylesheet',
            'href' => $href
        ]);
    }

    /**
     * @param Element[] $headElements
     * @return array|string[]
     */
    public static function filter(array $headElements) {
        return array_values(array_unique(array_map(function (Element $element) {
            return (string)$element;
        }, $headElements)));
    }
}