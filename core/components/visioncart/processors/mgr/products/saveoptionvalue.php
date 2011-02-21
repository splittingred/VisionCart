<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$valueArray = (array) json_decode($_REQUEST['formData']);
$shopId = (int) $_REQUEST['shopId'];
$prodId = (int) $_REQUEST['prodId'];

// If it's a new option also create one for each child SKU
$children = $modx->getCollection('vcProduct', array(
	'parent' => $prodId
));

foreach($children as $child) {
	$optionValue = $modx->newObject('vcProductOption', array(
		'productid' => $child->get('id'),
		'optionid' => $valueArray['option'],
		'valueid' => $valueArray['optionvalue']
	));
	$optionValue->save();
}

$optionValue = $modx->newObject('vcProductOption', array(
	'productid' => $prodId,
	'optionid' => $valueArray['option'],
	'valueid' => $valueArray['optionvalue']
));

$optionValue->save();

return $modx->error->success('', $optionValue);