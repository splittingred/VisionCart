<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;

$processor = $modx->runProcessor('basket', array(), array(
	'location' => 'web',
	'processors_path' => $vc->config['processorsPath'],
	'return' => 'tpl',
	'requestURL' => (isset($_REQUEST['method'])) ? $_REQUEST['method'] : $_SERVER['REQUEST_URI']
));

return $processor->getResponse();