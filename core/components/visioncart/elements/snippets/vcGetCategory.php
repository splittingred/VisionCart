<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;

$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($vc->shop->get('id'), 'getCategory', null, array('config' => $scriptProperties['config']));
$scriptProperties = array_merge($config, $scriptProperties);

if (!isset($scriptProperties['tpl']) || $scriptProperties['tpl'] == '') {
	return '';
}

if (isset($scriptProperties['id'])) {
	$category = $vc->getCategory($scriptProperties['id']);
} else {
	$category = $vc->getCategory(end($vc->router['categories']), array(
		'whereColumn' => 'alias'
	));
}

if ($category == null) {
	return '';	
}

return $vc->parseChunk($scriptProperties['tpl'], $category->toArray());