<?php

// Get all plugins
$systemSetting = $modx->newObject('modSystemSetting');
$systemSetting->fromArray(array(
	'key' => 'visioncart_default_shop',
	'value' => '1',
	'xtype' => 'textfield',
	'namespace' => 'visioncart',
	'area' => 'system'
), '', true, true);

$vehicle = $builder->createVehicle($systemSetting, array ( 
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => false,
    xPDOTransport::UNIQUE_KEY => 'key' 
)); 

$builder->putVehicle($vehicle);
unset($vehicle); 