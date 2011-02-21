<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;
$source = $_REQUEST['sourceId'];
$hideSkus = (int) $_REQUEST['hideSku'];

$target = $_REQUEST['targetId'];
$target = explode('|', $target);
$target = explode(':', $target[0]);
$target = (int) $target[1];	

$targetSort = (int) $_REQUEST['targetSort'];

$query = $modx->newQuery('vcProductCategory', array(
	'categoryid' => $target
));
$query->sortby('sort', 'ASC');

$productLinks = $modx->getCollection('vcProductCategory', $query);

$sourceLink = $modx->getObject('vcProductCategory', array(
	'productid' => $source,
	'categoryid' => $target
));

$currentPlace = 0;
$count = 0;
foreach($productLinks as $productLink) {
	if ($hideSkus == 1) {
		$product = $productLink->getOne('Product');
		if ($product->get('parent') != 0) {
			continue;	
		}
	}
	
	if ($currentPlace == $targetSort) {
		$sourceLink->set('sort', $count);
		$sourceLink->save();
		$count++;
	}
	
	if ($productLink->get('id') != $sourceLink->get('id')) {
		$productLink->set('sort', $count);
		$productLink->save();
		$count++;
	}
	$currentPlace += 1;
}