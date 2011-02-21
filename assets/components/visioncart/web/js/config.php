<?php
/**
 * VisionCart Config
 *
 * @package visioncart
 */

require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';

$_REQUEST['ctx'] = 'web';
$_REQUEST['HTTP_MODAUTH'] = $site_id;	

require_once MODX_CONNECTORS_PATH.'index.php';

$corePath = $modx->getOption('core_path').'components/visioncart/';
require_once $corePath.'model/visioncart/visioncart.class.php';
$modx->visioncart = new VisionCart($modx);

header('Content-type: text/javascript');

?>
var visionCartConfig = {
	connector: '<?php echo $modx->visioncart->config['connectorUrl']; ?>'
};