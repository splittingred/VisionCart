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
$customFields = $categoryArray['customfields'];

$outputArray = array();
foreach($customFields as $key => $value) {
	$outputArray[] = array(
		'name' => $key,
		'type' => $value['type'],
		'mandatory' => $value['mandatory']
	);
}

return $this->outputArray($outputArray, sizeof($outputArray));