<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$categories = $modx->getCollection('vcCategory', array('parent' => (int) $_REQUEST['parent'], 'shopid' => $_REQUEST['shopId']));
  
$list = array();
foreach ($categories as $category) {
	$categoryArray = $category->toArray();
    $list[] = $categoryArray;
}

return $this->outputArray($list, sizeof($list));