<form action="" method="post"><table width="100%">
	<tr>
		<td>&nbsp;</td>
		<td>ArticleID</td>
		<td>Description</td>
		<td>&nbsp;</td>
		<td>Quantity</td>
		<td>Price</td>
		<td>Total</td>
	</tr>
	[[+vc.content]]
	<tr>
		<td colspan="6">&nbsp;</td>
		<td><input type="submit" value="Update" />
	</tr>
</table>

<input type="hidden" name="step" value="1" />
<input type="hidden" name="method" value="update" />
</form>