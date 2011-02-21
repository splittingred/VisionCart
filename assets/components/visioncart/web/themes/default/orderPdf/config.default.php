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
$params['inlineStyles'] = '@page {
	font-family: helvetica;
	margin: 10px;
}

table {
	border-collapse: collapse;
}

td {
	font-size: 11px;
}';

$params['orderWrapper'] = '@CODE:
<table width="100%">
	<tr>
		<td width="350">
			<img src="'.$this->modx->getOption('base_path').'assets/components/visioncart/web/themes/default/orderPdf/pdflogo.jpg" />
		</td>
		<td valign="middle">
			<span style="font-size: 40px; font-weight: bold;">Invoice</span><br />
			Order number: [[+order.ordernumber]]<br />
			Date: [[+order.ordertime:date=`%m/%d/%Y`]]
		</td>
	</tr>
</table>
<br /><br />
<table width="100%" cellpadding="0" cellspacing="0">
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
			<b>VisionCart</b><br />
			Fake address<br />
			Fake street 123<br />
			City, Zip code<br />
			+12 123456 789<br />
			http://www.visioncart.net
		</td>
	</tr>
</table>
<br /><br />
<table width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<td><b>Product</b></td>
			<td><b>Quantity</b></td>
			<td><b>Price</b></td>
			<td><b>Subtotal</b></td>
		</tr>
	</thead>
	<tbody>
		[[+products]]
		<tr>
			<td>Shipping: [[+shippingModule.name]]</td>
			<td>1</td>
			<td>[[+order.shippingcostsin:vcMoney=`shopId==[[+order.shopid]]`]]</td>
			<td>[[+order.shippingcostsin:vcMoney=`shopId==[[+order.shopid]]`]]</td>
		</tr>
		<tr>
			<td>Payment: [[+paymentModule.name]]</td>
			<td>1</td>
			<td>[[+order.paymentcostsin:vcMoney=`shopId==[[+order.shopid]]`]]</td>
			<td>[[+order.paymentcostsin:vcMoney=`shopId==[[+order.shopid]]`]]</td>
		</tr>
		<tr>
			<td style="background-color: black; color: white; font-weight: bold;" colspan="3" align="right">Total (ex):</td>
			<td style="background-color: black; color: white; font-weight: bold;">[[+order.totalorderamountex:vcMoney=`shopId==[[+order.shopid]]`]]</td>
		</tr>
		[[+taxes]]
		<tr>
			<td style="background-color: black; color: white; font-weight: bold;" colspan="3" align="right">Total (in):</td>
			<td style="background-color: black; color: white; font-weight: bold;">[[+order.totalorderamountin:vcMoney=`shopId==[[+order.shopid]]`]]</td>
		</tr>
	</tbody>
</table>';

$params['taxRowTpl'] = '@CODE:<tr>
<td colspan="3" align="right" style="background-color: black; color: white; font-weight: bold;">Taxes: [[+tax.name]] ([[+tax.display.percentage]]):</td>
<td style="background-color: black; color: white; font-weight: bold;">[[+tax.display.amount]]</td>
</tr>';

$params['productRowTpl'] = '@CODE:<tr>
	<td>[[+product.name]]</td>
	<td>[[+product.quantity]]</td>
	<td>[[+product.display.pricein]]</td>
	<td>[[+product.display.subtotal]]</td>
</tr>';  