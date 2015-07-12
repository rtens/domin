<?php
namespace spec\rtens\domin\web\fields;

use rtens\domin\Parameter;
use rtens\domin\web\fields\UploadedFileField;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\protocol\UploadedFile;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\UnknownType;

class UploadedFileFieldSpec extends StaticTestSuite {

    function handlesUploadedFiles() {
        $field = new UploadedFileField();
        $this->assert->not($field->handles(new Parameter('foo', new ClassType(\StdClass::class))));
        $this->assert($field->handles(new Parameter('foo', new ClassType(UploadedFile::class))));
    }

    function optionalField() {
        $field = new UploadedFileField();
        $this->assert($field->render(new Parameter('foo', new UnknownType('file')), null),
            '<input type="file" name="foo"/>');
    }

    function requiredField() {
        $field = new UploadedFileField();
        $this->assert($field->render(new Parameter('foo', new IntegerType(), true), null),
            '<input type="file" name="foo" required="true"/>');
    }
}