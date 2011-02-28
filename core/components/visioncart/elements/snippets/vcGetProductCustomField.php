<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;

if (isset($scriptProperties['id']) || !is_numeric($scriptProperties['id'])) {
	return '';
}

$product = $vc->getProduct($scriptProperties['id']);

if ($product == null) {
	return '';
}

$link = $product->getOne('ProductCategory');

$product = $product->toArray();
$link = $link->toArray();

if ($product['customfields'] != '' && is_array($product['customfields'])) {
	if (isset($product['customfields'][$link['categoryid']][$field])) {
		return $product['customfields'][$link['categoryid']][$field];
	}
}

return '';