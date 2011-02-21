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
$params['wrapperTpl'] = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0" exportedBy="VisionCart" timestamp="[[+timestamp]]" base="[[+base]]">
<channel>
[[+content]]
</channel>
</rss>
';

$params['itemTpl'] = '<[[+key]]>
[[+content]]
</[[+key]]>

';
$params['dataTpl'] = '<[[+key]]>[[+value]]</[[+key]]>
';