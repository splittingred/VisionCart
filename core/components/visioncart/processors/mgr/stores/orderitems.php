<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$orderId = (int) $_REQUEST['orderId'];

$order = $modx->getObject('vcOrder', array('id' => $orderId));


return $this->outputArray($order->get('basket'), sizeof($order->get('basket')));