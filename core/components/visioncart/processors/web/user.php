<?php

if (!isset($modx->visioncart) || $modx->visioncart == null) {
	$modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
    $modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/');	
}

$vc =& $modx->visioncart;
$vc->initialize('web', array(
	'requestURL' => $options['requestURL'],
	'method' => 'processor'
));
$vc->getPlaceholders();

$methods = array('add', 'update', 'subtract', 'empty', 'remove');

$options = array_merge($options, $scriptProperties);

print_r($options);