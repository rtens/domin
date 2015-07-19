<?php
namespace spec\rtens\domin\reflection;

use rtens\domin\ActionRegistry;
use rtens\domin\Parameter;
use rtens\domin\reflection\GenericMethodAction;
use rtens\domin\reflection\MethodAction;
use rtens\domin\reflection\MethodActionGenerator;
use rtens\domin\reflection\TypeFactory;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\StringType;

class DeriveActionsFromMethodSpec extends StaticTestSuite {

    function noParameters() {
        eval("class SomeEmptyMethod {
            function doStuff() {
                return 'did it';
            }
        }");

        /** @noinspection PhpUndefinedClassInspection */
        $action = new MethodAction(new \SomeEmptyMethod(), 'doStuff', new TypeFactory());

        $this->assert($action->caption(), 'Do Stuff');
        $this->assert($action->parameters(), []);
        $this->assert($action->fill(['foo', 'bar']), ['foo', 'bar']);
        $this->assert($action->execute([]), 'did it');
    }

    function withParameters() {
        eval('class MethodWithSimpleParams {
            /**
             * @param string $one
             * @param null|string $two
             * @param string $three
             */
            function doStuffWithStuff($one, $two = "default", $three = "default") {
                return $one . ":" . $two;
            }
        }');

        /** @noinspection PhpUndefinedClassInspection */
        $action = new MethodAction(new \MethodWithSimpleParams(), 'doStuffWithStuff', new TypeFactory());

        $this->assert($action->parameters(), [
            new Parameter('one', new StringType(), true),
            new Parameter('two', new NullableType(new StringType())),
            new Parameter('three', new StringType()),
        ]);
        $this->assert($action->execute(['one' => 'foo', 'two' => 'bar']), 'foo:bar');
        $this->assert($action->fill(['two' => null, 'three' => 'foo']), [
            'three' => 'foo',
            'two' => 'default'
        ]);
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
        (new MethodActionGenerator($actions, new TypeFactory()))
            ->fromObject($object)
            ->configure($object, 'doThis', function (GenericMethodAction $action) {
                $action->setFill(function ($p) {
                    $p['one'] = 'foo';
                    return $p;
                });
            })
            ->configure($object, 'doThat', function (GenericMethodAction $action) {
                $action
                    ->setAfterExecute(function ($s) {
                        return $s . '!';
                    })
                    ->setCaption('That');
            });

        $this->assert->size($actions->getAllActions(), 2);
        $this->assert($actions->getAction('ClassWithSomeMethods:doThis')->execute([
            'one' => 'foo',
            'two' => 'bar'
        ]), 'foo:bar');
        $this->assert($actions->getAction('ClassWithSomeMethods:doThis')->fill([]), [
            'one' => 'foo',
            'two' => 'default'
        ]);
        $this->assert($actions->getAction('ClassWithSomeMethods:doThat')->execute([]), 'foo!');
        $this->assert($actions->getAction('ClassWithSomeMethods:doThat')->caption(), 'That');
    }
}