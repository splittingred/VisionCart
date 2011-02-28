<?php

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

// Check for minimum order amount
if ($vc->getShopSetting('enableMinimumOrderAmount') == 1) {
	if ($order->get('totalorderamountin') <= $vc->getShopSetting('minimumOrderAmount')) {
		$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=1'));
		exit();
	} 
}

$vc->fireEvent('vcEventOrderStep2', '', array(
	'order' => $order
));

// Get theme configuration
$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($order->get('shopid'), 'orderStep2', null, array('config' => $scriptProperties['config']));
$config = array_merge($config, $scriptProperties);

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