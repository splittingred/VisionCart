[[!FormIt?preHooks=`vcFormItGetUser`
&validate=`shipping_fullname:required,shipping_address:required,shipping_zip:required,shipping_city:required,shipping_state:required,shipping_country:required`
&hooks=`vcFormItSaveUser`]]
<form action="[[+action:default=`[[~[[*id]]]]`]]" method="post"> 
	<table>
		<tr>
			<td colspan="2"><strong>Shipping address</strong></td>
			<td colspan="2"><strong>Billing address</strong></td>
		</tr>
		<tr>
			<td><strong>Full name</strong></td>
			<td><input type="text" name="shipping_fullname" value="[[!+fi.shipping_fullname]]" /> [[+fi.error.shipping_fullname]]</td>
			<td><strong>Full name</strong></td>
			<td><input type="text" name="billing_fullname" value="[[!+fi.billing_fullname]]" /> [[+fi.error.billing_fullname]]</td>
		</tr>
		<tr>
			<td><strong>Adres</strong></td>
			<td><input type="text" name="shipping_address" value="[[!+fi.shipping_address]]" /> [[+fi.error.shipping_address]]</td>
			<td><strong>Adres</strong></td>
			<td><input type="text" name="billing_address" value="[[!+fi.billing_address]]" /> [[+fi.error.billing_address]]</td>
		</tr>
		<tr>
			<td><strong>Zip code</strong></td>
			<td><input type="text" name="shipping_zip" value="[[!+fi.shipping_zip]]" /> [[+fi.error.shipping_zip]]</td>
			<td><strong>Zip code</strong></td>
			<td><input type="text" name="billing_zip" value="[[!+fi.billing_zip]]" /> [[+fi.error.billing_zip]]</td>
		</tr>
		<tr>
			<td><strong>City</strong></td>
			<td><input type="text" name="shipping_city" value="[[!+fi.shipping_city]]" /> [[+fi.error.shipping_city]]</td>
			<td><strong>City</strong></td>
			<td><input type="text" name="billing_city" value="[[!+fi.billing_city]]" /> [[+fi.error.billing_city]]</td>
		</tr>
		<tr>
			<td><strong>State</strong></td>
			<td><input type="text" name="shipping_state" value="[[!+fi.shipping_state]]" /> [[+fi.error.shipping_state]]</td>
			<td><strong>State</strong></td>
			<td><input type="text" name="billing_state" value="[[!+fi.billing_state]]" /> [[+fi.error.billing_state]]</td>
		</tr>
		<tr>
			<td><strong>Country</strong></td>
			<td><input type="text" name="shipping_country" value="[[!+fi.shipping_country]]" /> [[+fi.error.shipping_country]]</td>
			<td><strong>Country</strong></td>
			<td><input type="text" name="billing_country" value="[[!+fi.billing_country]]" /> [[+fi.error.billing_country]]</td>
		</tr>
		<tr>
			<td colspan="2"><strong>Billing address same as shipping address</strong></td>
			<td colspan="2"><input type="checkbox" name="billing_as_shipping" value="1" [[!+fi.billing_as_shipping:FormItIsChecked=`1`]] /> (toggle)</td>
		</tr>
		<tr>
			<td colspan="4"><input type="submit" name="vc_user_form" value="Save" />
		</tr>
	</table>
</form>