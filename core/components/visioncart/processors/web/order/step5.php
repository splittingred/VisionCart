<?php

$vc =& $modx->visioncart;
$options = array_merge($options, $scriptProperties);
$order = $vc->getBasket();
$action = $modx->makeUrl($modx->resource->get('id'), '', 'step=5');
$previousStep = $modx->makeUrl($modx->resource->get('id'), '', 'step=4');
$chunkParameters = array();
$shop = $vc->shop;

// Check for authentication and a full basket ;-)
if (!$modx->user->isAuthenticated()) {
	$modx->sendUnauthorizedPage();	
	exit();
} elseif ($order->get('basket') == '' || !is_array($order->get('basket')) || sizeof($order->get('basket')) == 0 || $order->get('status') > 0) {
	$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=1'));
	exit();
}

// Check for minimum order amount
if ($vc->getShopSetting('enableMinimumOrderAmount') == 1) {
	if ($order->get('totalorderamountin') <= $vc->getShopSetting('minimumOrderAmount')) {
		$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=1'));
		exit();
	} 
}

// Update order step
if (!isset($_SESSION['vc-order-step']) || $_SESSION['vc-order-step'] < 5) {
	$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=4'));
	exit();
}

$vc->fireEvent('vcEventOrderStep5', '', array(
	'order' => $order
));

// Get theme configuration
$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($order->get('shopid'), 'orderStep5', null, array('config' => $scriptProperties['config']));
$config = array_merge($config, $scriptProperties);

$chunkArray = array(
	'vcOrderFinalBasketRow' => '', 
	'vcOrderFinalBasketWrapper' => '', 
	'vcOrderFinalTaxRow' => '',
	'vcOrderStep5' => ''
); 

foreach($chunkArray as $key => $value) {
	if (isset($config[$key])) {
		$chunkArray[$key] = $config[$key];	
	} else {
		$chunkArray[$key] = $key;	
	}
}

// Save user data with order
$order->set('userdata', array(
	'user' => $modx->user->toArray(),
	'profile' => $modx->user->getOne('Profile')->toArray()
));
$order->save();

$profile = $modx->user->getOne('Profile')->toArray();
$chunkParameters['order'] = $order->toArray();
$chunkParameters['shippingAddress'] = $profile['extended']['VisionCart']['shippingaddress'];
if ($extendedFields['profile']['extended']['VisionCart']['billing_as_shipping'] == 1) {
	$chunkParameters['billingAddress'] = $chunkParameters['shippingAddress'];	
} else {
	$chunkParameters['billingAddress'] = $profile['extended']['VisionCart']['billingaddress'];
}

// Get highest tax
$highestTax = $vc->getOrderHighestTax($order);

// Fetch the used shipping module
$shippingModule = $order->getOne('ShippingModule');
if ($shippingModule != null) {
	$returnValue = array();	
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
	$chunkParameters['shippingModule'] = array_merge($shippingModule, $returnValue);
}

// Fetch the used payment module
$paymentModule = $order->getOne('PaymentModule');
if ($paymentModule != null) {
	$returnValue = array();	
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
	$chunkParameters['paymentModule'] = array_merge($paymentModule, $returnValue);
}

// Loop through the order basket
$temporaryContent = '';
$basket = $order->get('basket');
if (is_array($basket)) {
	foreach($basket as $product) {
		$productObject = $modx->getObject('vcProduct', $product['id']);
		$taxCategory = $productObject->getOne('TaxCategory');
		$productPrice = $vc->calculateProductPrice($product, true);
		
		$price = $productPrice;
		$subtotal = array(
			'in' => $productPrice['in'] * $product['quantity'],
			'ex' => $productPrice['ex'] * $product['quantity']
		);
		
		$temporaryContent .= $vc->parseChunk($chunkArray['vcOrderFinalBasketRow'], array_merge($chunkParameters, array(
			'tax' => $taxCategory->toArray(),
			'product' => $product,
			'display' => array(
				'price' => $price,
				'subtotal' => $subtotal
			)
		)), array('isChunk' => true));
	}
}

// Loop through taxes
$taxes = $vc->calculateTaxes($order);
$taxContent = '';
if (is_array($taxes)) {
	foreach($taxes as $tax) {
		$taxContent .= $vc->parseChunk($chunkArray['vcOrderFinalTaxRow'], array_merge($chunkParameters, array(
			'tax' => $tax,
		)), array('isChunk' => true));
	}
}

$content = $vc->parseChunk($chunkArray['vcOrderFinalBasketWrapper'], array_merge($chunkParameters, array(
	'action' => $action,
	'previousStep' => $previousStep,
	'content' => $temporaryContent,
	'taxes' => $taxContent
)), array('isChunk' => true)); 

if (isset($_REQUEST['vc_order_confirm'])) {
	$vc->fireEvent('vcEventConfirmOrder', 'before', array(
		'order' => $order
	));
	
	if ($order->get('ordernumber') == '') {
		$orderNumber = $vc->generateOrderNumber();
		$order->set('ordernumber', $orderNumber);
		$order->save();
	} else {
		$orderNumber = $order->get('ordernumber');
	}
	
	// Last order step
	$_SESSION['vc-order-step'] = 6;
	
	// Update status to new and update the order time
	$order->set('ordertime', time());
	$order->set('status', 1);
	$order->save();
	
	// Send an order email
	$vc->sendStatusEmail($order, true);
	
	$vc->fireEvent('vcEventConfirmOrder', 'after', array(
		'order' => $order
	));

	// Do payment
	$paymentModule = $order->getOne('PaymentModule');
	if ($paymentModule != null) {
		if ($paymentModule->get('controller') != '') {
			$controller = $vc->config['corePath'].'modules/payment/'.$paymentModule->get('controller');
			if (is_file($controller)) {	
				$vcAction = 'doPayment';
				$parameters['returnUrl'] = $modx->makeUrl($modx->resource->get('id'), '', 'step=6&order='.$orderNumber, 'full');
				
				$returnValue = include($controller);	
				
				if (!is_array($returnValue)) {
					$returnValue = array();	
				}
			}
		}
	}
	exit();
}

return $vc->parseChunk($chunkArray['vcOrderStep5'], array_merge($chunkParameters, array(
	'action' => $action,
	'previousStep' => $previousStep,
	'basket' => $content
)), array('isChunk' => true)); 