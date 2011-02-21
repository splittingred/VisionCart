<?php
/**
 * @package visioncart
 */

if (!isset($modx->visioncart) || $modx->visioncart == null) {
	$modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
    $modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/');	
}

$vc =& $modx->visioncart;

if (!empty($_REQUEST) && isset($_REQUEST['products']) && !empty($_REQUEST['products'])) {
	$modx->executeProcessor(array_merge($_REQUEST, array(
		'location' => 'web',
		'processors_path' => $vc->config['processorsPath'],
		'action' => 'basket',
		'return' => 0
	)));
}

$vc->initialize('web');
$step = (int) $vc->router['query']['step'];

if (!$step || !is_numeric($step)) {
	$step = 1;
}

return $vc->order(array(
	'step' => $step
));