<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$moduleId = (int) $_REQUEST['id'];

$visionCart = $modx->visioncart;
$moduleConfig = $visionCart->getModuleConfig($moduleId);

return $modx->error->success('', $moduleConfig);