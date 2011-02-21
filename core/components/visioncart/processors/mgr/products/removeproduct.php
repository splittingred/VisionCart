<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$prodId = (int) $_REQUEST['prodid'];
$visionCart = $modx->visioncart;
$targetDir = $modx->getOption('base_path').'assets/components/visioncart/web/images/products/';

$product = $modx->getObject('vcProduct', $prodId);

if ($product != null) {
	$productImages = $product->get('pictures');
	$thumbnailArray = $visionCart->getThumbnailSettings($product->get('shopid'));
	
	// If product is a parent, remove all SKU's
	if ($product->get('parent') == 0) {
		$skus = $modx->removeCollection('vcProduct', array(
			'parent' => $product->get('id')
		));
	}
	
	$product->remove();	
	
	foreach($productImages as $image) {
		// Remove all images
		if ($product->get('parent') == 0) {
			$productList = $modx->getCollection('vcProduct', array(
				'parent' => $product->get('id')
			));
		} else {
			$query = $modx->newQuery('vcProduct');
			$query->where(array('parent' => $product->get('parent')));
			$query->orCondition(array('id' => $product->get('parent')));
			$productList = $modx->getCollection('vcProduct', $query);
		}
		
		$imageUsed = false;
		
		foreach($productList as $childProduct) {
			if (in_array($image, $childProduct->get('pictures')) && $childProduct->get('id') != $product->get('id')) {
				$imageUsed = true;
			}
		}
		
		if (!$imageUsed) {
			foreach($thumbnailArray as $thumb) {
				$path = $targetDir.$thumb['prefix'].$image;
				if (is_file($path)) {
					@unlink($path);
				}
			}	
		}
	}
	
	return $modx->error->success('');
} else {
	return $modx->error->failure('');	
}