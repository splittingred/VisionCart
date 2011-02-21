<?php
/**
 * Loads the header for mgr pages.
 *
 * @package visioncart
 * @subpackage controllers
 */

$add = '';
if (isset($_REQUEST['shopid'])) {
	$add = '?shopid='.(int)$_REQUEST['shopid'];	
}

$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/extensions.js');
$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/visioncart.js');
//$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/config.php'.$add);

$config = '<script type="text/javascript">'."\n";
$config .= 'vcCore.config.connectorUrl = \''.$modx->visioncart->config['connectorUrl'].'\';'."\n";
$config .= 'vcCore.config.assetsUrl = \''.$modx->visioncart->config['assetsUrl'].'\';'."\n";
/*foreach($modx->visioncart->config as $key => $value) {
	$config .= 'vcCore.config.'.$key.' = \''.$value.'\';'."\n";	
}*/

if (isset($_REQUEST['shopid'])) {
	$shop = $modx->getObject('vcShop', (int) $_REQUEST['shopid']);
	$shopConfig = json_encode($shop->get('config'));
	if ($shop != null) {
		$config .= 'vcCore.config.currentShop = \''.str_replace("'", "\'", $shop->get('name')).'\';'."\n";
		$config .= 'vcCore.config.shopConfig = '.$shopConfig.';';
	}
}
$config .= '</script>';
$modx->regClientStartupHTMLBlock($config);

$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/stores.js');
$modx->regClientCss($visionCart->config['assetsUrl'].'mgr/css/style.css');

switch($_REQUEST['action']) {
	default:
		$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/page_shops.js');
		break;
	case 'modules':
		$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/page_modules.js');
		break;
	case 'categories':
		$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/page_categories.js');
		break;	
	case 'options':
		$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/page_options.js');
		break;
	case 'products':
		$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/page_products.js');
		break;
	case 'product':
		$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/page_product.js');
		break;
	case 'orders':
		$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/page_orders.js');
		break;		
	case 'vieworder':
		$modx->regClientStartupScript($visionCart->config['assetsUrl'].'mgr/js/page_vieworder.js');
		break;
}



return '';