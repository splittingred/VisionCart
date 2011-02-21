<?php

/*$output = $modx->executeProcessor(array(
   'action' => 'mgr/orders/vieworder',
   'processors_path' => $visionCart->config['processorsPath']
));*/

//get order id
$id = $_REQUEST['id'];

//get object from database
$order = $modx->getObject('vcOrder', $id);
$orderArray = $order->toArray();

$modx->regClientStartupHTMLBlock('<script type="text/javascript">var vcOrder = '.json_encode($orderArray).';</script>');

$vc =& $modx->visioncart;
$vc->shop = $modx->getObject('vcShop', $order->get('shopid'));
$shop =& $vc->shop;

$scriptProperties = $vc->getConfigFile($order->get('shopid'), 'orderView', null, array('context' => 'mgr'));

$output = ''; 
$cache = '';

foreach($order->get('basket') as $product) {
	$productObject = $modx->getObject('vcProduct', $product['id']);
	$taxCategory = $productObject->getOne('TaxCategory');
	
	// Calculate product price
	$product['display']['price'] = $vc->calculateProductPrice($product, true);
	$product['display']['price']['subtotal'] = ((($product['price'] / 100) * $taxCategory->get('pricechange')) + $product['price']) * $product['quantity'];
	
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
			'tax' => $tax
		)));
	}
}

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

$output .= '<div id="visioncart-container">'.$output.'</div><div id="vc-ajax-haze" class="vc-ajax-haze"></div>';

return $output;