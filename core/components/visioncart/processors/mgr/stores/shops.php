<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$shops = $modx->getCollection('vcShop');
        
$list = array();
foreach ($shops as $shop) {
	$shopArray = $shop->toArray();
    $list[] = $shopArray;
}

return $this->outputArray($list, sizeof($list));