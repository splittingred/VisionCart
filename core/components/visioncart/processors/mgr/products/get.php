<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$productId = (int) $_REQUEST['id'];

$product = $modx->getObject('vcProduct', $productId);

if ($product == null) {
	return $modx->error->failure('Product not found');
}

$productArray = $product->toArray();

$productOptions = $modx->getCollection('vcProductOption', array(
	'productid' => $productId
));

foreach($productOptions as $productOption) {
	$option = $modx->getObject('vcOption', $productOption->get('optionid'));	
	
	$productArray['productoption_'.$productOption->get('optionid')] = $productOption->get('valueid');
}

if ($productArray['publishdate'] != 0) {
	$productArray['publishdate'] = date('m/d/Y', strtotime($productArray['publishdate']));
} else {
	$productArray['publishdate'] = '';	
}

if ($productArray['unpublishdate'] != 0) {
	$productArray['unpublishdate'] = date('m/d/Y', strtotime($productArray['unpublishdate']));
} else {
	$productArray['unpublishdate'] = '';		
}

return $modx->error->success('', $productArray);