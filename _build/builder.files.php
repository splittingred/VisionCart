<?php

$vehicle->resolve('file', array(
    'source' => $sources['source_core'],
    'target' => 'return MODX_CORE_PATH.\'components/\';'
));

$vehicle->resolve('file', array(
    'source' => $sources['source_assets'],
    'target' => 'return MODX_ASSETS_PATH.\'components/\';'
));

$vehicle->resolve('php',array(
    'source' => $sources['resolvers'].'postactions.dbchanges.php',
));

$vehicle->resolve('php',array(
    'source' => $sources['resolvers'].'postactions.resolver.php',
));

$builder->putVehicle($vehicle);
