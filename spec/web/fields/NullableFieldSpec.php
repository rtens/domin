<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\Parameter;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\fields\NullableField;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebField;
use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\Mockster as M;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\UnknownType;

class NullableFieldSpec extends StaticTestSuite {

    /** @var NullableField */
    private $field;

    /** @var WebField */
    private $inside;

    protected function before() {
        $fields = new FieldRegistry();
        $this->inside = M::of(WebField::class);
        $fields->add(M::mock($this->inside));
        $this->field = new NullableField($fields);

        M::stub($this->inside->handles(Arg::any()))->will()->return_(true);
    }

    function handleNullableType() {
        $this->assert($this->field->handles($this->param('foo')));
        $this->assert->not($this->field->handles(new Parameter('foo', new UnknownType())));
    }

    function rendersToggleAndInnerField() {
        M::stub($this->inside->render(Arg::any(), Arg::any()))->will()->forwardTo(function (Parameter $p) {
            return $p->getName() . '!';
        });

        $this->assert($this->field->render($this->param('foo[bar]'), null),
            '<input type="hidden" name="foo[bar]" value="____IS_NULL____"/>' . "\n" .
            '<input type="checkbox" onchange="var control = $(\'#foo-bar--control\').detach(); $(this).is(\':checked\') ? control.show().insertAfter($(this)) : control.hide().appendTo(\'body\');"/>' . "\n" .
            '<div id="foo-bar--control" class="null-nullable">foo[bar]!</div>');
    }

    function rendersInnerFieldWithValue() {
        M::stub($this->inside->render(Arg::any(), Arg::any()))->will()->forwardTo(function (Parameter $p, $v) {
            return $p->getName() . ':' . $v;
        });

        $this->assert->contains($this->field->render($this->param('foo'), 'bar'),
            'checked="checked"/>' . "\n" .
            '<div id="foo-control">foo:bar</div>');
    }

    function inflateValues() {
        M::stub($this->inside->inflate(Arg::any(), Arg::any()))->will()->forwardTo(function (Parameter $p, $v) {
            return $p->getName() . '_' . $v;
        });

        $this->assert($this->field->inflate($this->param('foo'), 'bar'), 'foo_bar');
        $this->assert($this->field->inflate($this->param('foo'), null), null);
        $this->assert($this->field->inflate($this->param('foo'), NullableField::NULL_VALUE), null);
    }

    function requireHeadElementsOfInnerField() {
        M::stub($this->inside->headElements(Arg::any()))->will()->return_([
            new Element('link', ['src' => 'foo.bar']),
            new Element('link', ['src' => 'bar.bas']),
        ]);

        $headElements = implode('', $this->field->headElements($this->param('foo')));
        $this->assert->contains($headElements, (string)HeadElements::jquery());
        $this->assert->contains($headElements, 'src="foo.bar"');
        $this->assert->contains($headElements, 'src="bar.bas"');
    }

    private function param($name) {
        return new Parameter($name, new NullableType(new UnknownType()));
    }
}