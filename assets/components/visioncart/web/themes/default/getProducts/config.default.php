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
$params['tpl'] = '@CODE:<tr>
	<td>[[!vcGetProductImages?id=`[[+id]]`&prefix=`100_`&rel=`lightboxdemo[]`&limit=`1`]]</td>
	<td>
		<strong><a href="[[+url]]" title="[[+name]]">[[+name]]</a> ([[!vcGetProductCustomField?id=`[[+id]]`&field=`Publisher`]])</strong>
<p>[[+vc.category.name]] | [[!vcGetProductCustomField?id=`[[+id]]`&field=`Release year`]] | PEGI [[!vcGetProductCustomField?id=`[[+id]]`&field=`PEGI`]] | Professionele beoordeling: [[!vcGetProductCustomField?id=`[[+id]]`&field=`Rating`]]
		<p>[[+description:ellipsis=`250`]]</p>
		<p><a href="[[+url]]" title="Lees meer">Lees meer...</a></p>
<!--		[[!vcGetProductCustomFields?id=`[[+id]]`]]-->
	</td>
	<td>
		<p>Prijs: [[+vc.shop.config.currency]][[+price]]</p>
		<p>[[+stock:if=`[[+stock]]`:gt=`0`:then=`Op werkdagen voor 21:30 besteld, morgen in huis.`:else=`Levering 1 tot 3 werkdagen.`]]</p>
	</td>
</tr>';