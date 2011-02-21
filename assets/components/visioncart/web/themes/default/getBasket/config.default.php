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
	<ul>[[+vc.content]]</ul>
	<hr />
	You have [[+vc.basket.count]] products in your cart.
	<br /><br />
	Total: [[+vc.order.totalproductamountin:vcMoney]]
	<br />
	<form action="" method="post">
		<input type="hidden" name="action" value="basket" />
		<input type="hidden" name="basketAction" value="empty" />
		<input type="submit" value="Empty cart" />
		<input type="button" value="Checkout" onclick="window.location=\'[[+checkOutUrl]]\';" />
	</form>
</div>';
$params['rowTpl'] = '@CODE:<li>[[+quantity]]x [[+name]] [[+display.price.in:vcMoney]]</li>';
$params['emptyBasketTpl'] = '@CODE:Your shopping cart is empty.';