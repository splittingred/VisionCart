<?php

$params = array();

$params['vcOrderBasketEmpty'] = '@CODE:Your shopping cart is still empty, why would you want to check out? ;-)';

$params['vcBasketRow'] = '@CODE:<tr>
	<td>[[!vcGetProductImages?id=`[[+id]]`&prefix=`thumb_`&limit=`1`]]</td>
	<td>#[[+articlenumber]]</td>
	<td>[[+name]]<br />[[+description]]</td>
	<td><input id="vc-order-basket-product[[+id]]" name="products[[[+id]]]" type="text" value="[[+quantity]]" /></td>
	<td>[[+display.price.in:vcMoney]]</td>
	<td>[[+display.price.subtotal:vcMoney]]</td>
</tr> ';

$params['vcBasketWrapper'] = '@CODE:<form action="" method="post"><table width="100%">
	<tr>
		<td>&nbsp;</td>
		<td>Article number</td>
		<td>Description</td>
		<td>Quantity</td>
		<td>Price</td>
		<td>Total</td>
	</tr> 
	[[+content]]
	<tr>
		<td colspan="5">&nbsp;</td>
		<td><input type="submit" value="Update" />
	</tr>
</table>

<input type="hidden" name="step" value="1" />
<input type="hidden" name="basketAction" value="update" />
</form>
<hr />
[[!+orderAmountMet:if=`[[+orderAmountMet]]`:is=`0`:then=`<b>You have not met the minimum order amount.</b>`:else=`
<form action="[[+action]]" method="post">
<input type="submit" value="Next" />
</form>`]]';

$params['vcOrderStep1'] = '@CODE:<h2>Basket</h2>
[[+content]]';