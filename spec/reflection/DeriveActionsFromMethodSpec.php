<?php
namespace spec\rtens\domin\reflection;

use rtens\domin\ActionRegistry;
use rtens\domin\Parameter;
use rtens\domin\reflection\CommentParser;
use rtens\domin\reflection\GenericMethodAction;
use rtens\domin\reflection\MethodAction;
use rtens\domin\reflection\MethodActionGenerator;
use rtens\domin\reflection\types\TypeFactory;
use rtens\mockster\arguments\Argument;
use rtens\mockster\Mockster;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\ArrayType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\StringType;

class DeriveActionsFromMethodSpec extends StaticTestSuite {

    function noParameters() {
        eval("class SomeEmptyMethod {
            function doStuff() {
                return 'did it';
            }
        }");

        $action = $this->createMethodAction('SomeEmptyMethod', 'doStuff');

        $this->assert($action->caption(), 'Some Empty Method: Do Stuff');
        $this->assert($action->parameters(), []);
        $this->assert($action->fill(['foo', 'bar']), ['foo', 'bar']);
        $this->assert($action->execute([]), 'did it');
    }

    function withParameters() {
        eval('class MethodWithSimpleParams {
            /**
             * @param string $one
             * @param string $two
             * @param string $three
             * @param string $four
             * @param string $five
             */
            function doStuffWithStuff($one, $two = "default", $three = "", $four = null, $five = "this") {
                return $one . ":" . $two;
            }
        }');

        $action = $this->createMethodAction('MethodWithSimpleParams', 'doStuffWithStuff');

        $this->assert($action->parameters(), [
            new Parameter('one', new StringType(), true),
            new Parameter('two', new StringType()),
            new Parameter('three', new StringType()),
            new Parameter('four', new NullableType(new StringType())),
            new Parameter('five', new StringType()),
        ]);
        $this->assert($action->execute(['one' => 'foo', 'two' => 'bar']), 'foo:bar');
        $this->assert($action->fill(['two' => null, 'three' => 'foo']), [
            'two' => null,
            'three' => 'foo',
            'four' => null,
            'five' => 'this'
        ]);
    }

    function getDescriptionFromDocComment() {
        eval('class ClassWithCommentedMethod {
            /**
             * This describes the method.
             *
             * In possibly multiple lines.
             *
             * @param string $one Comment one
             * @param string $two Comment two
             */
            function doStuff($one, $two) {}
        }');

        $action = $this->createMethodAction('ClassWithCommentedMethod', 'doStuff');

        $this->assert($action->description(), "This describes the method.\n\nIn possibly multiple lines. (parsed)");

        $parameters = $action->parameters();
        $this->assert($parameters[0]->getDescription(), "Comment one (parsed)");
        $this->assert($parameters[1]->getDescription(), "Comment two (parsed)");
    }

    function generateFromMethods() {
        eval('class ClassWithSomeMethods {
            /**
             * @param string $one
             * @param null|string $two
             */
            function doThis($one, $two = "default") {
                return $one . ":" . $two;
            }

            function doThat() {
                return "foo";
            }

            function _notMe() {}

            protected function notThis() {}

            static function neitherThis() {}
        }');

        /** @noinspection PhpUndefinedClassInspection */
        $object = new \ClassWithSomeMethods();

        $actions = new ActionRegistry();
        (new MethodActionGenerator($actions, new TypeFactory(), new CommentParser()))
            ->fromObject($object)
            ->configure($object, 'doThis', function (GenericMethodAction $action) {
                $action->generic()
                    ->setFill(function ($p) {
                        $p['one'] = 'foo';
                        return $p;
                    })
                    ->setDescription('My description')
                    ->setCaption('My caption')
                    ->mapParameter('one', function (Parameter $one) {
                        return new Parameter('one', new ArrayType($one->getType()));
                    });
            })
            ->configure($object, 'doThat', function (GenericMethodAction $action) {
                $action->generic()
                    ->setAfterExecute(function ($s) {
                        return $s . '!';
                    })
                    ->setCaption('That');
            });

        $this->assert->size($actions->getAllActions(), 2);

        $doThis = $actions->getAction('ClassWithSomeMethods:doThis');
        $this->assert($doThis->description(), 'My description');
        $this->assert($doThis->caption(), 'My caption');
        $this->assert($doThis->execute([
            'one' => 'foo',
            'two' => 'bar'
        ]), 'foo:bar');
        $this->assert($doThis->fill([
            'one' => 'bar'
        ]), [
            'one' => 'foo',
            'two' => 'default'
        ]);
        $this->assert($doThis->parameters()[0], new Parameter('one', new ArrayType(new StringType())));

        $doThat = $actions->getAction('ClassWithSomeMethods:doThat');
        $this->assert($doThat->description(), '');
        $this->assert($doThat->execute([]), 'foo!');
        $this->assert($doThat->caption(), 'That');
    }

    private function createMethodAction($class, $method) {
        $parser = Mockster::of(CommentParser::class);
        Mockster::stub($parser->parse(Argument::any()))->will()->forwardTo(function ($comment) {
            if (!$comment) {
                return null;
            }
            return $comment . ' (parsed)';
        });
        return new MethodAction(new $class(), $method, new TypeFactory(), Mockster::mock($parser));
    }
}