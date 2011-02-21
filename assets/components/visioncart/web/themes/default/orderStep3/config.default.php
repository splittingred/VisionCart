<?php

$params = array();

$params['vcOrderStep3'] = '@CODE:<form action="[[+action]]" method="post">
	<h2>Shipping method</h2>
	[[+content]]
	<hr />
	<input type="button" onclick="window.location=\'[[+previousStep]]\';" value="Previous" />
	<input type="submit" value="Next" />
</form> ';

$params['vcShippingRow'] = '@CODE:<tr>
<td>[[+name]]</td>
<td>[[+description]]</td>
<td>[[+costs]]</td>
<td><input type="radio" name="vc_shipping_method" value="[[+id]]" /></td>
</tr>';

$params['vcShippingWrapper'] = '@CODE:<table width="100%">
<tr>
	<td>
		<b>Shipping method</b>
	</td>
	<td>
		<b>Description</b>
	</td>
	<td>
		<b>Costs</b>
	</td>
	<td>
	
	</td>
</tr>
[[+content]]
</table>';