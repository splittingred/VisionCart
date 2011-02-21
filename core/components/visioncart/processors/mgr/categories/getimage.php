<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;
$targetDir = $modx->getOption('base_path').'assets/components/visioncart/web/images/categories/';
$htmlPath = '/assets/components/visioncart/web/images/categories/';
$categoryId = (int) $_REQUEST['id'];
$shopId = (int) $_REQUEST['shopid'];

$category = $modx->getObject('vcCategory', $categoryId);
$config = $category->get('config');
$thumbnailArray = $visionCart->getThumbnailSettings($shopId, true);

if (isset($config['categoryThumbnail'])) {
	$currentPicture = $config['categoryThumbnail'];
} else {
	$currentPicture = '';	
}

if ($currentPicture == '') {
	return 'No image has been uploaded yet.';	
}

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
	
	echo '<div class="product-image"><img src="'.$htmlPath.$prefix.$currentPicture.'?time='.mt_rand(100, 999).time().'" alt="" /></div>';	
}