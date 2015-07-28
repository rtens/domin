<?php
namespace spec\rtens\domin\delivery\web\fields;

use rtens\domin\parameters\File;
use rtens\domin\parameters\file\MemoryFile;
use rtens\domin\parameters\file\SavedFile;
use rtens\domin\Parameter;
use rtens\domin\delivery\web\fields\FileField;
use rtens\scrut\tests\statics\StaticTestSuite;
use watoki\curir\protocol\UploadedFile;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\IntegerType;
use watoki\reflect\type\StringType;
use watoki\reflect\type\UnknownType;

class FileFieldSpec extends StaticTestSuite {

    function handlesFiles() {
        $field = new FileField();
        $this->assert->not($field->handles(new Parameter('foo', new ClassType(\StdClass::class))));
        $this->assert($field->handles(new Parameter('foo', new ClassType(File::class))));
    }

    function inflateNothing() {
        $field = new FileField();
        $param = new Parameter('foo', new StringType());
        $this->assert($field->inflate($param, ['file' => '']), null);
    }

    function inflateUploadedFile() {
        $field = new FileField();
        $param = new Parameter('foo', new StringType());
        $this->assert($field->inflate($param, ['file' => new UploadedFile('foo', 'foo/type', 'tmp/name', 0, 100)]),
            new SavedFile('tmp/name', 'foo', 'foo/type'));
    }

    function inflatePreservedFile() {
        $field = new FileField();
        $param = new Parameter('foo', new StringType());
        $this->assert($field->inflate($param, [
            'file' => new UploadedFile('', '', '', 1, 0),
            'name' => 'foo.file',
            'type' => 'foo/type',
            'data' => 'Zm9v'
        ]), new MemoryFile('foo.file', 'foo/type', 'foo'));
    }

    function optionalField() {
        $field = new FileField();
        $this->assert->contains($field->render(new Parameter('foo', new UnknownType('file')), null),
            '<input class="sr-only file-input" type="file" name="foo[file]"/>');
    }

    function requiredField() {
        $field = new FileField();
        $this->assert->contains($field->render(new Parameter('foo', new IntegerType(), true), null),
            '<input class="sr-only file-input" type="file" name="foo[file]" required="required"/>');
    }

    function withValue() {
        $field = new FileField();
        $param = new Parameter('foo', new UnknownType('file'), true);
        $file = new MemoryFile('foo.file', 'foo/type', 'foo');
        $rendered = $field->render($param, $file);

        $this->assert->contains($rendered,
            '<input type="hidden" name="foo[name]" value="foo.file"/>' . "\n" .
            '<input type="hidden" name="foo[type]" value="foo/type"/>' . "\n" .
            '<input type="hidden" name="foo[data]" value="Zm9v"/>' . "\n" .
            '<a download="foo.file" href="data:foo/type;base64,Zm9v" target="_blank">foo.file</a>');
        $this->assert->contains($rendered,
            '<input class="sr-only file-input" type="file" name="foo[file]"/>');
    }
}