<?php

if (!isset($modx->visioncart) || $modx->visioncart == null) {
	$modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
    $modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/');	
}

$vc =& $modx->visioncart;
$order = $vc->getBasket();

// Check for authentication and a full basket ;-)
if (!$modx->user->isAuthenticated()) {
	$modx->sendUnauthorizedPage();	
	exit();
} elseif ($order->get('basket') == '' || !is_array($order->get('basket')) || sizeof($order->get('basket')) == 0 || $order->get('status') > 0) {
	$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=1'));
	exit();
}

// Get theme configuration
$config = $vc->getConfigFile($order->get('shopid'), 'orderStep2');

$chunkArray = array(
	'vcOrderStep2' => ''
); 

foreach($chunkArray as $key => $value) {
	if (isset($config[$key])) {
		$chunkArray[$key] = $config[$key];	
	} else {
		$chunkArray[$key] = $key;	
	}
}

$order->set('userid', $modx->user->get('id'));
$order->save();

$nextStep = $modx->makeUrl($modx->resource->get('id'), '', 'step=3');
$previousStep = $modx->makeUrl($modx->resource->get('id'), '', 'step=1');

return $vc->parseChunk($chunkArray['vcOrderStep2'], array(
	'action' => $modx->makeUrl($modx->resource->get('id'), '', 'step=2'),
	'nextStep' => $nextStep,
	'previousStep' => $previousStep
), array('isChunk' => true)); 