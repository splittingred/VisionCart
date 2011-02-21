<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$categoryId = explode('|', $_REQUEST['id']);
$categoryId = explode(':', $categoryId[0]);
$categoryId = (int) $categoryId[1];	

$category = $modx->getObject('vcCategory', $categoryId);

if ($category == null) {
	return $modx->error->failure('Category not found');
}

$categoryArray = $category->toArray();

if (!isset($categoryArray['config']['resource'])) {
	$categoryArray['config']['resource'] = 0;
}

if (!isset($categoryArray['config']['chunk'])) {
	$categoryArray['config']['chunk'] = 0;
}

return $modx->error->success('', $categoryArray);