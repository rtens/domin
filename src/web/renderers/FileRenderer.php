<?php
namespace rtens\domin\web\renderers;

use rtens\domin\delivery\Renderer;
use rtens\domin\parameters\File;
use rtens\domin\web\Element;

class FileRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof File;
    }

    /**
     * @param File $value
     * @return mixed
     */
    public function render($value) {
        if ($this->isImage($value)) {
            return (string)new Element('img', [
                'title' => $value->getName(),
                'src' => $this->createUrl($value),
                'style' => 'max-height:' . $this->maxHeight(),
            ]);
        }

        return (string)new Element('a', [
            'download' => $value->getName(),
            'href' => $this->createUrl($value),
            'target' => '_blank'
        ], [
            $value->getName()
        ]);
    }

    protected function createUrl(File $file) {
        return 'data:' . $file->getType() . ';base64,' . base64_encode($file->getContent());
    }

    protected function isImage(File $file) {
        return strpos($file->getType(), 'image') === 0;
    }

    private function maxHeight() {
        return '10em';
    }
}