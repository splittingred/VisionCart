<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$modules = $modx->getCollection('vcModule', array('type' => 'payment'));
        
$list = array();
foreach ($modules as $module) {
	$moduleArray = $module->toArray();
    $list[] = $moduleArray;
}

return $this->outputArray($list, sizeof($list));