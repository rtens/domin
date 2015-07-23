<?php
namespace spec\rtens\domin\reflection;

use rtens\domin\ActionRegistry;
use rtens\domin\reflection\GenericObjectAction;
use rtens\domin\reflection\ObjectActionGenerator;
use rtens\domin\reflection\types\TypeFactory;
use rtens\mockster\arguments\Argument as A;
use rtens\mockster\Mockster as M;
use rtens\scrut\tests\statics\StaticTestSuite;

/**
 * @property \rtens\scrut\fixtures\FilesFixture file <-
 */
class ObjectActionGeneratorSpec extends StaticTestSuite {

    function emptyFolder() {
        $this->file->givenTheFolder('empty/folder');

        $registry = new ActionRegistry();
        $generator = new ObjectActionGenerator($registry, new TypeFactory());

        $generator->fromFolder($this->file->fullPath('empty/folder'), 'is_null');

        $this->assert->size($registry->getAllActions(), 0);
    }

    function findClasses() {
        $this->file->givenTheFile_Containing('folder/one.php', '<?php class OneObjectAction {}');
        $this->file->givenTheFile_Containing('folder/two.php', '<?php class TwoObjectAction {}');
        $this->file->givenTheFile_Containing('folder/three.txt', '<?php class ThreeObjectAction {}');

        $registry = M::of(ActionRegistry::class);
        (new ObjectActionGenerator(M::mock($registry), new TypeFactory()))->fromFolder($this->file->fullPath('folder'), 'is_null');

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
        (new ObjectActionGenerator(M::mock($registry), new TypeFactory()))
            ->fromFolder($this->file->fullPath('folder'), function ($object) {
                return $object->one . ':' . $object->two;
            });

        /** @var GenericObjectAction $action */
        $action = M::stub($registry->add('barObjectAction', A::any()))->has()->inCall(0)->argument('action');
        $this->assert($action->execute(['one' => 'foo', 'two' => 'bar']), 'foo:bar');
    }

    function extendExecute() {
        $this->file->givenTheFile_Containing('folder/foo.php', '<?php namespace extendExecute; class FooObjectAction {}');
        $this->file->givenTheFile_Containing('folder/bar.php', '<?php namespace extendExecute; class BarObjectAction {}');

        $execute = function ($object) {
            return get_class($object);
        };
        $actions = new ActionRegistry();
        $generator = new ObjectActionGenerator($actions, new TypeFactory());
        $generator->fromFolder($this->file->fullPath('folder'), $execute);

        $generator->get('extendExecute\FooObjectAction')->setExecute(function ($object) use ($execute) {
            return '(' . $execute($object) . ')';
        });
        $this->assert($actions->getAction('fooObjectAction')->execute([]), '(extendExecute\FooObjectAction)');
        $this->assert($actions->getAction('barObjectAction')->execute([]), 'extendExecute\BarObjectAction');
    }

    function afterExecute() {
        $this->file->givenTheFile_Containing('folder/foo.php', '<?php namespace after\execute; class FooObjectAction {}');

        $actions = new ActionRegistry();
        (new ObjectActionGenerator($actions, new TypeFactory()))
            ->fromFolder($this->file->fullPath('folder'), function ($object) {
                return get_class($object);
            })
            ->configure('after\execute\FooObjectAction', function (GenericObjectAction $action) {
                $action->setAfterExecute(function ($result) {
                    return $result . '!';
                });
            });

        $this->assert($actions->getAction('fooObjectAction')->execute([]), 'after\execute\FooObjectAction!');
    }

    function fillParameters() {
        $this->file->givenTheFile_Containing('folder/file.php', '<?php namespace fill; class FooObjectAction {
            /** @var string */
            public $one = "default";
            /** @var string */
            public $two;
        }');

        $actions = new ActionRegistry();
        (new ObjectActionGenerator($actions, new TypeFactory()))
            ->fromFolder($this->file->fullPath('folder'), 'is_null')
            ->configure('fill\FooObjectAction', function (GenericObjectAction $action) {
                $action->setFill(function ($parameters) {
                    $parameters['two'] = 'foo';
                    return $parameters;
                });
            });

        $this->assert($actions->getAction('fooObjectAction')->fill([]), ['one' => 'default', 'two' => 'foo']);
    }
}