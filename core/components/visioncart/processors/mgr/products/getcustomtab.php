<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$snippetId = (int) $_REQUEST['id'];
$productId = (int) $_REQUEST['prodid'];
$snippet = $modx->getObject('modSnippet', $snippetId);

$returnValue = $modx->runSnippet($snippet->get('name'), array(
	'vcAction' => 'view',
	'productId' => $productId
));

return $returnValue;