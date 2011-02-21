<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$productSnippets = $modx->getCollection('modSnippet', array('name:LIKE' => 'vcEventProduct%'));
        
$list = array();
foreach ($productSnippets as $productSnippet) {
	$returnValue = $modx->runSnippet($productSnippet->get('name'), array(
		'vcAction' => 'getParams'
	));
	
	$tabArray = json_decode($returnValue);
	
	$active = true;
	if (isset($tabArray->active) && $tabArray->active == false) {
		$active = false;	
	}
	
	if ($tabArray->showTab == true && $active == true) {
		$tabArray->id = $productSnippet->get('id');

		$list[] = $tabArray;
	}
}

return $modx->error->success('', $list);