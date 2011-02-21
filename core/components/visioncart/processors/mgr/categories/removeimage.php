<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$image = $_REQUEST['image'];
$visionCart = $modx->visioncart;
$targetDir = $modx->getOption('base_path').'assets/components/visioncart/web/images/categories/';
$htmlPath = '/assets/components/visioncart/web/images/categories/';
$categoryId = (int) $_REQUEST['id'];
$shopId = (int) $_REQUEST['shopid'];

$category = $modx->getObject('vcCategory', $categoryId);
$config = $category->get('config');

if (isset($config['categoryThumbnail'])) {
	$currentPicture = $config['categoryThumbnail'];
} else {
	$currentPicture = '';	
}

$thumbnailArray = $visionCart->getThumbnailSettings($shopId, true);

foreach($thumbnailArray as $thumb) {
	$path = $targetDir.$thumb['prefix'].$currentPicture;
	if (is_file($path)) {
		@unlink($path);
	}
}	

unset($config['categoryThumbnail']);


$category->set('config', $config);
$category->save();