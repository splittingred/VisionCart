<?php

// Add vcInit plugin
$plugin = $modx->getObject('modPlugin', array(
	'name' => 'vcInit'
));

$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'name',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
    	'PluginEvents' => array(
			xPDOTransport::PRESERVE_KEYS => true,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => array('pluginid', 'event')
    	) 
    )
);

// Get and add the events
$pluginEvents = $plugin->getMany('PluginEvents');
 
$plugin->addMany($pluginEvents);
$plugin->set('category', 0);

$vehicle = $builder->createVehicle($plugin, $attributes);
$builder->putVehicle($vehicle);
unset($vehicle);