<?php
namespace spec\rtens\domin\web\fields;

use rtens\domin\parameters\File;
use rtens\domin\parameters\SavedFile;
use rtens\domin\Parameter;
use rtens\domin\web\fields\FileField;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\protocol\UploadedFile;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\UnknownType;

class FileFieldSpec extends StaticTestSuite {

    function handlesFiles() {
        $field = new FileField();
        $this->assert->not($field->handles(new Parameter('foo', new ClassType(\StdClass::class))));
        $this->assert($field->handles(new Parameter('foo', new ClassType(File::class))));
    }

    function doNotInflateNull() {
        $field = new FileField();
        $this->assert($field->inflate(null), null);
    }

    function inflateUploadedFile() {
        $field = new FileField();
        $this->assert($field->inflate(new UploadedFile('foo', 'foo/type', 'tmp/name', 0, 100)),
            new SavedFile('tmp/name', 'foo', 'foo/type'));
    }

    function optionalField() {
        $field = new FileField();
        $this->assert($field->render(new Parameter('foo', new UnknownType('file')), null),
            '<input type="file" name="foo"/>');
    }

    function requiredField() {
        $field = new FileField();
        $this->assert($field->render(new Parameter('foo', new IntegerType(), true), null),
            '<input type="file" name="foo" required="required"/>');
    }
}