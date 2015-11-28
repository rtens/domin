<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\web\menu\ActionMenuItem;
use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\menu\MenuGroup;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\deli\Path;

class ShowMenuSpec extends StaticTestSuite {

    function emptyMenuOnOverview() {
        $this->whenIRenderTheMenu();
        $this->assert($this->rendered,
            '<nav class="navbar navbar-default">' . "\n" .
            '<div class="container-fluid">' . "\n" .
            '<div class="navbar-header">' . "\n" .
            '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">' . "\n" .
            '<span class="sr-only">Toggle navigation</span>' . "\n" .
            '<span class="icon-bar"></span>' . "\n" .
            '<span class="icon-bar"></span>' . "\n" .
            '<span class="icon-bar"></span>' . "\n" .
            '</button>' . "\n" .
            '<a class="navbar-brand" href="http://example.com/base/">domin</a>' . "\n" .
            '</div>' . "\n" .
            '<div id="navbar" class="navbar-collapse collapse">' . "\n" .
            '<ul class="nav navbar-nav"></ul>' . "\n" .
            '<ul class="nav navbar-nav navbar-right"></ul>' . "\n" .
            '</div>' . "\n" .
            '</div>' . "\n" .
            '</nav>'
        );
    }

    function customBrand() {
        $this->menu->setBrand('my Brand');
        $this->whenIRenderTheMenu();
        $this->assert->contains($this->rendered, '<a class="navbar-brand" href="http://example.com/base/">my Brand</a>');
    }

    function menuWithItems() {
        $this->givenTheAction_WithCaption('foo', 'My Foo');
        $this->givenTheAction_WithCaption('bar', 'My Bar');

        $this->menu->add(new ActionMenuItem($this->actions, 'foo'));
        $this->menu->add(new ActionMenuItem($this->actions, 'bar', [
            'one' => 'uno',
            'two' => 'dos'
        ]));

        $this->whenIRenderTheMenu();
        $this->assert->contains($this->rendered,
            '<ul class="nav navbar-nav">' . "\n" .
            '<li><a href="http://example.com/base/foo">My Foo</a></li>' . "\n" .
            '<li><a href="http://example.com/base/bar?one=uno&amp;two=dos">My Bar</a></li>' . "\n" .
            '</ul>');
    }

    function itemsOnRightSide() {
        $this->givenTheAction_WithCaption('foo', 'My Foo');
        $this->menu->addRight(new ActionMenuItem($this->actions, 'foo'));

        $this->whenIRenderTheMenu();
        $this->assert->contains($this->rendered,
            '<ul class="nav navbar-nav"></ul>');
        $this->assert->contains($this->rendered,
            '<ul class="nav navbar-nav navbar-right">' .
            '<li><a href="http://example.com/base/foo">My Foo</a></li>' .
            '</ul>');
    }

    function menuWithGroups() {
        $this->givenTheAction_WithCaption('foo', 'My Foo');
        $this->givenTheAction_WithCaption('bar', 'My Bar');

        $this->menu->add((new MenuGroup('My Group'))
            ->add(new ActionMenuItem($this->actions, 'foo', ['foo' => 'bar']))
            ->add(new ActionMenuItem($this->actions, 'bar', ['one' => 'two'])));

        $this->whenIRenderTheMenu();
        $this->assert->contains($this->rendered,
            '<li class="dropdown">' . "\n" .
            '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . "\n" .
            'My Group' . "\n" .
            '<span class="caret"></span>' . "\n" .
            '</a>' . "\n" .
            '<ul class="dropdown-menu">' . "\n" .
            '<li><a href="http://example.com/base/foo?foo=bar">My Foo</a></li>' . "\n" .
            '<li><a href="http://example.com/base/bar?one=two">My Bar</a></li>' . "\n" .
            '</ul>' . "\n" .
            '</li>');
    }

    /** @var Menu */
    private $menu;

    /** @var ActionRegistry */
    private $actions;

    private $rendered;

    protected function before() {
        $this->actions = new ActionRegistry();
        $this->menu = new Menu($this->actions);
    }

    private function whenIRenderTheMenu() {
        $this->rendered = (string)$this->menu->render(new WebRequest(Url::fromString('http://example.com/base'), new Path()));
    }

    private function givenTheAction_WithCaption($id, $caption) {
        $action = Mockster::of(Action::class);
        Mockster::stub($action->caption())->will()->return_($caption);
        $this->actions->add($id, Mockster::mock($action));
    }
}