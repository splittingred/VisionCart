<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$productId = (int) $_REQUEST['productId'];
$quantity = (int) $_REQUEST['quantity'];

$product = $modx->getObject('vcProduct', $productId);
$currentTierConfig = $product->get('tierprice');

if (empty($currentTierConfig) || !is_array($currentTierConfig)) {
	$currentTierConfig = array();
}

foreach($currentTierConfig as $key => $tier) {
	if ($tier['quantity'] == $quantity) {
		unset($currentTierConfig[$key]);	
		break;
	}	
}

$product->set('tierprice', $currentTierConfig);
$product->save();

return $modx->error->success('');