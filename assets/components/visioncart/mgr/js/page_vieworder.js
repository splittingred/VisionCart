var vcPageOrder = Ext.extend(Ext.Panel, {
	initComponent: function() {
		
		//Item grid
		this.itemGrid = new Ext.grid.GridPanel({
			store: vcCore.stores.orderItems,
			autoHeight: true,
			loadMask: true,
			viewConfig: {
				forceFit: true,
				enableRowBody: true,
				autoFill: true,
				deferEmptyText: false,
				showPreview: true,
				scrollOffset: 0,
				emptyText: _('ext_emptymsg')
			},
		    autoExpandColumn: 'item-name',
		    columns: [
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'articlenumber',
		            id: 'item-articlenumber',
		            header: 'Articlenumber'
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'name',
		            id: 'item-name',
		            header: 'Item name'
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'quantity',
		            id: 'item-quantity',
		            header: 'Quantity',
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'price',
		            id: 'item-price',
		            header: 'Price',
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'totalprice',
		            id: 'item-totalprice',
		            header: 'Totalprice',
		        }
		    ]/*,
		    listeners: {
		    	rowContextMenu: {
		    		scope: this,
		    		fn: function(grid, rowIndex, event) {
		    			// Set the database ID in the menu's base params so we can access it when an action is performed
		    			this.rowMenu.baseParams.rowId = vcCore.stores.orders.getAt(rowIndex).get('id');
		    			//this.rowMenu.baseParams.status = vcCore.stores.orders.getAt(rowIndex).get('status');
		    			this.rowMenu.showAt(event.xy);
		    			event.stopEvent();
		    		}
		    	}
		    }*/
		});
		
	this.itemGrid.getStore().load();
	
		// The mainpanel always has to be in the "this.mainPanel" variable
		this.mainPanel = new Ext.Panel({
			renderTo: 'visioncart-content',
			padding: 15,
			layout: 'form',
			items: [
				{
					xtype: 'toolbar',
					style: {
						border: 'none',
						backgroundColor: 'transparent'
					},
					items: [
						'->',
						{
							text: 'Export PDF',
							handler: function() {
								window.location = '?a='+vcCore.getUrlVar('a')+'&action=printorder&shopid='+vcCore.getUrlVar('shopid')+'&id='+vcCore.getUrlVar('id');	
							}
						},
						{
							text: 'Back to orderlist',
							scope: this,
							handler: function() {
								window.location = '?a='+vcCore.getUrlVar('a')+'&action=orders&shopid='+vcCore.getUrlVar('shopid');		
							}
						}
					]
				},
				{
					layout: 'column',
					frameBorder: false,
					unstyled: true,
					defaults: {
						frameBorder: false,
						unstyled: true
					},
					items: [
						{
							defaults: {
								style: {
									paddingTop: '3px'
								}
							},
							layout: 'form',
							columnWidth: .5,

							items: [
								{
									fieldLabel: 'Ordernumber',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.ordernumber.toString()
								},
								{
									fieldLabel: 'Billing adress',
									unstyled: true,
									labelStyle: 'font-weight: bold;',
									frameBorder: false,
								},
								{
									fieldLabel: 'Name',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.billingAddress.fullname
								},
								{
									fieldLabel: 'Address',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.billingAddress.street
								},
								{
									fieldLabel: 'Zip',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.billingAddress.zip
								},
								{
									fieldLabel: 'City',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.billingAddress.city
								},
								{
									fieldLabel: 'Country',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.billingAddress.country
								},
								{
									fieldLabel: 'Comments',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.comment
								}
							]
						},
						{		
							layout: 'form',
							columnWidth: .5,
							defaults: {
								style: {
									paddingTop: '3px'
								}
							},
							items: [
								{
									fieldLabel: '',
									unstyled: true,
									frameBorder: false,
									html: '&nbsp;',
									style: {
										padding: '5px'
									}
								},
								{
									fieldLabel: 'Shipping adress',
									unstyled: true,
									labelStyle: 'font-weight: bold; width:150px;',
									frameBorder: false,
								},
								{
									fieldLabel: 'Name',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.shippingAddress.fullname
								},
								{
									fieldLabel: 'Address',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.shippingAddress.street
								},
								{
									fieldLabel: 'Zip',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.shippingAddress.zip
								},
								{
									fieldLabel: 'City',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.shippingAddress.city
								},
								{
									fieldLabel: 'Country',
									unstyled: true,
									frameBorder: false,
									html: vcOrder.userdata.profile.extended.VisionCart.shippingAddress.country
								}
							]
						}
					]	
				},
				this.itemGrid,
				{	
					layout: 'form',
					items: [
						{
							fieldLabel: 'Totals',
							unstyled: true,
							labelStyle: 'font-weight: bold; width:150px;',
							frameBorder: false,
						},
						{
							fieldLabel: 'Total excl.',
							unstyled: true,
							frameBorder: false,
							html: vcOrder.totalamountex.toString()
						},
						{
							fieldLabel: 'Tax.',
							unstyled: true,
							frameBorder: false,
							html: 'needs the tax calculation'
						},
						{
							fieldLabel: 'Total incl.',
							unstyled: true,
							frameBorder: false,
							html: vcOrder.totalamountin.toString()
						},
						{
							fieldLabel: 'Shipping',
							unstyled: true,
							frameBorder: false,
							html: vcOrder.shippingcosts.toString()
						},
						{
							fieldLabel: 'Total',
							unstyled: true,
							frameBorder: false,
							html: 'needs the total calculation'
						}
					]
				}
			]
		});
	}
	
});

Ext.onReady(function() {
	// Set page title and load main panel
	vcCore.setTitle(vcCore.config.currentShop+' - Order Items');
	
	// this makes the main class accessible through vcCore.pageClass and the panel through vcCore.pagePanel
	vcCore.loadPanel(vcPageOrder); 
});