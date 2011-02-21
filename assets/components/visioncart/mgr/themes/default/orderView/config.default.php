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
$params['wrapperTpl'] = '@CODE:<h2>Order</h2>
<p>
	<b>Order number</b>: [[+ordernumber]]<br />
	<b>Date</b>: [[+ordertime:date=`%m/%d/%Y`]]
</p>
<br /><br />
<table class="vc-maintable"  width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<b>Billing address</b><br />
			[[+userdata.profile.extended.VisionCart.billingaddress.fullname]]<br />
			[[+userdata.profile.extended.VisionCart.billingaddress.address]]<br />
			[[+userdata.profile.extended.VisionCart.billingaddress.zip]]<br />
			[[+userdata.profile.extended.VisionCart.billingaddress.city]]<br />
			[[+userdata.profile.extended.VisionCart.billingaddress.state]]<br />
			[[+userdata.profile.extended.VisionCart.billingaddress.country]]
		</td>
		<td>
			<b>Shipping address</b><br />
			[[+userdata.profile.extended.VisionCart.shippingaddress.fullname]]<br />
			[[+userdata.profile.extended.VisionCart.shippingaddress.address]]<br />
			[[+userdata.profile.extended.VisionCart.shippingaddress.zip]]<br />
			[[+userdata.profile.extended.VisionCart.shippingaddress.city]]<br />
			[[+userdata.profile.extended.VisionCart.shippingaddress.state]]<br />
			[[+userdata.profile.extended.VisionCart.shippingaddress.country]]
		</td>
	</tr>
</table>
<br /><br />
<table class="vc-maintable"  width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th><b>Product</b></th>
			<th><b>Quantity</b></th>
			<th><b>Price</b></th>
			<th><b>Subtotal</b></th>
		</tr>
	</thead>
	<tbody>
		[[+wrapper.products]]
		<tr>
			<td>Shipping: [[+shippingModule.name]]</td>
			<td>1</td>
			<td>[[+shippingcostsin:vcMoney]]</td>
			<td>[[+shippingcostsin:vcMoney]]</td>
		</tr>
		<tr>
			<td>Payment: [[+paymentModule.name]]</td>
			<td>1</td>
			<td>[[+paymentcostsin:vcMoney]]</td>
			<td>[[+paymentcostsin:vcMoney]]</td>
		</tr>
		<tr>
			<td style="background-color: black; color: white; font-weight: bold;" colspan="3" align="right">Total (ex):</td>
			<td style="background-color: black; color: white; font-weight: bold;">[[+totalorderamountex:vcMoney]]</td>
		</tr>
		[[+taxes]]
		<tr>
			<td style="background-color: black; color: white; font-weight: bold;" colspan="3" align="right">Total (in):</td>
			<td style="background-color: black; color: white; font-weight: bold;">[[+totalorderamountin:vcMoney]]</td>
		</tr>
	</tbody>
</table>';

$params['productRow'] = '@CODE:<tr>
	<td>[[+product.name]]</td>
	<td>[[+product.quantity]]</td>
	<td>[[+product.display.price.in:vcMoney]]</td>
	<td>[[+product.display.price.subtotal:vcMoney]]</td>
</tr>';

$params['taxRow'] = '@CODE:<tr>
<td colspan="3" align="right" style="background-color: black; color: white; font-weight: bold;">Taxes: [[+tax.name]] ([[+tax.display.percentage]])</td>
<td style="background-color: black; color: white; font-weight: bold;">[[+tax.display.amount]]</td>
</tr>'; 


