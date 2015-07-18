<?php
namespace rtens\domin\cli;

use rtens\domin\ActionRegistry;
use rtens\domin\delivery\ParameterReader;
use rtens\domin\Executor;
use watoki\factory\Factory;

class CliApplication {

    public static function run(Factory $factory, callable $read, callable $write) {
        $actions = $factory->getInstance(ActionRegistry::class);

        $i = 1;
        $actionIds = [];
        foreach ($actions->getAllActions() as $id => $action) {
            $write($i++ . ' - ' . $action->caption() . PHP_EOL);
            $actionIds[] = $id;
        }

        $actionIndex = $read('Action:');

        $actionId = $actionIds[$actionIndex - 1];
        $action = $actions->getAction($actionId);
        $write(PHP_EOL . $action->caption() . PHP_EOL);

        $reader = $factory->getInstance(CliParameterReader::class, ['read' => $read]);
        $factory->setSingleton($reader, ParameterReader::class);

        $executor = $factory->getInstance(Executor::class);
        $write($executor->execute($actionId));
    }
}