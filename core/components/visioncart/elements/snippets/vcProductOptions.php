<?php
/**
 * @package visioncart
 */
$vc =& $modx->visioncart;

$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($vc->shop->get('id'), 'getProductOptions', null, array('config' => $scriptProperties['config']));
$scriptProperties = array_merge($config, $scriptProperties);

$scriptProperties['tpl'] = $modx->getOption('tpl', $scriptProperties, '');
$scriptProperties['rowTpl'] = $modx->getOption('rowTpl', $scriptProperties, '');
$scriptProperties['return'] = $modx->getOption('return', $scriptProperties, 'tpl');
$scriptProperties['id'] = $modx->getOption('id', $scriptProperties, $vc->product->get('id'));
$scriptProperties['selectedValues'] = $modx->getOption('selectedValues', $scriptProperties, array());
$scriptProperties['categoryId'] = $modx->getOption('categoryId', $scriptProperties, $vc->category->get('id'));
$scriptProperties['scheme'] = $modx->getOption('scheme', $scriptProperties, -1);

if (!isset($scriptProperties['id']) || !is_numeric($scriptProperties['id'])) {
	return '';
}

if (!isset($scriptProperties['categoryId']) || !is_numeric($scriptProperties['categoryId'])) {
	return '';
}

if (!isset($scriptProperties['tpl']) || $scriptProperties['categoryId'] == '') {
	return '';
}

if (!isset($scriptProperties['rowTpl']) || $scriptProperties['rowTpl'] == '') {
	return '';
}

/*$tpl = $modx->getOption('tpl', $scriptProperties, 'vcProductOptions');
$rowTpl = $modx->getOption('rowTpl', $scriptProperties, 'vcProductOptionsRow');
$return = $modx->getOption('return', $scriptProperties, 'tpl');
$id = $modx->getOption('id', $scriptProperties, false);
$selectedValues = $modx->getOption('selectedValues', $scriptProperties, array());
$categoryId = $modx->getOption('categoryId', $scriptProperties, $vc->category->get('id'));*/

if (!is_numeric($scriptProperties['id'])) {
	$productId = $vc->router['product']['id'];
} else {
	$productId = $scriptProperties['id'];
}

$currentProduct = $vc->getProduct($scriptProperties['id']);
if ($currentProduct->get('parent') != 0) {
	$productId = $currentProduct->get('parent');
	$product = $vc->getProduct($currentProduct->get('parent'));
} else {
	$product = $currentProduct;	
}

$returnArray = array();
$skuArray = array($product);
$output = '';
$innerChunk = '';
$hiddenInputs = "\n";
$optionArray = array();

// Set selected options
if (isset($_GET['option'])) {
	$optionValues = $modx->getCollection('vcProductOption', array(
		'productid' => $currentProduct->get('id')
	));
	
	foreach($optionValues as $value) {
		$scriptProperties['selectedValues'][$value->get('optionid')] = $value->get('valueid');
	}
}

$query = $modx->newQuery('vcProductOption', array(
	'productid' => $productId
));
$query->sortby('sort', 'ASC');

$optionValues = $modx->getCollection('vcProductOption', $query);

if (sizeof($optionValues) > 0) {
	$masterOption = reset($optionValues);
}

$skipSku = false;

// Check if selected option matches SKU
foreach($optionValues as $value) {
	if ($value->get('optionid') == $masterOption->get('optionid') && $value->get('valueid') == $scriptProperties['selectedValues'][$masterOption->get('optionid')]) {
		/*$skipSku = false;
		break;	*/
	}
	if (isset($scriptProperties['selectedValues'][$value->get('optionid')]) && $scriptProperties['selectedValues'][$value->get('optionid')] != $value->get('valueid') && $scriptProperties['selectedValues'][$value->get('optionid')] != 0) {
		$skipSku = true;
		continue;
	}
}

foreach($optionValues as $value) {
	if ($skipSku == false || $value->get('optionid') == $masterOption->get('optionid')) {
		$value = $value->getOne('OptionValue');
		$optionArray[$value->get('optionid')][$value->get('id')] = $value->toArray();	
	}
}

// Fetch all SKUs
$skus = $vc->getSKUs($scriptProperties['id'], $vc->category->get('id'));
foreach($skus as $sku) {
	$skuArray[] = $sku;
	$optionValues = $modx->getCollection('vcProductOption', array(
		'productid' => $sku->get('id')
	));
	
	$skipSku = false;
	
	// Check if selected option matches SKU
	foreach($optionValues as $value) {
		if ($value->get('optionid') == $masterOption->get('optionid') && 
		$value->get('valueid') == $scriptProperties['selectedValues'][$masterOption->get('optionid')]) {
			/*$skipSku = false;
			break;	*/
		}

		if (isset($scriptProperties['selectedValues'][$value->get('optionid')]) && $scriptProperties['selectedValues'][$value->get('optionid')] != $value->get('valueid') && $scriptProperties['selectedValues'][$value->get('optionid')] != 0) {
			$skipSku = true;
			continue;	
		}
	}
	
	foreach($optionValues as $value) {
		if ($skipSku == false || $value->get('optionid') == $masterOption->get('optionid')) {
			$value = $value->getOne('OptionValue');
			$optionArray[$value->get('optionid')][$value->get('id')] = $value->toArray();	
		}
	}
}

// Prepare HTML responses
foreach($optionArray as $optionId => $optionValues) {
	$option = $modx->getObject('vcOption', $optionId);
	
	array_unshift($optionValues, array(
		'id' => 0,
		'optionid' => $optionId,
		'value' => 'Select value'
	));

	$selectedValue = isset($scriptProperties['selectedValues'][$optionId]) ? $scriptProperties['selectedValues'][$optionId] == '' ? '0' : $scriptProperties['selectedValues'][$optionId] : '0';

	if ($option->get('outputsnippet') == '') {
		$returnValue = '';
	} else {
		$returnValue = $modx->runSnippet($option->get('outputsnippet'), array(
			'option' => $option->toArray(),
			'values' => $optionValues,
			'selectedValue' => $selectedValue
		));
	}
	
	// JSON response
	$returnArray['html'][] = array(
		'option' => $optionId,
		'response' => $returnValue
	);
	 
	// TPL parsing
	$innerChunk .= $vc->parseChunk($scriptProperties['rowTpl'], array(
		'option' => $option->toArray(),
		'innerChunk' => $returnValue
	));
	
	// Hidden input
	$hiddenInputs .= '<input type="hidden" name="vc-product-option-'.$optionId.'" id="vc-product-option-'.$optionId.'" value="'.$selectedValue.'" />'."\n";
}  

// Fetch available SKU (if more then one, return false)
$skuFound = false;
$skuLink = '';

foreach($skuArray as $sku) {
	$optionValues = $modx->getCollection('vcProductOption', array(
		'productid' => $sku->get('id')
	));
	 
	$skipSku = false;
	// Check if selected option matches SKU
	foreach($optionValues as $value) { 
		if (isset($scriptProperties['selectedValues'][$value->get('optionid')]) && $scriptProperties['selectedValues'][$value->get('optionid')] != $value->get('valueid')) {
			$skipSku = true;
		} elseif (!isset($scriptProperties['selectedValues'][$value->get('optionid')])) {
			$skipSku = true;	
		}
	}
	
	if ($skipSku == false) { 
		// We found a match (woohoow)
		if ($skuFound == false) { 
			$skuFound = $sku->get('id');	
		} else { 
			$skuFound = false;
			break;	 
		}
	}
}

if ($skuFound != false) {
	$productLink = $modx->getObject('vcProductCategory', array(
		'productid' => $skuFound,
		'categoryid' => $scriptProperties['categoryId']
	));
	$skuLink = $vc->makeUrl(array(
		'productCategory' => $productLink->get('id'),
		'scheme' => $scriptProperties['scheme']
	));
}

if ($scriptProperties['return'] == 'tpl') {
	$output = $vc->parseChunk($scriptProperties['tpl'], array(
		'innerChunk' => $innerChunk.$hiddenInputs
	));
	
	return $output;
} else {
	$returnArray['productId'] = $skuFound;
	$returnArray['productLink'] = $skuLink;
	return json_encode($returnArray);	
}