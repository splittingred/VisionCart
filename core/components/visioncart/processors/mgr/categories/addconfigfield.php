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

if (!isset($config['customFields'])) {
	$config['customFields'] = array();	
}

if ($_REQUEST['isUpdate'] == true) {
	if (isset($config['customFields'][$_REQUEST['oldField']])) {
		unset($config['customFields'][$_REQUEST['oldField']]);
	}
}

$config['customFields'][$_REQUEST['key']] = $_REQUEST['value'];

$category->set('config', $config);
$category->save();

return $modx->error->success('', $category);