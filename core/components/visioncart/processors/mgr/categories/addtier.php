<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$tierConfig = json_decode($_REQUEST['tier'], true);

// Get the category
$category = $modx->getObject('vcCategory', (int) $_REQUEST['categoryId']);

$currentTierConfig = $category->get('tierprice');

if (empty($currentTierConfig) || !is_array($currentTierConfig)) {
	$currentTierConfig = array();
}

$duplicateTier = false;
foreach($currentTierConfig as $key => $tier) {
	if ($tier['quantity'] == $tierConfig['quantity']) {
		$duplicateTier = true;
		$currentTierConfig[$key] = $tierConfig;	
	}
}

if (!$duplicateTier) {
	$currentTierConfig[] = $tierConfig;
}

$category->set('tierprice', $currentTierConfig);

$category->save();

return $modx->error->success('');