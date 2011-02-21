<?php

if (!isset($_GET['prodid'])) {
	$productProperties = array(
		'shopid' => (int) $_REQUEST['shopid'],
		'active' => 2
	);
	
	$product = $modx->newObject('vcProduct', $productProperties);
	$product->save();
	
	if (isset($_REQUEST['catid'])) {
		// Get highest sorted product from category
		$query = $modx->newQuery('vcProductCategory');
		$query->where(array('categoryid' => (int) $_REQUEST['catid']));
		$query->sortby('sort', 'DESC');
		$query->limit(1);
		
		$highest = $modx->getObject('vcProductCategory', $query);
		
		if ($highest == null) {
			$sort = 0;	
		} else {
			$sort = $highest->get('sort') + 1;
		}
		
		// Create new category
		$categoryLink = $modx->newObject('vcProductCategory', array(
			'shopid' => (int) $_REQUEST['shopid'],
			'categoryid' => (int) $_REQUEST['catid'],
			'productid' => $product->get('id'),
			'sort' => $sort
		));
		
		$categoryLink->save();
	}
	
	if (isset($_REQUEST['sku'])) {
		// Create SKU based on parent
		$parent = $modx->getObject('vcProduct', (int) $_REQUEST['parent']);
		
		$product->fromArray($parent->toArray());
		$product->set('parent', (int) $_REQUEST['parent']);
		
		$product->save();
		
		// Clone categories
		$categories = $modx->getCollection('vcProductCategory', array(
			'shopid' => $_REQUEST['shopid'],
			'productid' => $_REQUEST['parent']
		));
		
		foreach($categories as $key => $value) {
			$category = $value->getOne('Category');
			
			// Get highest sorted product from category
			$query = $modx->newQuery('vcProductCategory');
			$query->where(array('categoryid' => $category->get('id')));
			$query->sortby('sort', 'DESC');
			$query->limit(1);
			
			$highest = $modx->getObject('vcProductCategory', $query);
			
			$newLink = $modx->newObject('vcProductCategory', array(
				'shopid' => $_REQUEST['shopid'],
				'productid' => $product->get('id'),
				'categoryid' => $value->get('categoryid'),
				'sort' => $highest->get('sort')+1
			));
			
			$newLink->save();
		}
		
		// Clone product options
		$productOptions = $modx->getCollection('vcProductOption', array(
			'productid' => (int) $_REQUEST['parent']
		));
		
		foreach($productOptions as $productOption) {
			$newOption = $modx->newObject('vcProductOption');
			$newOption->fromArray($productOption->toArray());
			$newOption->set('productid', $product->get('id'));
			$newOption->save();
		}
	}
	
	$modx->sendRedirect($_SERVER['REQUEST_URI'].'&prodid='.$product->get('id'));
} else {
	$product = $modx->getObject('vcProduct', $_GET['prodid']);
	
	if ($product->get('parent') == 0) {
		$parentId = $product->get('id');	
	} else {
		$parentId = $product->get('parent');	
	}
	
	// Get the shop
	$taxesCategory = $modx->visioncart->getShopSetting('taxesCategory', $_REQUEST['shopid']);
	
	$modx->regClientStartupHTMLBlock('<script type="text/javascript">vcCore.config.productParent = '.$parentId.';</script>');	
	$modx->regClientStartupHTMLBlock('<script type="text/javascript">vcCore.config.taxesCategory = '.$taxesCategory.';</script>');	
}

return '<div id="vc-ajax-haze" class="vc-ajax-haze"></div><iframe width="0" style="visibility: hidden;" height="0" frameborder="0" name="file-upload" id="file-upload" src=""></iframe><div id="visioncart-container"></div>'; 