<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$query = $modx->getOption('query', $scriptProperties, '');

$resources = $modx->getCollection('modResource', array('pagetitle:LIKE' => '%'.$query.'%'));
        
$list = array();
$list[0] = array(
	'id' => 0,
	'pagetitle' => 'Shop default'
);

foreach ($resources as $resource) {
    $list[] = $resource->toArray();
}

return $this->outputArray($list, sizeof($list));