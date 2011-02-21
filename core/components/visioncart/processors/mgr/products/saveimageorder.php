<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$order = $_REQUEST['order'];
$product = $modx->getObject('vcProduct', (int) $_REQUEST['id']);

$order = explode(',', $order);

$finalArray = array();
foreach($order as $image) {
	if (substr($image, 0, 7) == 'product') {
		$imageName = explode('-', $image);
		$imageName = $imageName[2];
		
		$finalArray[] = $imageName;
	}	
}

$product->set('pictures', $finalArray);
$product->save();