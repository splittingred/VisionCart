<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;

$option = $modx->getObject('vcProductOption', array(
	'productid' => (int) $_REQUEST['prodid'],
	'optionid' => (int) $_REQUEST['optionId']
));

if ($option != null) {
	$option->remove();
	
	// Fetch all SKU's	
	$skus = $modx->getCollection('vcProduct', array(
		'parent' => (int) $_REQUEST['prodid']
	));
	
	foreach($skus as $sku) {
		$option = $modx->getObject('vcProductOption', array(
			'productid' => $sku->get('id'),
			'optionid' => (int) $_REQUEST['optionId']
		));
		
		if ($option != null) {
			$option->remove();
		}
	}
}

return $modx->error->success();