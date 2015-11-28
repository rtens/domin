<?php
namespace rtens\domin\reflection;

use rtens\domin\reflection\types\TypeFactory;

class GenericMethodAction extends MethodAction {

    /** @var GenericAction */
    private $generic;

    /**
     * @param object $object
     * @param string $method
     * @param TypeFactory $types
     * @param CommentParser $parser
     */
    public function __construct($object, $method, TypeFactory $types, CommentParser $parser) {
        parent::__construct($object, $method, $types, $parser);
        $this->generic = new GenericAction($this);
        $this->generic
            ->setCaption(parent::caption())
            ->setDescription(parent::description())
            ->setParameters(parent::parameters())
            ->setExecute(function ($parameters) {
                return parent::execute($parameters);
            })
            ->setFill(function ($parameters) {
                return $parameters;
            });
    }

    public function generic() {
        return $this->generic;
    }

    public function execute(array $parameters) {
        return $this->generic->execute($parameters);
    }

    public function caption() {
        return $this->generic->caption();
    }

    public function description() {
        return $this->generic->description();
    }

    public function fill(array $parameters) {
        return $this->generic->fill(parent::fill($parameters));
    }

    public function parameters() {
        return $this->generic->parameters();
    }
}