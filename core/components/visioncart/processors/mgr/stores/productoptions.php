<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$query = $modx->newQuery('vcProductOption');
$query->where(array('productid' => (int) $_REQUEST['prodId']));
$query->sortby('sort', 'ASC');

$productOptions = $modx->getCollection('vcProductOption', $query);
        
$list = array();
foreach ($productOptions as $productOption) {
	$option = $productOption->getOne('Option');
	$optionValue = $productOption->getOne('OptionValue');
	
	$optionArray = $productOption->toArray();
	
	if ($option != null) {
		$optionArray['option'] = $option->get('name');
	}
	
	if ($optionValue != null) {
		$optionArray['value'] = $optionValue->get('value');
	}
	
    $list[] = $optionArray;
}

return $this->outputArray($list, sizeof($list));