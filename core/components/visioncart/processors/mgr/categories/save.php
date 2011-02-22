<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;
$categoryConfig = json_decode($_REQUEST['categoryConfig'], true);
$parent = $_REQUEST['parent'];
$parent = explode('|', $parent);
$parent = explode(':', $parent[0]);
$parent = (int) $parent[1];	

$categoryConfig['description'] = $modx->visioncart->cleanExt($categoryConfig['description']);

$configArray = array(
	array('key' => 'chunk', 'defaultValue' => 0),
	array('key' => 'resource', 'defaultValue' => 0)
);

$configOutput = array();
foreach($configArray as $config) {
	$categoryConfig['config'][$config['key']] = $categoryConfig[$config['key']];
}

if (isset($categoryConfig['id']) && !empty($categoryConfig['id']) && $categoryConfig['id'] != 0) {
	$category = $modx->getObject('vcCategory', $categoryConfig['id']);
	
	$config = $category->get('config');
	$categoryConfig['config'] = array_merge($config, $categoryConfig['config']);
	$category->fromArray($categoryConfig);
	$category->save();
} else {
	$category = $modx->newObject('vcCategory', $categoryConfig);
	$category->set('parent', $parent);
	$category->set('shopid', (int) $_REQUEST['shop']);
	$category->save();
}

if ($categoryConfig['emptyCache'] == true) {
	$modx->runProcessor('clearCache', array(), array(
		'location' => 'system'
	));
}

// Return values
if ($category->save()) {
	return $modx->error->success('', $category);
} else {
	return $modx->error->failure('');
}