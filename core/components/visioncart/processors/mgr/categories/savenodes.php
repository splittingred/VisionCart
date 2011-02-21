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

$currentPlace = 0;
$count = 0;
foreach($categories as $category) {
	if ($currentPlace == $targetSort) {
		$sourceCategory->set('sort', $count);
		$count++;
	}
	
	$category->set('sort', $count);
	$category->save();
	$count++;
	$currentPlace += 1;
}

$sourceCategory->save();

return $modx->error->success('');