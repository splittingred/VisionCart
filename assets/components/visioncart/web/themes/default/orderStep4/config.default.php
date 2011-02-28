<?php

$params = array();

$params['vcOrderStep4'] = '@CODE:<form action="[[+action]]" method="post">
	<h2>Payment method</h2>
	[[+content]]
	<hr />
	<input type="button" onclick="window.location=\'[[+previousStep]]\';" value="Previous" />
	<input type="submit" value="Next" />
</form>';

$params['vcPaymentRow'] = '@CODE:<tr>
	<td>[[+name]]</td>
	<td>[[+description]]</td>
	<td>[[+costs]]</td>
	<td><input type="radio" [[!+selected:if=`[[+selected]]`:is=`1`:then=`checked="checked"`]] name="vc_payment_method" value="[[+id]]" /></td>
</tr>';

$params['vcPaymentWrapper'] = '@CODE:<table width="100%">
<tr>
	<td>
		<b>Payment method</b>
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