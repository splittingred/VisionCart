<?php

$action = $modx->newObject('modAction');
$action->fromArray(array(
	'id' => 1,
	'namespace' => 'visioncart',
	'parent' => 0,
	'controller' => 'index',
	'haslayout' => 1,
	'lang_topics' => 'visioncart:default',
	'assets' => ''
), '', true, true);

// The main menu item
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
	'text' => 'menu.visioncart',
	'parent' => 'components', 
	'description' => 'menu.visioncart_desc',
	'icon' => '',
	'menuindex' => '0',
	'params' => '',
	'handler' => ''
), '', true, true);
$menu->addOne($action);
$vehicle = $builder->createVehicle($menu, array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false, 
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

// Create shops submenu
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
	'text' => 'menu.visioncart_shopmanagement',
	'parent' => 'menu.visioncart', 
	'description' => 'menu.visioncart_shopmanagement_desc',
	'icon' => '',
	'menuindex' => '0',
	'params' => '',
	'handler' => ''
), '', true, true);
$menu->addOne($action);

$vehicle = $builder->createVehicle($menu, array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

// Module management
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
	'text' => 'menu.visioncart_modules',
	'parent' => 'menu.visioncart', 
	'description' => 'menu.visioncart_modules_desc',
	'icon' => '',
	'menuindex' => '1',
	'params' => '&action=modules',
	'handler' => ''
), '', true, true);
$menu->addOne($action);

$vehicle = $builder->createVehicle($menu, array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

// The second main menu item
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
	'text' => 'menu.visioncart_shops',
	'parent' => 'components', 
	'description' => 'menu.visioncart_shops_desc',
	'icon' => '',
	'menuindex' => '0',
	'params' => '',
	'handler' => ''
), '', true, true);
$menu->addOne($action);

$vehicle = $builder->createVehicle($menu, array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => false,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => false,
            xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
        )
    )
));
$builder->putVehicle($vehicle);

unset($vehicle,$action); /* to keep memory low */