<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;

$optionArray = json_decode($_REQUEST['formData'], true);
$product = $modx->getObject('vcProduct', $optionArray['id']);

$visionCart->fireEvent('vcEventProduct', 'before', array(
	'vcAction' => 'process',
	'formData' => $optionArray,
	'productId' => $optionArray['id'],
	'product' => $product
));

// Decode again for plugin purposes
$optionArray = json_decode($_REQUEST['formData'], true);

// Get extra config array
$extraInfo = $product->get('customfields');
foreach($optionArray as $key => $value) {
	if (substr($key, 0, 12) == 'extraconfig_') {
		$value = $modx->visioncart->cleanExt($value);
		$key = substr($key, 12);
		$key = explode('_', $key);
		$id = $key[0];
		
		array_shift($key);
		$key = implode('_', $key);
		
		$extraInfo[$id][$key] = $value;
	}
}

if ($optionArray['emptyCache'] == true) {
	$modx->runProcessor('clearCache', array(), array(
		'location' => 'system'
	));
}

$product->fromArray(array(
	'shopid' => (int) $_REQUEST['shopId'], 
	'taxcategory' => (int) $optionArray['taxcategory'],
	'name' => $optionArray['name'],
	'alias' => $optionArray['alias'],
	'description' => $modx->visioncart->cleanExt($optionArray['description']),
	'articlenumber' => $optionArray['articlenumber'],
	'price' => str_replace(',', '.', $optionArray['price']),
	'weight' => $optionArray['weight'],
	'shippingprice' => $optionArray['shippingprice'],
	'publishdate' => strtotime($optionArray['publishdate']),
	'unpublishdate' => strtotime($optionArray['unpublishdate']),
	'stock' => $optionArray['stock'],
	'customfields' => isset($extraInfo) ? $extraInfo : '',
	'pictures' => isset($optionArray['pictures']) ? $optionArray['pictures'] : '',
	'active' => $optionArray['active']
));

$product->save();

if ($_REQUEST['sku'] != '') {
	foreach($optionArray as $key => $value) {
		if (substr($key, 0, 14) == 'productoption_') {
			$optionId = substr($key, 14);
			
			// Save the new options
			$currentOption = $modx->getObject('vcProductOption', array(
				'productid' => $optionArray['id'],
				'optionid' => $optionId
			));
			
			$currentOption->set('valueid', $value);
			$currentOption->save();
		}	
	}
}

$visionCart->fireEvent('vcEventProduct', 'after', array(
	'vcAction' => 'process',
	'formData' => $optionArray,
	'productId' => $optionArray['id'],
	'product' => $product
));

return $modx->error->success('', $product);