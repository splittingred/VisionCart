<?php
/**
 * @package visioncart
 */
 
$vc =& $modx->visioncart;
$shop = $vc->shop;
$output = '';
$cache = '';
$order = $vc->getOrder();

if ($order == null) {
	$modx->sendUnauthorizedPage();	
	exit();
}

$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$scriptProperties = array_merge($vc->getConfigFile($order->get('shopid'), 'orderView', null, array('config' => $scriptProperties['config'])), $scriptProperties);

foreach($order->get('basket') as $product) {
	$productObject = $modx->getObject('vcProduct', $product['id']);
	$taxCategory = $productObject->getOne('TaxCategory');
	$productPrice = $vc->calculateProductPrice($product, true);
	
	// Calculate product price
	$product['display']['price'] = $productPrice;
	$product['display']['price']['subtotal'] = ($productPrice['in'] * $product['quantity']);
	
	$cache .= $vc->parseChunk($scriptProperties['productRow'], array_merge($order->toArray(), array(
		'product' => $product
	)));
}

// Loop through taxes
$taxes = $vc->calculateTaxes($order);
$taxContent = '';

if (is_array($taxes)) {
	foreach($taxes as $tax) {
		$taxContent .= $vc->parseChunk($scriptProperties['taxRow'], array_merge(array(
			'tax' => $tax,
		)));
	}
}

// Get highest tax
$highestTax = $vc->getOrderHighestTax($order);

// Get the shipping module
$shippingModule = $order->getOne('ShippingModule');
if ($shippingModule == null) {
	$shippingModule = array();	
} else {
	// Include the controller to give it a chance to do some stuff as well
	if ($shippingModule->get('controller') != '') {
		$controller = $vc->config['corePath'].'modules/shipping/'.$shippingModule->get('controller');
		if (is_file($controller)) {	
			$vcAction = 'getParams';
			
			$returnValue = include($controller);	
			
			if (!is_array($returnValue)) {
				$returnValue = array();	
			}
			
			if (isset($module)) {
				unset($module);	
			}
		}
	}
	$shippingModule = $shippingModule->toArray();
	$shippingModule = array_merge($shippingModule, $returnValue);
}

// Get the payment module
$paymentModule = $order->getOne('PaymentModule');
if ($paymentModule == null) {
	$paymentModule = array();	
} else {
	// Include the controller to give it a chance to do some stuff as well
	if ($paymentModule->get('controller') != '') {
		$controller = $vc->config['corePath'].'modules/payment/'.$paymentModule->get('controller');
		if (is_file($controller)) {	
			$vcAction = 'getParams';
			
			$returnValue = include($controller);	
			
			if (!is_array($returnValue)) {
				$returnValue = array();	
			}
			
			if (isset($module)) {
				unset($module);	
			}
		}
	}
	$paymentModule = $paymentModule->toArray();
	$paymentModule = array_merge($paymentModule, $returnValue);
} 

$user = $order->get('userdata');
$profile = $user['profile'];

$rawOrder = $order->toArray('', true);
$order = $order->toArray();
$order['ordertime'] = $rawOrder['ordertime'];

$output = $vc->parseChunk($scriptProperties['wrapperTpl'], array_merge($order, array(
	'wrapper.products' => $cache,
	'shippingModule' => $shippingModule,
	'paymentModule' => $paymentModule,
	'taxes' => $taxContent
)));

return $output;