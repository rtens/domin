<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\fields\ActionField;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;
use rtens\mockster\arguments\Argument as Arg;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\UnknownType;

/**
 * @property \spec\rtens\domin\fixtures\ActionFixture action <-
 */
class ActionFieldSpec extends StaticTestSuite {

    function collectHeadElementsFromFields() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'bar');
        $this->action->given_HasTheParameter('foo', 'bas');

        $this->givenAWebFieldRequiringTheHeadElements(function (Parameter $parameter) {
            return [
                new Element($parameter->getName()),
            ];
        });

        $elements = $this->whenIGetTheHeadElementsOf('foo');

        $this->assert->size($elements, 3);
        $this->assert->contains((string)$elements[0], "$('.description').popover");
        $this->assert((string)$elements[1], '<bar></bar>');
        $this->assert((string)$elements[2], '<bas></bas>');
    }

    function showFields() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheParameter('foo', 'two');

        $this->givenAWebFieldRenderingWith(function (Parameter $p) {
            return 'Field['. $p->getName() . ']';
        });

        $rendered = $this->whenIRender('foo');

        $this->assert->contains($rendered, 'Field[one]');
        $this->assert->contains($rendered, 'Field[two]');
    }

    function fillFieldsWithParameters() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheParameter('foo', 'two');

        $this->givenAWebFieldRenderingWith(function (Parameter $p, $value) {
            return $p->getName() . ':' . $value;
        });

        $rendered = $this->whenIRenderWith('foo', ['two' => 'dos', 'three' => 'not a parameter']);

        $this->assert->contains($rendered, 'one:');
        $this->assert->contains($rendered, 'two:dos');
        $this->assert->not()->contains($rendered, 'three:');
    }

    function markRequiredFields() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheRequiredParameter('foo', 'two');

        $this->givenAWebFieldRenderingWith(function (Parameter $p, $value) {
            return $p->getName() . ($p->isRequired() ? '*' : '') . ':' . $value;
        });

        $rendered = $this->whenIRender('foo');

        $this->assert->contains($rendered, 'one:');
        $this->assert->contains($rendered, 'Two*');
        $this->assert->contains($rendered, 'two*:');
    }

    function fillParametersByAction() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'bar');
        $this->action->given_HasTheParameter('foo', 'bas');

        $this->givenAWebFieldRenderingWith(function (Parameter $p, $value) {
            return $p->getName() . ':' . $value;
        });

        $this->action->given_FillsParametersWith('foo', function ($params) {
            $params['bas'] = $params['bar'] . '!';
            return $params;
        });

        $rendered = $this->whenIRenderWith('foo', ['bar' => 'hey']);

        $this->assert->contains($rendered, 'bar:hey');
        $this->assert->contains($rendered, 'bas:hey!');
    }

    function showActionDescription() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheDescription('foo', 'Some Description');
        $rendered = $this->whenIRender('foo');
        $this->assert->contains($rendered, 'Some Description');
    }

    function showFieldDescription() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->action->given_HasTheParameter_WithTheDescription('foo', 'two', 'This is "foo"');
        $this->givenAWebFieldRenderingWith(function () {
            return '!';
        });

        $rendered = $this->whenIRender('foo');
        $this->assert->contains($rendered, 'This is "foo"');
    }

    function showError() {
        $this->action->givenTheAction('foo');
        $this->action->given_HasTheParameter('foo', 'one');
        $this->givenAWebFieldRenderingWith(function () {
            return '...';
        });

        $rendered = $this->whenIRenderWithErrors('foo', ['one' => new \Exception('Foo!')]);
        $this->assert->contains($rendered, '<div class="alert alert-danger">Foo!</div>');
    }

    /** @var RendererRegistry */
    private $renderers;

    /** @var FieldRegistry */
    public $fields;

    /** @var ActionField */
    private $actionField;

    protected function before() {
        $this->renderers = new RendererRegistry();
        $this->fields = new FieldRegistry();
        $this->actionField = new ActionField($this->fields, $this->action->registry);
    }

    private function givenAWebFieldRequiringTheHeadElements($elements) {
        $field = Mockster::of(WebField::class);
        $this->fields->add(Mockster::mock($field));

        Mockster::stub($field->handles(Arg::any()))->will()->return_(true);
        Mockster::stub($field->inflate(Arg::any(), Arg::any()))->will()->forwardTo(function ($s) {
            return $s . '(inflated)';
        });
        Mockster::stub($field->headElements(Arg::any()))->will()->forwardTo($elements);
    }

    private function givenAWebFieldRenderingWith($callback) {
        $field = Mockster::of(WebField::class);
        $this->fields->add(Mockster::mock($field));

        Mockster::stub($field->handles(Arg::any()))->will()->return_(true);
        Mockster::stub($field->render(Arg::any(), Arg::any()))->will()->forwardTo($callback);
    }

    private function whenIGetTheHeadElementsOf($action) {
        return $this->actionField->headElements(new Parameter($action, new UnknownType()));
    }

    private function whenIRender($action) {
        return $this->whenIRenderWith($action, []);
    }

    private function whenIRenderWith($action, $parameters) {
        return $this->whenIRenderWithParameters_AndErrors($action, $parameters, []);
    }

    private function whenIRenderWithErrors($action, $errors) {
        return $this->whenIRenderWithParameters_AndErrors($action, [], $errors);
    }

    private function whenIRenderWithParameters_AndErrors($action, $parameters, $errors) {
        return $this->actionField->render(new Parameter($action, new UnknownType()), [
            'inflated' => $parameters, 'errors' => $errors]);
    }
}