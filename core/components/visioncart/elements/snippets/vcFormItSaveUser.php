<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;

$processor = $modx->runProcessor('user/update', array(), array(
	'location' => 'web',
	'processors_path' => $vc->config['processorsPath'],
	'hook' => &$hook,
	'scriptProperties' => &$scriptProperties
));

return $processor->getResponse();