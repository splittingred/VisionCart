<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;
$categories = array();
$output = '';

if ($modx->visioncart->shop != null) {
	$shopId = $modx->visioncart->shop->get('id');	
}

$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($vc->shop->get('id'), 'getCategories', null, array('config' => $scriptProperties['config']));
$scriptProperties = array_merge($config, $scriptProperties);

$scriptProperties['parents'] = $modx->getOption('parents', $scriptProperties, 0);
$scriptProperties['exclude'] = $modx->getOption('exclude', $scriptProperties, '');
$scriptProperties['shopId'] = $modx->getOption('shopId', $scriptProperties, $shopId);

$scriptProperties['parents'] = explode(',', $scriptProperties['parents']);
$scriptProperties['exclude'] = explode(',', $scriptProperties['exclude']);

if ($scriptProperties['tpl'] == '') {
	return '';
}

foreach($scriptProperties['parents'] as $parent) {
	$stack = $vc->getCategories($scriptProperties['shopId'], array(
		'parent' => (int) $parent,
		'asArray' => true
	));
	
	foreach($stack as $category) {
		if (!in_array($category['id'], $scriptProperties['exclude'])) {
			$category['url'] = $vc->makeUrl(array(
				'categoryId' => $category['id'],
				'shopId' => $scriptProperties['shopId']
			));
			$categories[] = $category;
		}
	}
}

foreach($categories as $category) {
	$output .= $vc->parseChunk($scriptProperties['tpl'], $category);
}

return $output;