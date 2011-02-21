<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$productId = (int) $_REQUEST['id'];
$product = $modx->getObject('vcProduct', $productId);

$outputArray = array();

$productOptions = $modx->getCollection('vcProductOption', array(
	'productid' => $productId
));

foreach($productOptions as $productOption) {
	$option = $modx->getObject('vcOption', $productOption->get('optionid'));	
	$outputArray[] = array_merge($option->toArray(), $productOption->toArray());
}

return json_encode($outputArray);