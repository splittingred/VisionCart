<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$id = explode('|', $_REQUEST['id']);
$id = explode(':', $id[0]);
$id = (int) $id[1];	

$category = $modx->getObject('vcCategory', $id);

if ($_REQUEST['checked'] == 'false') {
	$category->set('active', 0);
} else {
	$category->set('active', 1);
}

$category->save();