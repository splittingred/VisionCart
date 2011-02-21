	<tr>
		<td>[[!vcGetProductImages?id=`[[+id]]`&prefix=`100_`&limit=`1`]]</td>
		<td>#[[+id]]</td>
		<td>[[+name]]<br />[[+description]]</td>
		<td>[[+stock]]</td>
		<td><input id="vc-order-basket-product[[+id]]" name="products[[[+id]]]" type="text" value="[[+quantity]]" /> <span onclick="vc.orderBasketAdd([[+id]], 'add', 'vc-order-basket-product[[+id]]');">Add</span></td>
		<td>[[+price:vcCurrency]]</td>
		<td>[[+total:vcCurrency]]</td>
	</tr>