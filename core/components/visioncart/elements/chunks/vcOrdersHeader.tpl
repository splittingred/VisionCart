<html>
<head>
	<title>Print Order</title>
	<link type="text/css" rel="stylesheet" href="[[+assetsRoot]]/mgr/css/style.css" media="all">
</head>
<body>
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
						 		Date: [[+ordertime]]
							</td>
						</tr>
						<tr>
						 	<td>
						 		Order#: [[+ordernumber]]
						 	</td>
						</tr>
						<tr>
						 	<td>
						 		Customer ID: [[+userid]]
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
						 		Name: [[+userdata.profile.fullname]]
						 	</td>
						</tr>
						<tr>
							 <td>
							 	Company name: [[+userdata.profile.extended.VisionCart.shippingAddress.companyname]]
							 </td>
						</tr>
						<tr>
							<td>
							 	Adres: [[+userdata.profile.extended.VisionCart.shippingAddress.street]]
							</td>
						</tr>
						<tr>
							<td>
						 		Zip: [[+userdata.profile.extended.VisionCart.shippingAddress.zip]]
							</td>
						</tr>
						<tr>
							<td>
						 		Phone: [[+userdata.profile.phone]]
							</td>
						</tr>
						<tr>
							<td>
						 		E-mail [[+userdata.profile.email]]
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