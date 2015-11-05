<?php
namespace rtens\domin\delivery\web\menu;

use rtens\domin\delivery\web\Element;
use watoki\curir\delivery\WebRequest;

class MenuGroup implements MenuItem {

    private $caption;

    /** @var MenuItem[] */
    private $items = [];

    public function __construct($caption) {
        $this->caption = $caption;
    }

    public function add(MenuItem $item) {
        $this->items[] = $item;
        return $this;
    }

    public function render(WebRequest $request) {
        return new Element('li', ['class' => 'dropdown'], [
            new Element('a', [
                'href' => '#',
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'role' => 'button',
                'aria-haspopup' => 'true',
                'aria-expanded' => 'false'
            ], [
                $this->caption,
                new Element('span', ['class' => 'caret'])
            ]),
            new Element('ul', ['class' => 'dropdown-menu'],
                array_map(function (MenuItem $item) use ($request) {
                    return $item->render($request);
                }, $this->items)
            )
        ]);
    }
}