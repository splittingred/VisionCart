<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;
$parent = $_REQUEST['node'];

$parent = explode('|', $parent);
$parent = explode(':', $parent[0]);
$parent = (int) $parent[1];	

return json_encode($visionCart->getProductNodes(0, array(
	'shopid' => (int) $_REQUEST['shop'],
	'parent' => $parent,
	'hideskus' => $_REQUEST['hideskus']
)));