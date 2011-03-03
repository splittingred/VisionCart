<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$image = $_REQUEST['image'];
$visionCart = $modx->visioncart;
$targetDir = $visionCart->config['assetsBasePath'].'web/images/products/';
$htmlPath = '/assets/components/visioncart/web/images/products/';
$prodId = (int) $_REQUEST['id'];
$shopId = (int) $_REQUEST['shopid'];

$product = $modx->getObject('vcProduct', $prodId);
$currentImages = $product->get('pictures');
$thumbnailArray = $visionCart->getThumbnailSettings($shopId);

$image = explode('-', $image);
$image = $image[2];

$id = explode('_', $image);
$id = (int) $id[0];

// Check if the image is still in use by one of the SKU's
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

foreach($currentImages as $key => $value) {
	if ($value == $image) {
		unset($currentImages[$key]);	
	}	
}

$product->set('pictures', $currentImages);
$product->save();