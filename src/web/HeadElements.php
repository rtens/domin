<?php
namespace rtens\domin\web;

class HeadElements {

    public static function jquery() {
        return new Element('script', [
            'src' => '//code.jquery.com/jquery-2.1.4.min.js'
        ]);
    }

    public static function jqueryUi() {
        return new Element('script', [
            'src' => '//code.jquery.com/ui/1.11.4/jquery-ui.js'
        ]);
    }

    public static function bootstrap() {
        return new Element('link', [
            'rel' => 'stylesheet',
            'href' => '//netdna.bootstrapcdn.com/bootstrap/3.0.1/css/bootstrap.min.css'
        ]);
    }

    public static function bootstrapJs() {
        return new Element('script', [
            'src' => '//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js'
        ]);
    }

    public static function fontAwesome() {
        return new Element('link', [
            'rel' => 'stylesheet',
            'href' => '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css'
        ]);
    }
}