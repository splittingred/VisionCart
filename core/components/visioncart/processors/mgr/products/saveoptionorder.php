<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$order = $_REQUEST['order'];
$order = explode(',', $order);

// Fetch parent product
$productOption = $modx->getObject('vcProductOption', $order[0]);
$product = $productOption->getOne('Product');
if ($product->get('parent') != 0) {
	$product = $modx->getObject('vcProduct', $product->get('parent'));	
}

$skuArray = array();
$skus = $modx->getCollection('vcProduct', array(
	'parent' => $product->get('id')
));
foreach($skus as $sku) {
	$skuArray[] = $sku;	
}
$skuArray[] = $product;

$count = 1;
foreach($order as $id) {
	$id = trim($id);
	if ($id != '') {
		$productOption = $modx->getObject('vcProductOption', $id);
		if ($productOption != null) {
			// Update every SKU's option order
			foreach($skuArray as $sku) {
				$productOption = $modx->getObject('vcProductOption', array(
					'optionid' => $productOption->get('optionid'),
					'productid' => $sku->get('id')
				));
				$productOption->set('sort', $count);
				$productOption->save();
			}
			$count++;
		}
	}
}

return $modx->error->success('');