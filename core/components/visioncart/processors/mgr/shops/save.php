<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$paymentObject = json_decode($scriptProperties['paymentObject']);
$shippingObject = json_decode($scriptProperties['shippingObject']);
$shopConfig = json_decode($scriptProperties['shopConfig'], true);

// Define config variables
$configArray = array(
	array('key' => 'defaultBtw', 'defaultValue' => 'in'),
	array('key' => 'enableOrdering', 'defaultValue' => true),
	array('key' => 'enableMinimumOrderAmount', 'defaultValue' => false),
	array('key' => 'minimumOrderAmount', 'defaultValue' => 0),
	array('key' => 'freeShippingBoundary', 'defaultValue' => 0),
	array('key' => 'currency', 'defaultValue' => '&euro;'),
	array('key' => 'stockDecrease', 'defaultValue' => 'paid'),
	array('key' => 'thumbnails', 'defaultValue' => 'w=75,h=75,prefix=thumb_
w=250,h=250,prefix=medium_
w=600,h=600,prefix=big_'),
	array('key' => 'categoryThumbnails', 'defaultValue' => 'w=75,h=75,prefix=thumb_
w=250,h=250,prefix=big_'),
	array('key' => 'emailFromName', 'defaultValue' => ''),
	array('key' => 'emailFromAddress', 'defaultValue' => ''),
	array('key' => 'emailOuterChunk', 'defaultValue' => ''),
	array('key' => 'emailInnerChunkOrder', 'defaultValue' => ''),
	array('key' => 'emailInnerChunkStatusUpdate', 'defaultValue' => ''),
	array('key' => 'emailSubjectOrder', 'defaultValue' => ''),
	array('key' => 'emailSubjectStatusUpdate', 'defaultValue' => ''),
	array('key' => 'hideSkus', 'defaultValue' => true),
	array('key' => 'orderNumberLength', 'defaultValue' => 5),
	array('key' => 'orderNumberFormat', 'defaultValue' => 'ORD[[+year]][[+orderNumber]]'),
	array('key' => 'currentOrderNumber', 'defaultValue' => 0),
	array('key' => 'categoryResource', 'defaultValue' => 0),
	array('key' => 'productResource', 'defaultValue' => 0),
	array('key' => 'orderProcessResource', 'defaultValue' => 0),
	array('key' => 'orderHistoryResource', 'defaultValue' => 0),
	array('key' => 'shopTheme', 'defaultValue' => 'default'),
	array('key' => 'taxesCategory', 'defaultValue' => 0),
	array('key' => 'decimalSeparator', 'defaultValue' => ','),
	array('key' => 'thousandsSeparator', 'defaultValue' => ','),
	array('key' => 'calculateShippingTaxes', 'defaultValue' => 1),
	array('key' => 'calculatePaymentTaxes', 'defaultValue' => 0)
);

// Check if payment options have been edited (and convert to array)
$paymentUpdated = false;
if (is_array($paymentObject) && !empty($paymentObject)) {
	$paymentUpdated = true;
	foreach($paymentObject as $key => $value) {
		$paymentObject[$key] = (array) $value;	
	}
}

// Check if shipping options have been edited (and convert to array)
$shippingUpdated = false;
if (is_array($shippingObject) && !empty($shippingObject)) {
	$shippingUpdated = true;
	foreach($shippingObject as $key => $value) {
		$shippingObject[$key] = (array) $value;	
	}
}

// Assemble the shop config
foreach($configArray as $key => $value) {
	$config[$value['key']] = isset($shopConfig[$value['key']]) ? $shopConfig[$value['key']] : $value['defaultValue'];
}

// Check if payment tab has been loaded, else enable all payment modules for this shop
if ($paymentUpdated) {
	$config['paymentModules'] = $paymentObject;
} else {
	$paymentMethods = $modx->getCollection('vcModule', array('type' => 'payment'));
	foreach($paymentMethods as $paymentMethod) {
		$config['paymentModules'][] = array(
			'id' => $paymentMethod->get('id'),
			'active' => 1
		);
	}	
}

// Check if payment tab has been loaded, else enable all shipping modules for this shop
if ($shippingUpdated) {
	$config['shippingModules'] = $shippingObject;	
} else {
	$shippingMethods = $modx->getCollection('vcModule', array('type' => 'shipping'));
	foreach($shippingMethods as $shippingMethod) {
		$config['shippingModules'][] = array(
			'id' => $shippingMethod->get('id'),
			'active' => 1
		);
	}	
}

if ($shopConfig['emptyCache'] == true) {
	$modx->runProcessor('clearCache', array(), array(
		'location' => 'system'
	));
}

if (isset($shopConfig['id']) && !empty($shopConfig['id']) && $shopConfig['id'] != 0) {
	// Apparently we are updating instead of creating a new shop
	$shop = $modx->getObject('vcShop', $shopConfig['id']);
	
	// Set shop data
	$shop->set('name', $shopConfig['name']);
	$shop->set('alias', $shopConfig['alias']);
	$shop->set('description', $shopConfig['description']);
	$shop->set('context', $shopConfig['context']);
	$shop->set('active', $shopConfig['active']);
	
	// Get current config and decode
	$currentConfig = $shop->get('config');
	
	// If payment is updated change the config
	if ($paymentUpdated) {
		unset($currentConfig['paymentModules']);
		$currentConfig['paymentModules'] = $config['paymentModules'];
	}
	
	// If shipping is updated change the config
	if ($shippingUpdated) {
		unset($currentConfig['shippingModules']);
		$currentConfig['shippingModules'] = $config['shippingModules'];
	}
	
	// Set the general config values
	foreach($configArray as $key => $value) {
		$currentConfig[$value['key']] = isset($shopConfig[$value['key']]) ? $shopConfig[$value['key']] : $currentConfig[$value['key']];
	}
	
	// Overwrite the config in the shop object
	$shop->set('config', $currentConfig);
	
	// Return values
	if ($shop->save()) {
		return $modx->error->success('', $newShop);
	} else {
		return $modx->error->failure('');
	}
} else {
	// We are creating a new shop, not updating an existing one	
	$newShop = $modx->newObject('vcShop', array(
		'name' => $shopConfig['name'],
		'alias' => $shopConfig['alias'],
		'description' => $shopConfig['description'],
		'context' => $shopConfig['context'],
		'active' => $shopConfig['active']
	));

	// Set the config in the new shop object
	$newShop->set('config', $config);
	
	// Return values
	if ($newShop->save()) {
		
		// Get the root action
		$action = $modx->getObject('modAction', array(
			'namespace' => 'visioncart',
			'controller' => 'index'
		));
				
		// Get the root menu-item for the shops
		$menuShops = $modx->getObject('modMenu', array(
			'text' => 'menu.visioncart_shops',
			'action' => $action->get('id')
		));
		
		$menuItem = $modx->newObject('modMenu');
		$menuItem->fromArray(array(
			'text' => 'Shop: '.$newShop->get('name'),
			'description' => $newShop->get('description'),
			'parent' => 'menu.visioncart_shops'
		), '', true);
		$menuItem->save();
		
		// Sub buttons
		$subButtons = array(
			array(
				'text' => $modx->lexicon('menu.visioncart_products'),
				'description' => 'menu.visioncart_products_desc',
				'params' => '&action=products&shopid='.$newShop->get('id')
			),
			array(
				'text' => $modx->lexicon('menu.visioncart_categories'),
				'description' => 'menu.visioncart_categories_desc',
				'params' => '&action=categories&shopid='.$newShop->get('id')
			),
			array(
				'text' => $modx->lexicon('menu.visioncart_options'),
				'description' => 'menu.visioncart_options_desc',
				'params' => '&action=options&shopid='.$newShop->get('id')
			),
			array(
				'text' => $modx->lexicon('menu.visioncart_orders'),
				'description' => 'menu.visioncart_orders_desc',
				'params' => '&action=orders&shopid='.$newShop->get('id')
			)
		);
		
		// Create sub buttons
		foreach($subButtons as $subButton) {
			$menuItem = $modx->newObject('modMenu');
			$menuItem->fromArray(array(
				'text' => $newShop->get('name').': '.$subButton['text'],
				'parent' => 'Shop: '.$newShop->get('name'),
				'action' => $action->get('id'),
				'description' => $subButton['description'],
				'params' => $subButton['params']
			), '', true);
			$menuItem->save();
		}
		
		// Create the root tax category
		$category = $modx->newObject('vcCategory', array(
			'shopid' => $newShop->get('id'),
			'name' => 'Taxes',
			'alias' => 'taxes',
			'description' => '',
			'parent' => 0,
			'sort' => 1,
			'config' => '',
			'customfields' => '',
			'pricechange' => 0,
			'pricepercent' => 1,
			'active' => 1
		));
		$category->save();
		
		$config['taxesCategory'] = $category->get('id');
		$newShop->set('config', $config);
		$newShop->save();
		
		// Create the sub tax category
		$category = $modx->newObject('vcCategory', array(
			'shopid' => $newShop->get('id'),
			'name' => 'Default tax',
			'alias' => 'default-tax',
			'description' => '',
			'parent' => $category->get('id'),
			'sort' => 1,
			'config' => '',
			'customfields' => '',
			'pricechange' => 0,
			'pricepercent' => 1, 
			'active' => 1
		));
		$category->save();
				
		return $modx->error->success('', $newShop);
	} else {
		return $modx->error->failure('');
	}
}