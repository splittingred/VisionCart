<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$categoryId = (int) $_REQUEST['categoryId'];
$quantity = (int) $_REQUEST['quantity'];

$category = $modx->getObject('vcCategory', $categoryId);
$currentTierConfig = $category->get('tierprice');

if (empty($currentTierConfig) || !is_array($currentTierConfig)) {
	$currentTierConfig = array();
}

foreach($currentTierConfig as $key => $tier) {
	if ($tier['quantity'] == $quantity) {
		unset($currentTierConfig[$key]);	
		break;
	}	
}

$category->set('tierprice', $currentTierConfig);
$category->save();

return $modx->error->success('');