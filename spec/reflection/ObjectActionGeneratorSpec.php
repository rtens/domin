<?php
namespace spec\rtens\domin\reflection;

use rtens\domin\ActionRegistry;
use rtens\domin\Parameter;
use rtens\domin\parameters\Html;
use rtens\domin\reflection\CommentParser;
use rtens\domin\reflection\GenericObjectAction;
use rtens\domin\reflection\ObjectActionGenerator;
use rtens\domin\reflection\types\TypeFactory;
use rtens\mockster\arguments\Argument as A;
use rtens\mockster\Mockster as M;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\NullableType;
use watoki\reflect\type\StringType;

/**
 * @property \rtens\scrut\fixtures\FilesFixture file <-
 */
class ObjectActionGeneratorSpec extends StaticTestSuite {

    function emptyFolder() {
        $this->file->givenTheFolder('empty/folder');

        $registry = new ActionRegistry();
        $generator = $this->createGenerator($registry);

        $generator->fromFolder($this->file->fullPath('empty/folder'), 'is_null');

        $this->assert->size($registry->getAllActions(), 0);
    }

    function findClasses() {
        $this->file->givenTheFile_Containing('folder/one.php', '<?php class OneObjectAction {}');
        $this->file->givenTheFile_Containing('folder/two.php', '<?php class TwoObjectAction {}');
        $this->file->givenTheFile_Containing('folder/three.txt', '<?php class ThreeObjectAction {}');

        $registry = M::of(ActionRegistry::class);
        $this->createGenerator(M::mock($registry))->fromFolder($this->file->fullPath('folder'), 'is_null');

        $this->assert(M::stub($registry->add('oneObjectAction', A::any()))->has()->beenCalled());
        $this->assert(M::stub($registry->add('twoObjectAction', A::any()))->has()->beenCalled());

        $action = M::stub($registry->add(A::any(), A::any()))->has()->inCall(0)->argument('action');
        $this->assert->isInstanceOf($action, GenericObjectAction::class);
    }

    function executeWithObject() {
        $this->file->givenTheFile_Containing('folder/file.php', '<?php class BarObjectAction {
            /** @var string */
            public $one;
            /** @var string */
            public $two;
        }');

        $registry = M::of(ActionRegistry::class);
        $this->createGenerator(M::mock($registry))
            ->fromFolder($this->file->fullPath('folder'), function ($object) {
                return $object->one . ':' . $object->two;
            });

        /** @var GenericObjectAction $action */
        $action = M::stub($registry->add('barObjectAction', A::any()))->has()->inCall(0)->argument('action');
        $this->assert($action->execute(['one' => 'foo', 'two' => 'bar']), 'foo:bar');
        $this->assert($action->description(), '');
    }

    function extendExecute() {
        $this->file->givenTheFile_Containing('folder/foo.php', '<?php namespace extendExecute; class FooObjectAction {}');
        $this->file->givenTheFile_Containing('folder/bar.php', '<?php namespace extendExecute; class BarObjectAction {}');

        $execute = function ($object) {
            return get_class($object);
        };
        $actions = new ActionRegistry();
        $generator = $this->createGenerator($actions);
        $generator->fromFolder($this->file->fullPath('folder'), $execute);

        $generator->get('extendExecute\FooObjectAction')->generic()->setExecute(function ($params) {
            return '(' . implode(',', $params) . ')';
        });
        $this->assert($actions->getAction('fooObjectAction')->execute(['foo']), '(foo)');
        $this->assert($actions->getAction('barObjectAction')->execute(['bar']), 'extendExecute\BarObjectAction');
    }

    function afterExecute() {
        $this->file->givenTheFile_Containing('folder/foo.php', '<?php namespace after\execute; class FooObjectAction {}');

        $actions = new ActionRegistry();
        $this->createGenerator($actions)
            ->fromFolder($this->file->fullPath('folder'), function ($object) {
                return get_class($object);
            })
            ->configure('after\execute\FooObjectAction', function (GenericObjectAction $action) {
                $action->generic()
                    ->setAfterExecute(function ($result) {
                        return $result . '!';
                    })
                    ->setDescription('My description')
                    ->setCaption('My caption');
            });

        $action = $actions->getAction('fooObjectAction');
        $this->assert($action->execute([]), 'after\execute\FooObjectAction!');
        $this->assert($action->description(), 'My description');
        $this->assert($action->caption(), 'My caption');
    }

    function fillParameters() {
        $this->file->givenTheFile_Containing('folder/file.php', '<?php namespace fill; class FooObjectAction {
            /** @var string */
            public $one = "default";
            /** @var string */
            public $two;
        }');

        $actions = new ActionRegistry();
        $this->createGenerator($actions)
            ->fromFolder($this->file->fullPath('folder'), 'is_null')
            ->configure('fill\FooObjectAction', function (GenericObjectAction $action) {
                $action->generic()->setFill(function ($parameters) {
                    $parameters['two'] = 'foo';
                    return $parameters;
                });
            });

        $this->assert($actions->getAction('fooObjectAction')->fill([]), ['one' => 'default', 'two' => 'foo']);
    }

    function mapParameters() {
        $this->file->givenTheFile_Containing('folder/foo.php', '<?php
        namespace params;
        class FooObjectAction {
            /** @var string */
            public $one;
            /** @var string */
            public $two;
        }');

        $actions = new ActionRegistry();
        $this->createGenerator($actions)
            ->fromFolder($this->file->fullPath('folder'), 'is_null')
            ->configure('params\FooObjectAction', function (GenericObjectAction $action) {
                $action->generic()
                    ->mapParameter('one', function () {
                        return new Parameter('one', new ClassType(Html::class));
                    })
                    ->mapParameter('two', function (Parameter $two) {
                        return new Parameter('two', new NullableType($two->getType()));
                    });
            });;

        $parameters = $actions->getAction('fooObjectAction')->parameters();
        $this->assert($parameters, [
            new Parameter('one', new ClassType(Html::class)),
            new Parameter('two', new NullableType(new StringType()))
        ]);
    }

    private function createGenerator(ActionRegistry $actions) {
        return new ObjectActionGenerator($actions, new TypeFactory(), new CommentParser());
    }
}