<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;
$source = $_REQUEST['sourceId'];
$source = explode('|', $source);
$source = explode(':', $source[0]);
$source = (int) $source[1];	

$target = $_REQUEST['targetId'];
$target = explode('|', $target);
$target = explode(':', $target[0]);
$target = (int) $target[1];	

$targetSort = (int) $_REQUEST['targetSort'];

$categories = $modx->getCollection('vcCategory', array(
	'parent' => $target
));

$sourceCategory = $modx->getObject('vcCategory', $source);
$sourceCategory->set('parent', $target);
$sourceCategory->save();

$sortArray = json_decode($_REQUEST['sortArray'], true);
$finalSortArray = array();
foreach($sortArray as $category) {
	$category = explode('|', $category);
	$category = explode(':', $category[0]);
	$category = (int) $category[1];		
	$finalSortArray[] = $category;
}

$categoryArray = array();
foreach($categories as $category) {
	$categoryArray[$category->get('id')] = $category;	
}

foreach($finalSortArray as $key => $categoryId) {
	if (isset($categoryArray[$categoryId])) {
		$categoryArray[$categoryId]->set('sort', $key);
		$categoryArray[$categoryId]->save();
	}
}

return $modx->error->success('');