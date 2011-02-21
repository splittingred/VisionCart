<?php
/**
 * @package visioncart
 */

if (!isset($modx->visioncart) || $modx->visioncart == null) {
	$modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
    $modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/');	
}

$vc =& $modx->visioncart;
$vc->initialize('web');

return $vc->order();