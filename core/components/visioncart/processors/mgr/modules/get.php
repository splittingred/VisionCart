<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$moduleId = (int) $_REQUEST['id'];

$module = $modx->getObject('vcModule', $moduleId);

if ($module == null) {
	return $modx->error->failure('Module not found');
}

$moduleObject = $module->toArray();

$visionCart = $modx->visioncart;
$moduleObject['extraConfig'] = $visionCart->getModuleConfig($moduleId);

return $modx->error->success('', $moduleObject);