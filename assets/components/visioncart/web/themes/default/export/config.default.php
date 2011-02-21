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
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<title>[[+title:stripTags]]</title>
		<description>[[+description:stripTags]]</description>
		<link>[[+link]]</link>
		<atom:link href="[[+link]]" rel="self" type="application/rss+xml" />
[[+content]]
	</channel>
</rss>';

$params['itemTpl'] = '		<item>
			<title>[[+name:stripTags]]</title>
			<link>[[+link]]</link>
			<description>[[+description:stripTags]]</description>
			<guid>[[+link]]#[[+id]]</guid>
		</item>
';