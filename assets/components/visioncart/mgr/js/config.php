<?php
/**
 * VisionCart Config
 *
 * @package visioncart
 */
require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$corePath = $modx->getOption('core_path').'components/visioncart/';
require_once $corePath.'model/visioncart/visioncart.class.php';
$modx->visioncart = new VisionCart($modx);

$modx->lexicon->load('visioncart:default');

header('Content-type: text/javascript');
foreach($modx->visioncart->config as $key => $value) {
	echo 'vcCore.config.'.$key.' = \''.$value.'\';'."\n";	
}

if (isset($_REQUEST['shopid'])) {
	$shop = $modx->getObject('vcShop', (int) $_REQUEST['shopid']);
	$shopConfig = json_encode($shop->get('config'));
	if ($shop != null) {
		echo 'vcCore.config.currentShop = \''.str_replace("'", "\'", $shop->get('name')).'\';'."\n";
		echo 'vcCore.config.shopConfig = '.$shopConfig.';';
	}
}