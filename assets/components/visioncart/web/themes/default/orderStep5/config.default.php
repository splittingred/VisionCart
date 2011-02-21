<?php

$params = array();

$params['vcOrderFinalBasketRow'] = '@CODE:<tr>
	<td>[[+product.name]]</td>
	<td>[[+display.pricein]]</td>
	<td>[[+product.quantity]]</td>
	<td>[[+display.subtotal]]</td>
</tr>';

$params['vcOrderFinalBasketWrapper'] = '@CODE:<table width="100%">
	<tr>
		<td><b>Product</b></td>
		<td><b>Price</b></td>
		<td><b>Quantity</b></td>
		<td><b>Sub total</b></td>
	</tr>
		[[+content]]
	<tr> 
		<td><b>Shipping:</b> [[+shippingModule.name]]</td>
		<td>[[+order.shippingcostsin:vcMoney]]</td>
		<td>1</td>
		<td>[[+order.shippingcostsin:vcMoney]]</td>
	</tr>
	<tr>
		<td><b>Payment:</b> [[+paymentModule.name]]</td>
		<td>[[+order.paymentcostsin:vcMoney]]</td>
		<td>1</td>
		<td>[[+order.paymentcostsin:vcMoney]]</td>
	</tr>
	<tr>
		<td style="background-color: black; color: white; font-weight: bold;" colspan="3" align="right">Total (ex)</td>
		<td style="background-color: black; color: white; font-weight: bold;" >[[+order.totalorderamountex:vcMoney]]</td>
	</tr>
		[[+taxes]]
	<tr>
		<td style="background-color: black; color: white; font-weight: bold;" colspan="3" align="right">Total (in)</td>
		<td style="background-color: black; color: white; font-weight: bold;" >[[+order.totalorderamountin:vcMoney]]</td>
	</tr>
</table>';

$params['vcOrderFinalTaxRow'] = '@CODE:<tr>
	<td colspan="3" align="right" style="background-color: black; color: white; font-weight: bold;">Taxes: [[+tax.name]] ([[+tax.display.percentage]])</td>
	<td style="background-color: black; color: white; font-weight: bold;">[[+tax.display.amount]]</td>
</tr>';

$params['vcOrderStep5'] = '@CODE:<form action="[[+action]]" method="post">
	<h2>Order overview</h2>
	<table width="100%">
		<tr>
			<td>
			<b>Shipping address</b><hr />
			[[+shippingAddress.fullname]]<br />
			[[+shippingAddress.address]]<br />
			[[+shippingAddress.zip]] [[+shippingAddress.city]]<br />
			[[+shippingAddress.state]]<br />
			[[+shippingAddress.country]]
			</td>
			<td>
			<b>Billing address</b><hr />
			[[+billingAddress.fullname]]<br />
			[[+billingAddress.address]]<br />
			[[+billingAddress.zip]] [[+shippingAddress.city]]<br />
			[[+billingAddress.state]]<br />
			[[+billingAddress.country]]
			</td>
		</tr>
	</table>
	<br /><br />
	[[+basket]]
	<hr />
	<input type="button" onclick="window.location=\'[[+previousStep]]\';" value="Previous" />
	<input type="hidden" name="vc_order_confirm" value="1" />
	<input type="submit" value="Confirm" onclick="return confirm(\'Are you sure you want to place this order?\');" />
</form>';