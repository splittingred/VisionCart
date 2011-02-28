<?php
/**
 * @package visioncart
 */
 
$vc =& $modx->visioncart;

if (!isset($scriptProperties['id']) || $scriptProperties['id'] == '') {
	return '';
}
 
$config = $vc->getConfigFile($vc->shop->get('id'), 'getProductCustomFields');
$scriptProperties = array_merge($config, $scriptProperties);

if (!isset($scriptProperties['rowTpl']) || $scriptProperties['rowTpl'] == '') {
	return '';
}

if (!isset($scriptProperties['wrapperTpl']) || $scriptProperties['wrapperTpl'] == '') {
	return '';
}

$output = array(
	'wrapper' => '',
	'rows' => ''
);

$product = $vc->getProduct($scriptProperties['id']);

if ($product == null) {
	return '';	
}

$link = $product->getOne('ProductCategory');
$category = $vc->getCategory($link->get('categoryid'), array(
	'whereColumn' => 'id',
	'asArray' => true
));
$product = $product->toArray();

if ($product['customfields'] != '' && is_array($product['customfields'])) {
	$fields = array();
	$values = $product['customfields'];
	
	$fields = $values[$category['id']];
	foreach($fields as $field => $value) {
		$output['rows'] .= $vc->parseChunk($scriptProperties['rowTpl'], array(
			'field' => $field,
		 	'value' => $value
		));
	}
	
	unset($product, $field, $value);
	
	if ($output['rows'] != '') {
		$output['wrapper'] = $vc->parseChunk($scriptProperties['wrapperTpl'], array(
			'rows' => $output['rows']
		));
		
		return $output['wrapper'];
	}
}

return '';