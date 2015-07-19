# Domin [![Build Status](https://travis-ci.org/rtens/domin.png?branch=master)](https://travis-ci.org/rtens/domin)

*domin* ( **do**main **m**odel **in**terface ) is an administration interface for abstract [Domain Models] using 
the [Command Object pattern].

For an example of how to use use, check out the [sample application].

[Domain Models]: https://en.wikipedia.org/wiki/Domain-driven_design#Concepts
[Command Object pattern]: http://c2.com/cgi/wiki?CommandObject
[sample application]: https://github.com/rtens/domin-sample

## Model ##

Every *ability* of a system is represented by an [`Action`] which specifies how to execute it and what [`Parameters`] 
it requires. Therefore *domin* can take care of getting missing parameters from the user using [`Fields`]. Actions may 
return values which are presented using [`Renderers`].

[`Action`]: https://github.com/rtens/domin/blob/master/src/Action.php
[`Parameters`]: https://github.com/rtens/domin/blob/master/src/Parameter.php
[`Fields`]: https://github.com/rtens/domin/blob/master/src/delivery/Field.php
[`Renderers`]: https://github.com/rtens/domin/blob/master/src/delivery/Renderer.php



## Action! ##

[`Actions`] decide what [`Parameters`] they need, how to `fill()` them with default values and how to `execute()` them. 
So in order to use *domin*, you need to put all action into the [`ActionRegistry`]. This can be done in several ways:

[`Actions`]: https://github.com/rtens/domin/blob/master/src/Action.php
[`Parameters`]: https://github.com/rtens/domin/blob/master/src/Parameter.php
[`ActionRegistry`]: https://github.com/rtens/domin/blob/master/src/ActionRegistry.php

#### Implementing `Action` ####

The most straight-forward although probably not most convenient way is to create an implementation of `Action` for 
every ability of the system.

```php
class MyAction implements Action {

    public function caption() {
        return 'Some Action';
    }

    public function parameters() {
        return [
            new Parameter('foo', new StringType()),
            new Parameter('bar', new ClassType(\DateTime::class))
        ];
    }

    public function fill(array $parameters) {
        $parameters['foo'] = 'default value of foo';
        return $parameters;
    }

    public function execute(array $parameters) {
        var_dump("Make it so!", $parameters);
    }
}

$actionRegistry->add('my', new MyAction());
```

#### Extending `ObjectAction` ####

If you represent abilities with [DTOs], you can extend you actions from the `ObjectAction` to infer `Parameters` from 
the properties of these classes using reflection. This sub-class can then be made generic for example by using 
a [Command Bus].

```php
class MyAction extends ObjectAction {
    
    public function __construct($class, TypeFactory $types, CommandBus $bus) {
        parent::__construct($class, $types);
        $this->bus = $bus;
    }

    protected function executeWith($object) {
        $this->bus->handle($object);
    }
}

$actionRegistry->add('my', new MyAction(MyCommand::class, $types, $bus));
$actionRegistry->add('your', new MyAction(YourCommand::class, $types, $bus));
$actionRegistry->add('their', new MyAction(TheirCommand::class, $types, $bus));
```

[DTOs]: https://en.wikipedia.org/wiki/Data_transfer_object
[Command Bus]: http://tactician.thephpleague.com/


#### Generating `ObjectActions` ####

With a generic way to execute actions, you can use the `ObjectActionGenerator` to generate and register actions from 
all classes in a folder automatically.

```php
(new ObjectActionGenerator($actionRegistry))->fromFolder('model/commands', function ($object) {
    $bus->handle($object);
});
```

#### Using `MethodAction` ####

If you don't feel like creating a class for every command, the `MethodAction` can infer parameters from a method signature.

```php
$actionRegistry->add('my', new MethodAction($handler, 'handleMyCommand'));
$actionRegistry->add('your', new MethodAction($handler, 'handleYourCommand'));
$actionRegistry->add('their', new MethodAction($handler, 'handleTheirCommand'));
```

#### Generating `MethodActions` ####

There is also a `MethodActionGenerator` to register all methods of an object.

```php
(new MethodActionGenerator($actionRegistry))->fromObject($handler);
```


## Installation ##

To use *domin* in your project, require it with [Composer]

    composer require rtens/domin
    
If you would like to develop on *domin*, download it with [Composer] and execute the specification with [scrut]

    composer create-project rtens/domin
    cd domin
    vendor/bin/scrut

[Composer]: http://getcomposer.org/download/
[scrut]: https://github.com/rtens/scrut
[git]: https://git-scm.com/
