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

$finalSortArray = json_decode($_REQUEST['sortArray'], true);

$productLinkArray = array();
foreach($productLinks as $productLink) {
	if ($hideSkus == 1) {
		$product = $productLink->getOne('Product');
		if ($product->get('parent') != 0) {
			continue;	
		}
	}
	
	$productLinkArray[$productLink->get('id')] = $productLink;
}

foreach($finalSortArray as $key => $productLinkId) {
	if (isset($productLinkArray[$productLinkId])) {
		$productLinkArray[$productLinkId]->set('sort', $key);
		$productLinkArray[$productLinkId]->save();
	}
}

return $modx->error->success('');