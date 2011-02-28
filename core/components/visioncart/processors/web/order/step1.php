<?php

$vc =& $modx->visioncart;
$order = $vc->getBasket();
$vc->calculateOrderPrice($order);
$order->set('ordertime', time());
$order->save();

$content = '';
$basket = $order->get('basket');

// Init order step variable
if (!isset($_SESSION['vc-order-step'])) {
	$_SESSION['vc-order-step'] = 1;
} elseif ($_SESSION['vc-order-step'] < 1) {
	$_SESSION['vc-order-step'] = 1;
}

$vc->fireEvent('vcEventOrderStep1', '', array(
	'order' => $order
));

// Get theme configuration
$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($order->get('shopid'), 'orderStep1', null, array('config' => $scriptProperties['config']));
$config = array_merge($config, $scriptProperties);

$chunkArray = array(
	'vcOrderBasketEmpty' => '', 
	'vcBasketWrapper' => '', 
	'vcOrderStep1' => '',
	'vcBasketRow' => ''
); 

// Check for minimum order amount
$orderAmountMet = 1;
if ($vc->getShopSetting('enableMinimumOrderAmount') == 1) {
	if ($order->get('totalorderamountin') <= $vc->getShopSetting('minimumOrderAmount')) {
		$orderAmountMet = 0;	
	} 
}

foreach($chunkArray as $key => $value) {
	if (isset($config[$key])) {
		$chunkArray[$key] = $config[$key];	
	} else {
		$chunkArray[$key] = $key;	
	}
}

if (($order->get('basket') == '' || !is_array($order->get('basket')) || sizeof($order->get('basket')) == 0 || $order->get('status') > 0)) {
	$content = $vc->parseChunk($chunkArray['vcOrderBasketEmpty'], array(
		'nextStep' => $nextStep,
		'orderAmountMet' => $orderAmountMet
	), array(
		'isChunk' => true
	));
} else {
	foreach($basket as $product) {
		$product['display']['price'] = $vc->calculateProductPrice($product, true);
		$product['display']['price']['subtotal'] = ($product['display']['price']['in'] * (int) $product['quantity']);
		$content .= $vc->parseChunk($chunkArray['vcBasketRow'], $product, array(
			'isChunk' => true
		));
	}
	
	$content = $vc->parseChunk($chunkArray['vcBasketWrapper'], array(
		'content' => $content,
		'nextStep' => $nextStep,
		'orderAmountMet' => $orderAmountMet
	), array(
		'isChunk' => true
	));
}

return $vc->parseChunk($chunkArray['vcOrderStep1'], array(
	'content' => $content,
	'action' => $modx->makeUrl($modx->resource->get('id'), '', 'step=2'),
	'orderAmountMet' => $orderAmountMet
), array(
	'isChunk' => true
));