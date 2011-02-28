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
$params['tpl'] = '@CODE:<table width="100%">
	<tr>
		<td>
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td>[[!vcGetProductImages?id=`[[+id]]`&offset=`0`&limit=`1`&prefix=`medium_`]]</td>
					<td valign="bottom">[[!vcGetProductImages?id=`[[+id]]`&prefix=`thumb_`]]</td>
				</tr>
				<tr>
					<td width="300"><strong>ID</strong></td>
					<td>[[+id]]</td>
				</tr>
				<tr>
					<td width="275"><strong>URL</strong></td>
					<td>[[+url]]</td>
				</tr>
				<tr>
					<td width="275"><strong>Naam</strong></td>
					<td>[[+name]]</td>
				</tr>
				<tr>
					<td><strong>Alias</strong></td>
					<td>[[+alias]]</td>
				</tr>
				<tr>
					<td><strong>Description</strong></td>
					<td>[[+description]]</td>
				</tr>
				<tr>
					<td><strong>Article number</strong></td>
					<td>[[+articlenumber]]</td>
				</tr>
				<tr>
					<td><strong>Price</strong></td>
					<td>[[+display.price]]</td>
				</tr>
				<tr>
					<td><strong>Weight</strong></td>
					<td>[[+weight]]</td>
				</tr>
				<tr>
					<td><strong>Shipping price</strong></td>
					<td>[[+display.shippingprice]]</td>
				</tr>
				<tr>
					<td><strong>Eigenschappen</strong></td>
					<td>[[!vcGetProductCustomFields?id=`[[+id]]`]]</td>
				</tr>
			</table>
		</td>
		<td width="250">
			<!-- <input type="button" value="Add to basket (ajax)" onclick="vc.basketAdd([[+id]], \'add\');" /> -->
			<form method="post" action="">
				<input type="hidden" name="action" value="basket" />
				<input type="hidden" name="basketAction" value="add" />
				<input type="hidden" name="product" value="[[+id]]" />
				Quantity: 
				<input type="text" name="quantity" value="1" size="4" /><br /><br />
				<input type="submit" value="Add to basket (post)" /> 
			</form>
			[[!vcProductOptions]]
		</td>
	</tr>
</table>';