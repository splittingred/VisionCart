<?php
/**
 * @package visioncart
 */

if (!isset($modx->visioncart)) {
    $modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
    $modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/');
}

$vc =& $modx->visioncart;

return $modx->executeProcessor(array(
	'location' => 'web',
	'processors_path' => $vc->config['processorsPath'],
	'action' => 'user/update',
	'hook' => &$hook,
	'scriptProperties' => &$scriptProperties
));