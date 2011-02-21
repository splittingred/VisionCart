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

$params['rowTpl'] = '@CODE:<tr><td><strong>[[+field]]</strong></td><td>[[+value]]</td></tr>';

$params['wrapperTpl'] = '@CODE:<table width="100%" cellpadding="0" cellspacing="0">
	[[+rows]]
</table>';