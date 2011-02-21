<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$categoryId = explode('|', $_REQUEST['categoryId']);
$categoryId = explode(':', $categoryId[0]);
$categoryId = (int) $categoryId[1];	

$category = $modx->getObject('vcCategory', $categoryId);

if ($category == null) {
	return $modx->error->failure('Category not found');
}

$categoryArray = $category->toArray();
$config = $categoryArray['config'];

if (!isset($categoryArray['config']['customFields'])) {
	$categoryArray['config']['customFields'] = array();	
}

$outputArray = array();
foreach($categoryArray['config']['customFields'] as $key => $value) {
	$outputArray[] = array(
		'key' => $key,
		'value' => $value
	);
}

return $this->outputArray($outputArray, sizeof($outputArray));