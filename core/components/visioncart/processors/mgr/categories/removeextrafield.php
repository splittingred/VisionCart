<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$categoryId = explode('|', $_REQUEST['categoryId']);
$categoryId = explode(':', $categoryId[0]);
$categoryId = (int) $categoryId[1];	

$category = $modx->getObject('vcCategory', $categoryId);

if ($category == null) {
	return $modx->error->failure('Category not found');
}

$customFields = $category->get('customfields');

if (!is_array($customFields)) {
	$customFields = array();	
}

if (isset($customFields[$_REQUEST['name']])) {
	unset($customFields[$_REQUEST['name']]);
	$category->set('customfields', $customFields);
	$category->save();
}

return $modx->error->success('', $category);