<?php namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\delivery\cli\fields\DateIntervalField as CliDateIntervalField;
use rtens\domin\delivery\web\fields\DateIntervalField;
use rtens\domin\Parameter;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\ClassType;

class DateIntervalFieldSpec extends StaticTestSuite {

    /** @var DateIntervalField */
    private $field;

    protected function before() {
        $this->field = new DateIntervalField();
    }


    function handlesDateInterval() {
        $this->assert($this->field->handles(new Parameter('foo', new ClassType(\DateInterval::class))));
        $this->assert->not($this->field->handles(new Parameter('foo', new ClassType(\DateTime::class))));
    }

    function doNotInflateEmptyString() {
        $param = new Parameter('foo', new ClassType(\DateInterval::class));
        $this->assert->isNull($this->field->inflate($param, ''));
    }

    function inflateStrings() {
        $this->assertInflatedCli('foo:00', 0, 0, 0);
        $this->assertInflatedCli('   bar:12    ', 0, 0, 12);
        $this->assertInflatedCli('3:00', 0, 3, 0);
        $this->assertInflatedCli('23:59', 0, 23, 59);
        $this->assertInflatedCli('75:76', 0, 75, 76);
        $this->assertInflatedCli('412 75:42', 412, 75, 42);
        $this->assertInflatedCli('412d 75:42', 412, 75, 42);
    }

    function inflateWebInput() {
        $param = new Parameter('foo', new ClassType(\DateInterval::class));

        $this->assert($this->field->inflate($param, null), null);
        $this->assert($this->field->inflate($param, ['d' => 2, 'h' => 5, 'i' => 3]), new \DateInterval('P2DT5H3M'));
        $this->assert($this->field->inflate($param, ['d' => '', 'h' => '', 'i' => '']), new \DateInterval('P0DT0H0M'));

        $requiredParam = new Parameter('foo', new ClassType(\DateInterval::class), true);
        $this->assert($this->field->inflate($requiredParam, null), new \DateInterval('P0D'));
    }

    function noHeadElements() {
        $this->assert($this->field->headElements(new Parameter('foo', new ClassType(\DateInterval::class))), []);
    }

    function render() {
        $param = new Parameter('foo', new ClassType(\DateInterval::class));

        $this->assert($this->field->render($param, null),
            '<div>' . "\n" .
            '<input class="form-control-inline" type="number" size="3" name="foo[d]" value=""/>' . "\n" .
            '<span>days</span>' . "\n" .
            '<input class="form-control-inline" type="number" size="3" name="foo[h]" value=""/>' . "\n" .
            '<span>hours</span>' . "\n" .
            '<input class="form-control-inline" type="number" size="3" name="foo[i]" value=""/>' . "\n" .
            '<span>minutes</span>' . "\n" .
            '</div>');

        $rendered = $this->field->render($param, new \DateInterval('P7DT45H76M'));
        $this->assert->contains($rendered, 'name="foo[d]" value="7"');
        $this->assert->contains($rendered, 'name="foo[h]" value="45"');
        $this->assert->contains($rendered, 'name="foo[i]" value="76"');

        $rendered = $this->field->render($param, new \DateInterval('P0DT0H0M'));
        $this->assert->contains($rendered, 'name="foo[d]" value="0"');
        $this->assert->contains($rendered, 'name="foo[h]" value="0"');
        $this->assert->contains($rendered, 'name="foo[i]" value="0"');
    }

    private function assertInflatedCli($string, $days, $hours, $minutes) {
        $field = new CliDateIntervalField;
        $param = new Parameter('foo', new ClassType(\DateInterval::class));
        $this->assert($field->inflate($param, $string)->d, $days);
        $this->assert($field->inflate($param, $string)->h, $hours);
        $this->assert($field->inflate($param, $string)->i, $minutes);
    }
}