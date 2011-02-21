<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;

return $modx->executeProcessor(array(
	'location' => 'web',
	'action' => 'basket',
	'processors_path' => $vc->config['processorsPath'],
	'return' => 'tpl',
	'requestURL' => (isset($_REQUEST['method'])) ? $_REQUEST['method'] : $_SERVER['REQUEST_URI']
));