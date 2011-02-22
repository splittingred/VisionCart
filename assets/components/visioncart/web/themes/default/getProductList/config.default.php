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
$params['wrapperTpl'] = '@CODE:<form name="vcListForm" action="" method="get"><table width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			[[+headers]]
		</tr>
	</thead>
	<tbody>
		[[+content]]
	</tbody>
	<tfoot>
		<tr>
			[[+footer]]
		</tr>
	</tfoot>
</table>

<input type="hidden" name="offset" value="[[+vc.list.router.query.offset]]" />
<input type="hidden" name="sort" value="asc" />
<input type="hidden" name="index" value="[[+vc.list.router.query.index]]" />
</form>';

$params['headerTpl'] = '@CODE:<table width="100%" cellspacing="0" cellpadding="0">
	<thead>
		[[+headers]]
	</thead>';
	
$params['headerTplColumn'] = '@CODE:<th><a href="[[+url]]" title="[[+title]]">[[+name]]</a></th>';

$params['rowTpl'] = '@CODE:<tr>
	[[+columns]]
</tr>';

$params['rowTplColumn'] = '@CODE:<td>[[+field]]</td>';

$params['rowLinkTplColumn'] = '@CODE:<td><a href="[[+url]]" title="[[+field]]">[[+field]]</a></td>';

$params['footerTpl'] = '@CODE:<td colspan="[[+vc.list.config.columns]]">
[[+limitbox]][[+wotox]]
Showing [[+vc.list.router.query.limit]] of [[+vc.list.pagination.items]] items (page [[+vc.list.pagination.page]] of [[+vc.list.pagination.pages]])
<input type="text" value="[[+vc.list.router.query.query]]" name="query" />
[[+pagination]]
</td>';

$params['limitTpl'] = '@CODE:<select name="limit" onchange="document.vcListForm.submit();">
	[[+options]]
</select>';

$params['limitItemTpl'] = '@CODE:<option value="[[+value]]" [[+selected:notempty=`selected="[[+selected]]"`]]>[[+value]]</option>';

$params['paginationWrapper'] = '@CODE:<div>[[+pages]]</div>';

$params['paginationTpl'] = '@CODE:<a href="[[+url]]" title="Ga naar pagina [[+page]]">[[+page]]</a>';