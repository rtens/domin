<?php
namespace spec\rtens\domin\delivery\web;

use rtens\domin\Action;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\menu\Menu;
use rtens\domin\delivery\web\menu\MenuGroup;
use rtens\domin\delivery\web\menu\MenuItem;
use rtens\domin\delivery\web\root\ExecuteResource;
use rtens\domin\delivery\web\root\IndexResource;
use rtens\domin\delivery\web\WebApplication;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\cookie\CookieStore;
use watoki\curir\delivery\WebRequest;
use watoki\curir\protocol\Url;
use watoki\deli\Path;
use watoki\factory\Factory;

class ShowMenuSpec extends StaticTestSuite {

    private $model;

    /** @var Menu */
    private $menu;

    /** @var ActionRegistry */
    private $actions;

    /** @var IndexResource */
    private $resource;

    protected function before() {
        $this->actions = new ActionRegistry();
        $this->menu = new Menu($this->actions);
        $this->resource = new IndexResource(new Factory(), Mockster::uut(WebApplication::class),
            $this->actions, $this->menu, Mockster::mock(CookieStore::class));
    }

    function emptyMenuOnOverview() {
        $this->whenIGetTheModel();
        $this->assert($this->model['menuItems'], []);
    }

    function menuWithItems() {
        $action = Mockster::of(Action::class);
        Mockster::stub($action->caption())->will()->return_('My Foo');
        $this->actions->add('foo', Mockster::mock($action));

        $this->menu->add(new MenuItem('foo'));
        $this->menu->add(new MenuItem('foo', [
            'one' => 'uno',
            'two' => 'dos'
        ]));

        $this->whenIGetTheModel();
        $this->assert($this->model['menuItems'], [
            ['caption' => 'My Foo', 'target' => 'http://example.com/base/foo'],
            ['caption' => 'My Foo', 'target' => 'http://example.com/base/foo?one=uno&two=dos'],
        ]);
    }

    function menuWithGroups() {
        $action = Mockster::of(Action::class);
        Mockster::stub($action->caption())->will()->return_('My Foo');
        $this->actions->add('foo', Mockster::mock($action));

        $this->menu->add(new MenuItem('foo'));
        $this->menu->addGroup((new MenuGroup('My Group'))
            ->add(new MenuItem('foo', ['foo' => 'bar']))
            ->add(new MenuItem('foo', ['one' => 'two'])));

        $this->whenIGetTheModel();
        $this->assert($this->model['menuItems'], [
            ['caption' => 'My Foo', 'target' => 'http://example.com/base/foo'],
            ['caption' => 'My Group', 'items' => [
                ['caption' => 'My Foo', 'target' => 'http://example.com/base/foo?foo=bar'],
                ['caption' => 'My Foo', 'target' => 'http://example.com/base/foo?one=two']
            ]]
        ]);
    }

    function menuOnExecution() {
        $action = Mockster::of(Action::class);
        Mockster::stub($action->caption())->will()->return_('My Foo');
        $this->actions->add('foo', Mockster::mock($action));

        $this->menu->add(new MenuItem('foo'));
        $this->menu->add(new MenuItem('foo', [
            'one' => 'uno',
            'two' => 'dos'
        ]));

        $this->resource = new ExecuteResource(new Factory(), $this->actions, new FieldRegistry(), new RendererRegistry(),
            $this->menu, Mockster::mock(CookieStore::class));

        $this->model = $this->resource->doGet('foo', new WebRequest(Url::fromString('http://example.com/base'), new Path()));
        $this->assert($this->model['menuItems'], [
            ['caption' => 'My Foo', 'target' => 'http://example.com/base/foo'],
            ['caption' => 'My Foo', 'target' => 'http://example.com/base/foo?one=uno&two=dos'],
        ]);
    }

    private function whenIGetTheModel() {
        $this->model = $this->resource->doGet(new WebRequest(Url::fromString('http://example.com/base'), new Path()));
    }
}