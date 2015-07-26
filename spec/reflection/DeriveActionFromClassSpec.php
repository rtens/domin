<?php
namespace spec\rtens\domin\reflection;

use rtens\domin\Action;
use rtens\domin\reflection\CommentParser;
use rtens\domin\reflection\ObjectAction;
use rtens\domin\reflection\types\TypeFactory;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\BooleanType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\StringType;

class DeriveActionFromClassSpec extends StaticTestSuite {

    function captionIsClassName() {
        $this->givenTheClass('class AFooClass2 {}');
        $this->whenICreateAnObjectActionFrom('AFooClass2');
        $this->thenItShouldHaveTheCaption('A Foo Class 2');
        $this->thenItShouldHave_Parameters(0);
    }

    function writablePropertiesAreParameters() {
        $this->givenTheClass('class ActionClass1 {
            public $public;
            private $private;
            function __construct($constructor) {}
            function setSetter($foo) {}
            function getGetter() {}
        }');
        $this->whenICreateAnObjectActionFrom('ActionClass1');
        $this->thenItShouldHave_Parameters(3);
        $this->thenParameter_ShouldBe(1, 'constructor');
        $this->thenParameter_ShouldBe(2, 'public');
        $this->thenParameter_ShouldBe(3, 'setter');
    }

    function constructorArgumentsAreRequired() {
        $this->givenTheClass('class ActionClass2 {
            public $public;
            function __construct($required, $optional = null) {}
            function setSetter($foo) {}
        }');
        $this->whenICreateAnObjectActionFrom('ActionClass2');
        $this->thenItShouldHave_Parameters(4);
        $this->thenParameter_ShouldBeRequired(1);
        $this->thenParameter_ShouldBeOptional(2);
        $this->thenParameter_ShouldBeOptional(3);
        $this->thenParameter_ShouldBeOptional(4);
    }

    function readTypesFromHints() {
        $this->givenTheClass('class ActionClass3 {
            /** @var int */
            public $public;
            /**
             * @param string $two
             */
            function __construct(\DateTime $one, $two) {}
            /**
             * @param null|boolean $foo
             */
            function setSetter($foo) {}
        }');
        $this->whenICreateAnObjectActionFrom('ActionClass3');
        $this->thenItShouldHave_Parameters(4);
        $this->thenParameter_ShouldHaveTheType(1, new ClassType(\DateTime::class));
        $this->thenParameter_ShouldHaveTheType(2, new StringType());
        $this->thenParameter_ShouldHaveTheType(3, new IntegerType());
        $this->thenParameter_ShouldHaveTheType(4, new NullableType(new BooleanType()));
    }

    function buildInstanceForExecution() {
        $this->givenTheClass('class ClassAction4 {
            public $public;
            function __construct($one, $two, $three = null, $four = null) {
                $this->one = $one;
                $this->two = $two;
                $this->four = $four;
            }
            function setSetter($foo) {
                $this->foo = $foo;
            }
            function setNotGiven($foo) {}
        }');
        $this->whenICreateAnObjectActionFrom('ClassAction4');
        $this->whenIExecuteThatActionWith([
            'one' => 'hey',
            'two' => 'ho',
            'four' => 'foo',
            'public' => 'lets',
            'setter' => 'go'
        ]);
        $this->thenItShouldBeExecutedWithAnInstanceOf('ClassAction4');
        $this->thenItsProperty_ShouldBe('one', 'hey');
        $this->thenItsProperty_ShouldBe('two', 'ho');
        $this->thenItsProperty_ShouldBe('four', 'foo');
        $this->thenItsProperty_ShouldBe('public', 'lets');
        $this->thenItsProperty_ShouldBe('foo', 'go');
    }

    function fillParametersWithDefaultValues() {
        $this->givenTheClass('class ClassAction5 {
            public $public = "foo";
            function __construct($required, $one = "bar", $two = "for", $three = "fos") {}
            function setSetter($foo = "bas") {}
        }');
        $this->whenICreateAnObjectActionFrom('ClassAction5');
        $this->whenIFillTheParameters([
            'one' => null,
            'two' => 'me'
        ]);
        $this->thenFilledParameter_ShouldBe('one', null);
        $this->thenFilledParameter_ShouldBe('two', "me");
        $this->thenFilledParameter_ShouldBe('three', "fos");
        $this->thenFilledParameter_ShouldBe('public', "foo");
        $this->thenFilledParameter_ShouldBe('setter', null);
    }

    function useDocCommentAsDescription() {
        $this->givenTheClass('
            /**
             * My doc comment
             *
             * *In many *lines*
             */
            class ClassAction6 {
                /** @var string Some comment */
                public $public;

                /**
                 * @param string $one Comment one
                 */
                function __construct($one) {}
            }');

        $this->whenICreateAnObjectActionFrom('ClassAction6');
        $this->thenTheDescriptionShouldBe("My doc comment\n\nIn many *lines* (parsed)");
        $this->thenParameter_ShouldHaveTheDescription(1, 'Comment one (parsed)');
        $this->thenParameter_ShouldHaveTheDescription(2, 'Some comment (parsed)');
    }

    /** @var Action */
    private $uut;

    /** @var ObjectAction|Mockster */
    private $action;

    private $instance;

    /** @var array */
    private $filledParameters;

    private function givenTheClass($code) {
        eval($code);
    }

    private function whenICreateAnObjectActionFrom($className) {
        $this->action = Mockster::of(ObjectAction::class);
        $parser = Mockster::of(CommentParser::class);
        Mockster::stub($parser->parse(Argument::any()))->will()->forwardTo(function ($comment) {
            return $comment . ' (parsed)';
        });
        $this->uut = Mockster::uut($this->action, [$className, new TypeFactory(), Mockster::mock($parser)]);
    }

    private function whenIExecuteThatActionWith($parameters) {
        $this->action->__call('executeWith', [Argument::any()])->will()->forwardTo(function ($object) {
            $this->instance = $object;
        });

        $this->uut->execute($parameters);
    }

    private function whenIFillTheParameters($parameters) {
        $this->filledParameters = $this->uut->fill($parameters);
    }

    private function thenItShouldHaveTheCaption($string) {
        $this->assert($this->uut->caption(), $string);
    }

    private function thenItShouldHave_Parameters($count) {
        $this->assert->size($this->uut->parameters(), $count);
    }

    private function thenParameter_ShouldBe($pos, $name) {
        $this->assert($this->uut->parameters()[$pos - 1]->getName(), $name);
    }

    private function thenParameter_ShouldBeRequired($pos) {
        $this->assert($this->uut->parameters()[$pos - 1]->isRequired());
    }

    private function thenParameter_ShouldBeOptional($pos) {
        $this->assert->not($this->uut->parameters()[$pos - 1]->isRequired());
    }

    private function thenParameter_ShouldHaveTheType($pos, $type) {
        $this->assert($this->uut->parameters()[$pos - 1]->getType(), $type);
    }

    private function thenParameter_ShouldHaveTheDescription($pos, $string) {
        $this->assert($this->uut->parameters()[$pos - 1]->getDescription(), $string);
    }

    private function thenItShouldBeExecutedWithAnInstanceOf($class) {
        $this->assert->isInstanceOf($this->instance, $class);
    }

    private function thenItsProperty_ShouldBe($key, $value) {
        $this->assert($this->instance->$key, $value);
    }

    private function thenFilledParameter_ShouldBe($name, $value) {
        $this->assert($this->filledParameters[$name], $value);
    }

    private function thenTheDescriptionShouldBe($string) {
        $this->assert($this->uut->description(), $string);
    }
}