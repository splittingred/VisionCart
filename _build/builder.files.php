<?php

$vehicle->resolve('file', array(
	'source' => $sources['source_core'],
	'target' => 'return MODX_CORE_PATH.\'components/\';'
));

$builder->putVehicle($vehicle);