<?php namespace spec\rtens\domin\delivery\web\fields;

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

    function inflateStrings() {
        $this->assertInflated('foo:00', 0, 0, 0);
        $this->assertInflated('   bar:12    ', 0, 0, 12);
        $this->assertInflated('3:00', 0, 3, 0);
        $this->assertInflated('23:59', 0, 23, 59);
        $this->assertInflated('75:76', 0, 75, 76);
        $this->assertInflated('412 75:42', 412, 75, 42);
        $this->assertInflated('412d 75:42', 412, 75, 42);
    }

    function noHeadElements() {
        $this->assert($this->field->headElements(new Parameter('foo', new ClassType(\DateInterval::class))), []);
    }

    function render() {
        $param = new Parameter('foo', new ClassType(\DateInterval::class));
        $this->assert($this->field->render($param, new \DateInterval('P7DT45H76M')),
            '<input class="form-control" type="text" name="foo" value="7d 45:76" placeholder="[d[\'d\']] hh:mm (eg \'3d 12:42\')"/>');

        $this->assert->contains($this->field->render($param, new \DateInterval('P0DT0H0M')),
            'value="00:00"');

        $requiredParam = new Parameter('foo', new ClassType(\DateInterval::class), true);
        $this->assert->contains($this->field->render($requiredParam, new \DateInterval('PT0S')),
            'required="required"');

    }

    private function assertInflated($string, $days, $hours, $minutes) {
        $param = new Parameter('foo', new ClassType(\DateInterval::class));
        $this->assert($this->field->inflate($param, $string)->d, $days);
        $this->assert($this->field->inflate($param, $string)->h, $hours);
        $this->assert($this->field->inflate($param, $string)->i, $minutes);
    }
}