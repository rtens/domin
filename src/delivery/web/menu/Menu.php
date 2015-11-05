<?php
namespace rtens\domin\delivery\web\menu;

use rtens\domin\delivery\web\Element;
use watoki\curir\delivery\WebRequest;

class Menu {

    /** @var MenuItem[] */
    private $left = [];

    /** @var MenuItem[] */
    private $right = [];

    /** @var string */
    private $brand = 'domin';

    public function add(MenuItem $item) {
        $this->left[] = $item;
        return $this;
    }

    public function addRight(MenuItem $item) {
        $this->right[] = $item;
        return $this;
    }

    /**
     * @param string $brand Displayed on the very left of the menu
     * @return Menu
     */
    public function setBrand($brand) {
        $this->brand = $brand;
        return $this;
    }

    public function render(WebRequest $request) {
        $render = function (MenuItem $item) use ($request) {
            return $item->render($request);
        };

        return new Element('nav', ['class' => 'navbar navbar-default'], [
            new Element('div', ['class' => 'container-fluid'], [
                new Element('div', ['class' => 'navbar-header'], [
                    new Element('button', [
                        'type' => 'button',
                        'class' => 'navbar-toggle collapsed',
                        'data-toggle' => 'collapse',
                        'data-target' => '#navbar',
                        'aria-expanded' => 'false',
                        'aria-controls' => 'navbar'
                    ], [
                        new Element('span', ['class' => 'sr-only'], ['Toggle navigation']),
                        new Element('span', ['class' => 'icon-bar']),
                        new Element('span', ['class' => 'icon-bar']),
                        new Element('span', ['class' => 'icon-bar']),
                    ]),
                    new Element('a', ['class' => 'navbar-brand', 'href' => $request->getContext()->appended('')->toString()], [$this->brand])
                ]),
                new Element('div', ['id' => 'navbar', 'class' => 'navbar-collapse collapse'], [
                    new Element('ul', ['class' => 'nav navbar-nav'],
                        array_map($render, $this->left)
                    ),
                    new Element('ul', ['class' => 'nav navbar-nav navbar-right'],
                        array_map($render, $this->right)
                    )
                ])
            ])
        ]);
    }
}