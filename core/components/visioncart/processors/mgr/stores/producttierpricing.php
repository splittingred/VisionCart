<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$productId = (int) $_REQUEST['productId'];

$product = $modx->getObject('vcProduct', $productId);
$currentTierConfig = $product->get('tierprice');

if (empty($currentTierConfig) || !is_array($currentTierConfig)) {
	$currentTierConfig = array();
}

$currentTierConfig = $modx->visioncart->arrayMultiSort($currentTierConfig, 'quantity');

return $this->outputArray($currentTierConfig, sizeof($currentTierConfig));