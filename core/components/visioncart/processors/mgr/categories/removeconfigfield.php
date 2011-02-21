<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$categoryId = explode('|', $_REQUEST['categoryId']);
$categoryId = explode(':', $categoryId[0]);
$categoryId = (int) $categoryId[1];	

$category = $modx->getObject('vcCategory', $categoryId);

if ($category == null) {
	return $modx->error->failure('Category not found');
}

$config = $category->get('config');

if (!is_array($config['customFields'])) {
	$config['customFields'] = array();	
}

if (isset($config['customFields'][$_REQUEST['key']])) {
	unset($config['customFields'][$_REQUEST['key']]);
}

$category->set('config', $config);
$category->save();

return $modx->error->success('', $category);