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
$params['outerTpl'] = '@CODE:
<div>
	<h3>Cart</h3>
	<ul>[[+innerChunk]]</ul>
	<hr />
	You have [[+items]] product(s) in your cart.
	<br /><br />
	Total: [[+order.totalproductamountin:vcMoney]]
	<br />
	<form action="" method="post" style="float: left;">
		<input type="hidden" name="action" value="basket" />
		<input type="hidden" name="basketAction" value="empty" />
		<input type="submit" value="Empty cart" onclick="return confirm(\'Are you sure you want to empty the cart?\');" />
	</form>
	<form action="" method="post" style="float: left;">
		<input type="hidden" name="action" value="basket" />
		<input type="hidden" name="basketAction" value="checkout" />
		<input type="submit" value="Checkout &raquo;" />
	</form>
	
	
</div>';
$params['rowTpl'] = '@CODE:<li>[[+quantity]]x [[+name]] [[+display.price.in:vcMoney]]</li>';
$params['emptyBasketTpl'] = '@CODE:Your shopping cart is empty.';