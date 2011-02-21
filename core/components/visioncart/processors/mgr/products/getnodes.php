<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;
$parent = $_REQUEST['node'];

$parent = explode('|', $parent);
$parent = explode(':', $parent[0]);
$parent = (int) $parent[1];	

echo json_encode($visionCart->getCategoryNodes(0, array(
	'shopid' => (int) $_REQUEST['shop'],
	'productview' => true,
	'sku' => $_REQUEST['sku'],
	'productId' => (int) $_REQUEST['productId']
)));