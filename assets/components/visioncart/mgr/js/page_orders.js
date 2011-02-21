var vcPageOrders = Ext.extend(Ext.Panel, {
	initComponent: function() {
		// Standard we want to get status 1
		vcCore.stores.orders.baseParams.status = 1;
		
		// Rightclick mouse menu for the grid
		this.rowMenu = new Ext.menu.Menu({
			baseParams: {
				rowId: 0,
				status: 0
			},
			items: [
				{
					text: 'Status',
					menu: [
						{
							text: 'New',
						    listeners: {
							   click: {
									scope: this,
									fn: function() {
										this.setOrderStatus(1);
									}	
								}	
							}
						    
						},
						{
							text: 'Confirmed',
						    listeners: {
							   click: {
									scope: this,
									fn: function() {
										this.setOrderStatus(2);
									}	
								}	
							}	
						},
						{
							text: 'Paid',
						    listeners: {
							   click: {
									scope: this,
									fn: function() {
										this.setOrderStatus(3);
									}	
								}	
							}	
						},
						{
							text: 'Shipped / Handled',
						    listeners: {
							   click: {
									scope: this,
									fn: function() {
										this.setOrderStatus(4);
									}	
								}	
							}	
						}
					]
				},
				{
					text: 'Show order',
					listeners: {
					   click: {
							scope: this,
							fn: function() {
								window.location = '?a='+vcCore.getUrlVar('a')+'&action=vieworder&id='+this.rowMenu.baseParams.rowId+'&shopid='+vcCore.getUrlVar('shopid');
							}	
						}	
					}
				},
				{
					text: 'Export PDF',
					listeners: {
					   click: {
							scope: this,
							fn: function() {
								window.location = '?a='+vcCore.getUrlVar('a')+'&action=printorder&id='+this.rowMenu.baseParams.rowId+'&shopid='+vcCore.getUrlVar('shopid');
							}	
						}	
					}
				}
						
			]
		});
		
		//Order grid
		this.orderGrid = new Ext.grid.GridPanel({
			store: vcCore.stores.orders,
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
			bbar: new Ext.PagingToolbar({
				store: vcCore.stores.orders,
				displayInfo: true,
				pageSize: 25,
				perpendButtons: true
			}),
			tbar: [
				'->',
				{
					xtype: 'textfield',
					id: 'search-value'	
				},
				{
					xtype: 'button',
					text: 'Search',
					scope: this,
					handler: function() {
						this.orderGrid.getStore().baseParams.search = Ext.getCmp('search-value').getValue();
						this.orderGrid.getStore().load();
					}	
				}
			],
		    autoExpandColumn: 'order-userdata',
		    columns: [
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'ordernumber',
		            id: 'order-ordernumber',
		            header: 'Ordernumber',
		            sortable: true
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'fullname',
		            id: 'order-fullname',
		            header: 'Fullname',
		            sortable: true
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'totalweight',
		            id: 'order-totalweight',
		            header: 'Total weight',
		            sortable: true
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'totalorderamountex',
		            id: 'order-totalamountex',
		            header: 'Total amount ex.',
		            renderer: function(value) {
		            	return vcCore.config.shopConfig.currency+' '+value;
		            },
		            sortable: true
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'totalorderamountin',
		            id: 'order-totalamountin',
		            header: 'Total amount in.',
		            renderer: function(value) {
		            	return vcCore.config.shopConfig.currency+' '+value;
		            },
		            sortable: true
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'paidamount',
		            header: 'Paid amount',
		            renderer: function(value) {
		            	return vcCore.config.shopConfig.currency+' '+value;
		            },
		            sortable: true
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'ordertime',
		            id: 'order-ordertime',
		            header: 'Orderdate',
		            sortable: true
		        }
		    ],
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
		    }
		});
		
		this.orderGrid.getStore().load();

		// tabpanel that holds all grids
		this.tabs = new Ext.TabPanel({
		    activeTab: 0,
			plain: true,
			id: 'vc_configuration_tab',
			deferredRender: false,
			padding: 10,
			activeTab: 0,
			autoHeight: true,
			defaults: {
				autoHeight: true
			},
		    items: [
			    {
			    	title: 'New',
			    	id: 'order-status-1',
			    	items: [
			    		
			    	],
			    	listeners: {
			    		activate: {
			    			scope: this,
			    			fn: function() {
				    			this.showOrderStatus(1);
			    			}	
			    		}	
			    	}
			    },
			    {
			    	title: 'Confirmed',
			    	id: 'order-status-2',
			    	items: [
			    		
			    	],
			    	listeners: {
			    		activate: {
			    			scope: this,
			    			fn: function() {
				    			this.showOrderStatus(2);		
			    			}	
			    		}	
			    	}
			    },
			    {
			    	title: 'Paid',
			    	id: 'order-status-3',
			    	items: [
			    		
			    	],
			    	listeners: {
			    		activate: {
			    			scope: this,
			    			fn: function() {
				    			this.showOrderStatus(3);	
			    			}	
			    		}	
			    	}
			    },
			    {
			    	title: 'Shipped / Handled',
			    	id: 'order-status-4',
			    	items: [
			    		
			    	],
			    	listeners: {
			    		activate: {
			    			scope: this,
			    			fn: function() {
				    			this.showOrderStatus(4);		
			    			}	
			    		}	
			    	}
			    }
		    ]
		});	

		// The mainpanel always has to be in the "this.mainPanel" variable
		this.mainPanel = new Ext.Panel({
			renderTo: 'visioncart-content',
			border: false,
			unstyled: true,
			items: [
			  this.tabs
			]
		});
	},
	showOrderStatus: function(status) {
		Ext.getCmp('order-status-'+status).add(this.orderGrid);
		Ext.getCmp('order-status-'+status).doLayout();
		vcCore.stores.orders.baseParams.status = status;		
		this.orderGrid.getStore().load();		
	},
	setOrderStatus: function(status) {
		Ext.Msg.show({
			title:'Send email?',
			msg: 'Do you wish to send an email to the customer about the status update?',
			buttons: Ext.Msg.YESNOCANCEL,
			scope: this,
			fn: function(response) {
				if (response != 'cancel') {
					if (response == 'yes') {
						var sendMail = 1;
					} else {
						var sendMail = 0;
					}
					
					vcCore.ajax.request({
						url: vcCore.config.connectorUrl,
						params: {
							id: this.rowMenu.baseParams.rowId,
							status: status,
							sendMail: sendMail,
							shopId: vcCore.getUrlVar('shopid'),
							action: 'mgr/orders/update'
						},
						scope: this,
						success: function(response) {
							this.orderGrid.getStore().load();	
						}
					});	
				}
			},
			icon: Ext.MessageBox.QUESTION
		});

	}, 
	showOrder: function() {
		vcCore.ajax.request({
			url: vcCore.config.connectorUrl,
			params: {
				id: this.rowMenu.baseParams.rowId,
				action: 'mgr/orders/get'
			}//,
			//scope: this,
			//success: function(response) {
			//	this.orderGrid.getStore().load();	
			//}
		});	
	}
	
});

Ext.onReady(function() {
	// Set page title and load main panel
	vcCore.setTitle(vcCore.config.currentShop+' - Orders');
	
	// this makes the main class accessible through vcCore.pageClass and the panel through vcCore.pagePanel
	vcCore.loadPanel(vcPageOrders); 
});