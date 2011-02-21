<?php
/**
 * @package visioncart
 * @subpackage controllers
 */
require_once dirname(dirname(__FILE__)).'/model/visioncart/visioncart.class.php';
$visionCart = new VisionCart($modx);

// Create tables
/*$modx->getCollection('vcShop');
$modx->getCollection('vcCategory');
$modx->getCollection('vcProductCategory');
$modx->getCollection('vcProduct');
$modx->getCollection('vcOption');
$modx->getCollection('vcOptionValue');
$modx->getCollection('vcProductOption');
$modx->getCollection('vcModule');
$modx->getCollection('vcOrder');*/

return $visionCart->initialize('mgr');