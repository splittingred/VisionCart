<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$contexts = $modx->getCollection('modContext', array('key:!=' => 'mgr'));
        
$list = array();
foreach ($contexts as $context) {
	$contextArray = $context->toArray();
    $list[] = $contextArray;
}

return $this->outputArray($list, sizeof($list));