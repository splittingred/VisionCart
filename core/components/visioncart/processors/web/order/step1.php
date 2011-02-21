<?php

if (!isset($modx->visioncart) || $modx->visioncart == null) {
	$modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
    $modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/');	
}

$vc =& $modx->visioncart;
$order = $vc->getBasket();
$vc->calculateOrderPrice($order);
$order->set('ordertime', time());
$order->save();

$content = '';
$basket = $order->get('basket');

// Get theme configuration
$config = $vc->getConfigFile($order->get('shopid'), 'orderStep1');

$chunkArray = array(
	'vcOrderBasketEmpty' => '', 
	'vcBasketWrapper' => '', 
	'vcOrderStep1' => '',
	'vcBasketRow' => ''
); 

foreach($chunkArray as $key => $value) {
	if (isset($config[$key])) {
		$chunkArray[$key] = $config[$key];	
	} else {
		$chunkArray[$key] = $key;	
	}
}

if (($order->get('basket') == '' || !is_array($order->get('basket')) || sizeof($order->get('basket')) == 0 || $order->get('status') > 0)) {
	$content = $vc->parseChunk($chunkArray['vcOrderBasketEmpty'], array(
		'nextStep' => $nextStep
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
		'nextStep' => $nextStep
	), array(
		'isChunk' => true
	));
}

return $vc->parseChunk($chunkArray['vcOrderStep1'], array(
	'content' => $content,
	'action' => $modx->makeUrl($modx->resource->get('id'), '', 'step=2')
), array(
	'isChunk' => true
));