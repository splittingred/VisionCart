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

if (isset($customFields[$_REQUEST['field']])) {
	$output = array(
		'name' => $_REQUEST['field'],
		'type' => $customFields[$_REQUEST['field']]['type'],
		'mandatory' => $customFields[$_REQUEST['field']]['mandatory'],
		'values' => $customFields[$_REQUEST['field']]['values'],
	);
	return $modx->error->success('', $output);
} else {
	return $modx->error->failure('Field not found');
}