<?php
namespace spec\rtens\domin\web\renderers;

use rtens\domin\web\renderers\DateTimeRenderer;
use rtens\scrut\tests\statics\StaticTestSuite;

class DateTimeRendererSpec extends StaticTestSuite {

    /** @var DateTimeRenderer */
    private $renderer;

    protected function before() {
        $this->renderer = new DateTimeRenderer();
    }

    function handlesDateTimes() {
        $this->assert($this->renderer->handles(new \DateTime()));
        $this->assert($this->renderer->handles(new \DateTimeImmutable()));
        $this->assert->not($this->renderer->handles(new \StdClass()));
        $this->assert->not($this->renderer->handles('today'));
    }

    function rendersToString() {
        $this->assert($this->renderer->render(new \DateTime('2011-12-13 14:15:16 UTC')), '2011-12-13 14:15:16 +00:00');
    }
}