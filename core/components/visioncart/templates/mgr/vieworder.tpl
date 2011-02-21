{if $print}
	{include file= $tplPath}
{/if}

<table  class="vc-maintable">
	<tbody>
		<tr>
			<td width="50%">
				<img src="http://www.1-vision.nl/gfx/logo.gif" alt="" />
			</td>
			<td>
				<table>
					<tbody>
						<tr>
							<td>
						 		Date: {$order.ordertime}
							</td>
						</tr>
						<tr>
						 	<td>
						 		Order#: {$order.ordernumber}
						 	</td>
						</tr>
						<tr>
						 	<td>
						 		Customer ID: {$order.userid}
						 	</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<table>
					<thead>
						<tr>
							<th class="vc-th">
								Vendor
							</th>
						</tr>	
					</thead>
					<tbody>
						<tr>
							 <td>
							 	Test Toko
							 </td>
						</tr>
						<tr>
							<td>
							 	Test Adres
							</td>
						</tr>
						<tr>
							<td>
						 		TestZip
							</td>
						</tr>
						<tr>
							<td>
						 		1234567890
							</td>
						</tr>
						<tr>
							<td>
						 		modx@1-vision.nl
						 	</td>
						</tr>
					</tbody>
				</table>
			</td>
			<td>
				<table>
					<thead>
						<tr>
							<th class="vc-th">
								Ship to
							</th>
						</tr>	
					</thead>
					<tbody>
						<tr>
							<td>
						 		Name: {$order.userdata.profile.fullname}
						 	</td>
						</tr>
						<tr>
							 <td>
							 	Company name: {$order.userdata.profile.extended.VisionCart.shippingAddress.companyname}
							 </td>
						</tr>
						<tr>
							<td>
							 	Adres: {$order.userdata.profile.extended.VisionCart.shippingAddress.street}
							</td>
						</tr>
						<tr>
							<td>
						 		Zip: {$order.userdata.profile.extended.VisionCart.shippingAddress.zip}
							</td>
						</tr>
						<tr>
							<td>
						 		Phone: {$order.userdata.profile.phone}
							</td>
						</tr>
						<tr>
							<td>
						 		E-mail {$order.userdata.profile.email}
						 	</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="vc-productstable">
					<thead>
						<tr>
							<th class="vc-th" width="15%">
								Product number
							</th>
							<th class="vc-th">
								Product name
							</th>
							<th width="5%" class="vc-th">
								Quantity
							</th>
							<th class="vc-th">
								Unit price
							</th>
							<th class="vc-th">
								Total
							</th>
						</tr>	
					</thead>
					<tbody>
						{foreach from=$order.basket item="item"}
							<tr>
								<td>
									{$item.articlenumber}
								</td>
								<td>
									{$item.name}
								</td>
								<td class="vc-alignright">
									{$item.quantity}
								</td>
								<td class="vc-alignright">
									{$currency} {$item.price}
								</td>
								<td class="vc-alignright">
									{$currency} {$item.totalprice}
								</td>
							</tr>
						{/foreach}
						<tr>
							<td class="vc-notes" colspan="3">
								{$notes}
							</td>
							<td class="vc-totals vc-nopadding vc-alignright" colspan="2">
								<table>
									<tbody>
										<tr>
											<td>
												Total ex.: 
											</td>
											<td class="vc-alignright">
												{$currency} {$order.totalamountex|number_format:2:".":""}
											</td>
										</tr>
										<tr>
											<td>
												Tax: 
											</td>
											<td class="vc-alignright">
												{$currency} {$order.tax|number_format:2:".":""}
											</td>
										</tr>
										<tr>
											<td>
												Total incl.: 
											</td>
											<td class="vc-alignright">
												{$currency} {$order.totalamountin|number_format:2:".":""}
											</td>
										</tr>
										<tr>
											<td>
												Shipping: 
											</td>
											<td class="vc-alignright">
												{$currency} {$order.shipping|number_format:2:".":""}
											</td>
										</tr>
										<tr>
											<td>
											</td>
										</tr>
										<tr>
											<td>
												total: 
											</td>
											<td class="vc-alignright">
												{$currency} {$order.totalamount|number_format:2:".":""}
											</td>
										</tr>
									</tbody>
								</table>	
							</td>
						</tr>
				    </tbody>
				</table>
			</td>
		</tr>		
    </tbody>
</table>
{if !$print}
<script type="text/javascript">
{literal}
	Ext.onReady(function() {
		new Ext.Toolbar({
			defaults: {
				style: {
					marginLeft: '5px',
					marginRight: '5px'	
				}
			},
			items: [
				{
					text: 'Print',
					handler: function() {
						window.location = '{/literal}{$printlink}{literal}';
					}
				},
				{
					text: 'Back to orderlist',
					scope: this,
					handler: function() {
						window.location = '?a='+vcCore.getUrlVar('a')+'&action=orders&shopid={/literal}{$order.shopid}{literal}';		
					}
				}
			],
			id: 'modx-action-buttons',
			renderTo: Ext.fly('modAB')
		});
	});
{/literal}
</script>
{/if}
{if $print}
 <script type="text/javascript">window.print();</script>
	</body>
</html>
{/if}
