<?php

if (is_file(dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php')) {
	require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
} else {
	require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.core.php';
}
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';

if (isset($_REQUEST['ctx']) && $_REQUEST['ctx'] == 'web') {
	$_REQUEST['HTTP_MODAUTH'] = $site_id;	
}

require_once MODX_CONNECTORS_PATH.'index.php';

$corePath = $modx->getOption('visioncart.core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH));

$modx->addPackage('visioncart', $corePath.'components/visioncart/model/');
$modx->visioncart = $modx->getService('visioncart', 'VisionCart', $corePath.'components/visioncart/model/visioncart/', array( 
	'method' => 'ajax',
	'initialize' => 'plugin',
	'context' => (string) $modx->context->get('key'),
	'event' => (string) $modx->event->name,
	'requestURL' => (string) $_REQUEST['requestURL']
));

$modx->lexicon->load('visioncart:default');

$modx->request->handleRequest(array(
    'processors_path' => $modx->getOption('processorsPath', $modx->visioncart->config, $corePath.'processors'),
    'location' => (isset($_POST['ctx']) && $_POST['ctx'] != '') ? $_POST['ctx'] : ''
));