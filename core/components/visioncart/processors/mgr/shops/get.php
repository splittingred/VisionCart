<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$shopId = (int) $_REQUEST['id'];

$shop = $modx->getObject('vcShop', $shopId);

if ($shop == null) {
	return $modx->error->failure('Shop not found');
}

$shopObject = $shop->toArray();

return $modx->error->success('', $shopObject);