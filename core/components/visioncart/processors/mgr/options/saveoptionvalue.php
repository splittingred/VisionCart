<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$valueArray = (array) json_decode($_REQUEST['formData']);

if (isset($_REQUEST['valueId']) && !empty($_REQUEST['valueId']) && $_REQUEST['valueId'] != 0) {
	$optionValue = $modx->getObject('vcOptionValue', (int) $_REQUEST['valueId']);
	
	$optionValue->fromArray(
		$valueArray
	);
} else {
	$optionValue = $modx->newObject('vcOptionValue', array(
		'shopid' => $_REQUEST['shopId'],
		'optionid' => $_REQUEST['optionId'],
		'value' => $valueArray['value'],
		'weight' => $valueArray['weight'],
		'price' => $valueArray['price'],
		'shippingprice' => $valueArray['shippingprice']
	));
}

// Return values
if ($optionValue->save()) {
	return $modx->error->success('', $optionValue);
} else {
	return $modx->error->failure('');
}