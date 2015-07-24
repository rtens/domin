<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\Parameter;
use rtens\domin\parameters\Html;
use rtens\domin\delivery\web\fields\HtmlField;
use rtens\domin\delivery\web\HeadElements;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\StringType;

class HtmlFieldSpec extends StaticTestSuite {

    /** @var HtmlField */
    private $field;

    protected function before() {
        $this->field = new HtmlField();
    }

    function handlesHtmlParameters() {
        $this->assert($this->field->handles(new Parameter('foo', new ClassType(Html::class))));
        $this->assert->not($this->field->handles(new Parameter('foo', new ClassType(\StdClass::class))));
        $this->assert->not($this->field->handles(new Parameter('foo', new StringType())));
    }

    function inflatesToHtmlObject() {
        $this->assert($this->field->inflate(new Parameter('foo', new StringType()), 'some <html/>'),
            new Html('some <html/>'));
    }

    function rendersTextArea() {
        $this->assert($this->field->render(new Parameter('foo', new StringType()), null),
            '<textarea name="foo" class="summernote"></textarea>');
    }

    function rendersRequiredTextArea() {
        $this->assert($this->field->render(new Parameter('foo', new StringType(), true), null),
            '<textarea name="foo" class="summernote" required="required"></textarea>');
    }

    function rendersValue() {
        $this->assert($this->field->render(new Parameter('foo', new StringType()), new Html('foo')),
            '<textarea name="foo" class="summernote">foo</textarea>');
    }

    function requiresJsAndCss() {
        $elements = $this->field->headElements(new Parameter('notUsed', new StringType()));

        $this->assert(array_slice($elements, 0, 4), [
            HeadElements::jquery(),
            HeadElements::bootstrap(),
            HeadElements::bootstrapJs(),
            HeadElements::fontAwesome()
        ]);
        $this->assert->contains((string)$elements[4], 'summernote.min.css');
        $this->assert->contains((string)$elements[5], 'summernote.min.js');
        $this->assert->contains((string)$elements[6], "$(this).val($(this).code());");
    }
}