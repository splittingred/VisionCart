<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$shopId = (int) $_REQUEST['id'];

$shop = $modx->getObject('vcShop', $shopId);

if ($shop == null) {
	return $modx->error->failure('Shop not found');
}

// Remove main menu entry
$menu = $modx->getObject('modMenu', array(
	'text' => 'Shop: '.$shop->get('name')
));
if ($menu != null) 	{
	$menu->remove();
}

// Remove submenu's
$menus = $modx->getCollection('modMenu', array(
	'parent' => 'Shop: '.$shop->get('name')
));

foreach($menus as $menu) {
	$menu->remove();	
}

// Remove shop
$shop->remove();

return $modx->error->success('');