<?php
/**
 * exportProducts default configuration
 * This file represents the default configuration style for the exportProducts layout.
 * @notice Globalised variables from the main included functions are $shopId, $topic, $key and $config
 * @notice Use the $config = array(); to block duplicate output or adding to previously loaded arrays 
 * 
 * @package visioncart
 * 
 */

$params = array();
$params['wrapperTpl'] = '@CODE:<p>Order number <a href="[[+link]]">[[+ordernumber]]</a> | [[+ordertime:date=`%m/%d/%Y`]] | [[+display.products]] products, total [[+totalorderamountin:vcMoney]]</p>';