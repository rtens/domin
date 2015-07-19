# Domin [![Build Status](https://travis-ci.org/rtens/domin.png?branch=master)](https://travis-ci.org/rtens/domin)

*domin* ( **do**main **m**odel **in**terface ) is an administration interface for abstract [Domain Models] using 
the [Command Object pattern].

For an example of how to use use, check out the [sample application].

[Domain Models]: https://en.wikipedia.org/wiki/Domain-driven_design#Concepts
[Command Object pattern]: http://c2.com/cgi/wiki?CommandObject
[sample application]: https://github.com/rtens/domin-sample

## Model ##

Every *ability* of a system is represented by an [`Action`] which specifies how to execute it and what [`Parameters`] it requires. Therefore *domin* can take care of getting missing parameters from the user using [`Fields`]. Actions may return values which are presented using [`Renderers`].

[`Action`]: https://github.com/rtens/domin/blob/master/src/Action.php
[`Parameters`]: https://github.com/rtens/domin/blob/master/src/Parameter.php
[`Fields`]: https://github.com/rtens/domin/blob/master/src/delivery/Field.php
[`Renderers`]: https://github.com/rtens/domin/blob/master/src/delivery/Renderer.php

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
