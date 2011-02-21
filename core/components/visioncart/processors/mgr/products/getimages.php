<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;
$targetDir = $modx->getOption('base_path').'assets/components/visioncart/web/images/products/';
$htmlPath = '/assets/components/visioncart/web/images/products/';
$prodId = (int) $_REQUEST['id'];
$shopId = (int) $_REQUEST['shopid'];

$product = $modx->getObject('vcProduct', $prodId);
$currentImages = $product->get('pictures');
$thumbnailArray = $visionCart->getThumbnailSettings($shopId);

// Get the smallest thumbnail
$smallest = false;
if (is_array($thumbnailArray)) {
	foreach($thumbnailArray as $thumb) {
		if ($smallest == false) {
			$smallest = $thumb;
		}	
		
		if ($thumb['w'] < $smallest['w']) {
			$smallest = $thumb;
		}
	}
	
	$prefix = $smallest['prefix'];
	
	foreach($currentImages as $image) {
		echo '<div class="product-image" id="product-image-'.$image.'"><img src="'.$htmlPath.$prefix.$image.'?time='.mt_rand(100, 999).time().'" alt="" /></div>';	
	}
}