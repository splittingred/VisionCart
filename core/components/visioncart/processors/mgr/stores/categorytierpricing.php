<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$categoryId = explode('|', $_REQUEST['categoryId']);
$categoryId = explode(':', $categoryId[0]);
$categoryId = (int) $categoryId[1];	

$category = $modx->getObject('vcCategory', $categoryId);
$currentTierConfig = $category->get('tierprice');

if (empty($currentTierConfig) || !is_array($currentTierConfig)) {
	$currentTierConfig = array();
}

$currentTierConfig = $modx->visioncart->arrayMultiSort($currentTierConfig, 'quantity');

return $this->outputArray($currentTierConfig, sizeof($currentTierConfig));