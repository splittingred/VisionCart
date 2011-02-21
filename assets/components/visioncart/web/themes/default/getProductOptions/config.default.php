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
$params['tpl'] = '@CODE:<div id="vc-product-options">
[[+innerChunk]]
</div>';

$params['rowTpl'] = '@CODE:<div id="vc-product-optioncontent-[[+option.id]]">
[[+innerChunk]]
</div>';