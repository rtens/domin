<?php
namespace spec\rtens\domin\reflection;

use rtens\domin\reflection\types\EnumerationType;
use rtens\domin\reflection\types\TypeFactory;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\reflect\PropertyReader;
use watoki\reflect\type\StringType;

class EnumerationTypesSpec extends StaticTestSuite {

    function determineTypeByHintToOwnConstants() {
        eval('class EnumerationHintToOwnConstants {
            const FOO_ONE = "one";
            const FOO_TWO = "two";

            /** @var self::FOO_ */
            public $foo;
        }');

        $reader = new PropertyReader(new TypeFactory(), 'EnumerationHintToOwnConstants');
        $properties = $reader->readInterface();

        $this->assert($properties['foo']->type(), new EnumerationType(['one', 'two'], new StringType()));
    }

    function determineTypeByHintToOtherConstants() {
        eval('class EnumerationHintToOtherConstants {
            /** @var ClassContainingTheConstants::FOO_ */
            public $foo;
        }');
        eval('class ClassContainingTheConstants {
            const FOO_ONE = "one";
            const FOO_TWO = "two";
        }');

        $reader = new PropertyReader(new TypeFactory(), 'EnumerationHintToOtherConstants');
        $properties = $reader->readInterface();

        $this->assert($properties['foo']->type(), new EnumerationType(['one', 'two'], new StringType()));
    }
}