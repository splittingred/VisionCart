<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

if (isset($_REQUEST['categoryView']) && $_REQUEST['categoryView'] == true) {
	$categoryId = explode('|', $_REQUEST['parent']);
	$categoryId = explode(':', $categoryId[0]);
	$categoryId = (int) $categoryId[1];	
	$productId = (int) $_REQUEST['prodid'];
	$shopId = (int) $_REQUEST['shopid'];
	$checked = 'false';	
} else {
	$categoryId = explode('|', $_REQUEST['catid']);
	$categoryId = explode(':', $categoryId[0]);
	$categoryId = (int) $categoryId[1];	
	$productId = (int) $_REQUEST['prodid'];
	$shopId = (int) $_REQUEST['shopid'];
	$checked = $_REQUEST['checked'];
}

// Try to fetch a existing link
$link = $modx->getObject('vcProductCategory', array(
	'productid' => $productId,
	'categoryid' => $categoryId
));

if ($checked == 'true') {
	if ($link == null) {
		$link = $modx->newObject('vcProductCategory', array(
			'productid' => $productId,
			'categoryid' => $categoryId,
			'shopid' => $shopId
		));	
		$link->save();
	}
	
	// If it's a new category also create one for each child SKU
	$children = $modx->getCollection('vcProduct', array(
		'parent' => $productId
	));
	
	foreach($children as $child) {
		// Try to fetch a existing link
		$link = $modx->getObject('vcProductCategory', array(
			'productid' => $child->get('id'),
			'categoryid' => $categoryId
		));
		if ($link == null) {
			$link = $modx->newObject('vcProductCategory', array(
				'productid' => $child->get('id'),
				'categoryid' => $categoryId,
				'shopid' => $shopId
			));	
			$link->save();
		} else {
			$modx->visioncart->logError('Link for SKU already existed when creating parent vcProductCategory link', __FILE__, __LINE__, 2);		
		}
	}
} else {
	
	if ($link != null) {
		// Check if product is in at least one category
		$linkCollection = $modx->getCollection('vcProductCategory', array(
			'productid' => $productId
		));
		
		if (sizeof($linkCollection) > 1) {
			// Remove every SKU from this category
			$children = $modx->getCollection('vcProduct', array(
				'parent' => $productId
			));
			
			foreach($children as $child) {
				$childLink = $modx->getObject('vcProductCategory', array(
					'productid' => $child->get('id'),
					'categoryid' => $categoryId
				));	
				if ($childLink != null) {
					$childLink->remove();
				} else {
					$modx->visioncart->logError('Could not find SKU child link for vcProductCategory removal', __FILE__, __LINE__, 2);	
				}
			}
			
			$link->remove();	
		} else {
			return $modx->error->failure('Last category');
		}	
	}
}

return $modx->error->success('');