var vcPageShops = Ext.extend(Ext.Panel, {
	initComponent: function() {
		this.updatingShop = false;
		this.shopConfigShipping = [];
		this.shopConfigPayment = [];
		
		// Rightclick mouse menu for the grid
		this.rowMenu = new Ext.menu.Menu({
			baseParams: {
				rowId: 0
			},
			items: [
				{
					text: 'Update',
					listeners: {
						click: {
							scope: this,
							fn: function() {
								vcCore.ajax.request({
									url: vcCore.config.connectorUrl,
									params: {
										id: this.rowMenu.baseParams.rowId,
										action: 'mgr/shops/get'
									},
									scope: this,
									success: function(response) {
										var shopConfig = Ext.decode(response.responseText);
										shopConfig = shopConfig.object;
										
										// Set form values
										this.newShopForm.getForm().setValues(shopConfig);
										this.newShopForm.getForm().setValues(shopConfig.config);
										
										vcCore.stores.categories.baseParams.shopId = this.rowMenu.baseParams.rowId;
										vcCore.stores.categories.load();
										
										this.shopConfigShipping = shopConfig.config.shippingModules;
										this.shopConfigPayment = shopConfig.config.paymentModules;
										
					    				this.newShopWindow.show();
									}
								});
							}	
						}	
					}
				},
				{
					text: 'Remove',
					listeners: {
						click: {
							scope: this,
							fn: function() {
								Ext.Msg.show({
									title: 'Remove shop?',
									msg: 'All of the products/categories/options and <b>everything linked to this shop</b> will be removed! Continue?',
									buttons: Ext.Msg.YESNO,
									fn: function(response) {
										if (response == 'yes') {
											vcCore.ajax.request({
												url: vcCore.config.connectorUrl,
												params: {
													id: this.rowMenu.baseParams.rowId,
													action: 'mgr/shops/remove'
												},
												scope: this,
												success: function(response) {
													window.location = window.location;
												}
											});
										}
									},
									icon: Ext.MessageBox.QUESTION,
									scope: this
								});
							}
						}
					}
				}
			]
		});
		
		this.paymentGrid = new Ext.grid.GridPanel({
			store: vcCore.stores.paymentModules,
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
		    columns: [
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'name',
	                id: 'payment-module',
	                header: 'Payment module'
	            },
	            {
			    	xtype: 'gridcolumn',
	                dataIndex: 'id',
	                header: 'Active',
	                renderer: function(value) {
                		return '<input type="checkbox" name="vc_payment_'+value+'" value="true" checked="checked" />';	
	                }
			    }
	        ],
	        listeners: {
	        	added: {
	        		scope: this,
	        		fn: function() {
		        		this.paymentGrid.getStore().load();
		        	}
	        	}
	        },
		    autoExpandColumn: 'payment-module'
		});
		
		this.shippingGrid = new Ext.grid.GridPanel({
			store: vcCore.stores.shippingModules,
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
		    columns: [
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'name',
	                id: 'shipping-module',
	                header: 'Shipping module'
	            },
	            {
			    	xtype: 'gridcolumn',
	                dataIndex: 'id',
	                header: 'Active',
	                renderer: function(value) {
	                	return '<input type="checkbox" name="vc_shipping_'+value+'" value="true" checked="checked" />';	
	                }
			    }
	        ],
	        listeners: {
	        	added: {
	        		scope: this,
	        		fn: function() {
		        		this.shippingGrid.getStore().load();
		        	}
	        	}
	        },
		    autoExpandColumn: 'shipping-module'
		});
		
		this.newShopForm = new Ext.form.FormPanel({
			border: false,
			labelWidth: 150,
			monitorValid: true,
			buttons: [
				{
					text: 'Cancel',
					scope: this,
					handler: function() {
						this.newShopWindow.hide();	
					}
				},
				{
					text: 'Save',
					formBind: true,
					scope: this,
					handler: function() {
						var paymentModules = Ext.query('input[name^=vc_payment_]');
						var shippingModules = Ext.query('input[name^=vc_shipping_]');
						var shopConfig = this.newShopForm.getForm().getFieldValues();
						
						var paymentObject = [];
						Ext.each(paymentModules, function(item, key) {
							item = Ext.get(item);
							var paymentId = item.getAttribute('name');
							paymentId = paymentId.split('_');
							paymentId = paymentId[2];
							
							paymentObject.push({
								id: paymentId,
								active: item.dom.checked
							});
						});
						
						var shippingObject = [];
						Ext.each(shippingModules, function(item, key) {
							item = Ext.get(item);
							var shippingId = item.getAttribute('name');
							shippingId = shippingId.split('_');
							shippingId = shippingId[2];
							
							shippingObject.push({
								id: shippingId,
								active: item.dom.checked
							});
						});
						
						paymentObject = Ext.encode(paymentObject);
						shippingObject = Ext.encode(shippingObject);
						shopConfig = Ext.encode(shopConfig);
						
						var postData = {
							paymentObject: paymentObject,
							shippingObject: shippingObject,
							shopConfig: shopConfig,
							action: 'mgr/shops/save'
						}
						
						vcCore.ajax.request({
							url: vcCore.config.connectorUrl,
							params: postData,
							scope: this,
							success: function() {
								if (Ext.getCmp('vc_shop_id').getValue() == 0 || Ext.getCmp('vc_shop_id').getValue() == '') {
									window.location = window.location;
								} else {
									vcCore.stores.shops.load();	
									this.newShopWindow.hide();
								}
							}
						});
					}
				}
			],
			items: [
				{
					xtype: 'hidden',
					id: 'vc_shop_id',
					name: 'id',
					defaultValue: 0	
				},
				{
					xtype: 'tabpanel',
					plain: true,
					id: 'vc_shops_tab',
					deferredRender: false,
					padding: 10,
					activeTab: 0,
					items: [
						{
							title: 'Shop details',
							layout: 'form',
							autoHeight: true,
							items: [
								{
									xtype: 'textfield',
									fieldLabel: 'Shop name',
									name: 'name',
									allowBlank: false,
									listeners: {
										blur: {
											scope: this,
											fn: function(field) {
												if (Ext.getCmp('shop-alias').getValue() == '') {
													Ext.getCmp('shop-alias').setValue(vcCore.createAlias(field.getValue()));
												}
											}	
										}
									}
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Shop alias',
									id: 'shop-alias',
									name: 'alias',
									allowBlank: false
								},
								{
									xtype: 'textarea',
									fieldLabel: 'Shop description',
									name: 'description',
									rows: 4,
									anchor: '100%',
									allowBlank: false
								},
								{
									xtype: 'combo',
									displayField: 'key',
									valueField: 'key',
									width: 150,
									forceSelection: true,
									store: vcCore.stores.context,
									mode: 'remote',
									triggerAction: 'all',
									fieldLabel: 'Context',
									name: 'context',
									allowBlank: false
								},
								{
									xtype: 'checkbox',
									fieldLabel: 'Active',
									name: 'active'	
								}
							]
						},
						{
							title: 'Settings', 
							layout: 'form',
							autoHeight: true,
							items: [
								{
									xtype: 'tabpanel',
									plain: true,
									deferredRender: false,
									autoHeight: true,
									padding: 10,
									activeTab: 0,
									items: [
										{
											title: 'Money',
											autoHeight: true,
											layout: 'form',
											items: [
												{
													xtype: 'fieldset',
													title: 'Currency and price configuration',
													items: [
														{
															xtype: 'textfield',
															fieldLabel: 'Shop currency',
															name: 'currency',
															allowBlank: false	 
														},
														{
															xtype: 'combo',
															displayField: 'option',
															valueField: 'value',
															width: 150,
															forceSelection: true,
															store: new Ext.data.SimpleStore({
														        fields: ['option', 'value'],
														        data: [
														        	['Inclusive', 'in'],
														        	['Exclusive', 'ex']
														        ]
														    }),
															mode: 'local',
															triggerAction: 'all',
															fieldLabel: 'Default price view',
															name: 'defaultBtw'
														},
														{
															xtype: 'textfield',
															fieldLabel: 'Decimal separator',
															name: 'decimalSeparator',
															allowBlank: false	 
														},
														{
															xtype: 'textfield',
															fieldLabel: 'Thousands separator',
															name: 'thousandsSeparator',
															allowBlank: true	 
														}
													]
												},
												{
													xtype: 'fieldset',
													title: 'Taxes',
													items: [
														{
															xtype: 'combo',
															displayField: 'name',
															valueField: 'id',
															width: 150,
															forceSelection: true,
															store: vcCore.stores.categories,
															mode: 'remote',
															triggerAction: 'all',
															fieldLabel: 'Taxes category',
															name: 'taxesCategory',
															description: 'Select the category that holds your tax categories'
														},
														{
															xtype: 'combo',
															displayField: 'option',
															valueField: 'value',
															width: 150,
															forceSelection: true,
															store: new Ext.data.SimpleStore({
														        fields: ['option', 'value'],
														        data: [
														        	['Yes', 1],
														        	['No', 0]
														        ]
														    }),
															mode: 'local',
															triggerAction: 'all',
															fieldLabel: 'Calculate shipping taxes',
															description: 'Define if shipping costs are subject to taxes',
															name: 'calculateShippingTaxes'
														},
														{
															xtype: 'combo',
															displayField: 'option',
															valueField: 'value',
															width: 150,
															forceSelection: true,
															store: new Ext.data.SimpleStore({
														        fields: ['option', 'value'],
														        data: [
														        	['Yes', 1],
														        	['No', 0]
														        ]
														    }),
															mode: 'local',
															triggerAction: 'all',
															fieldLabel: 'Calculate payment taxes',
															description: 'Define if payment costs are subject to taxes',
															name: 'calculatePaymentTaxes'
														}
													]
												}
											]
										},
										{
											title: 'Images',
											autoHeight: true,
											layout: 'form',
											items: [
												{
													xtype: 'textarea',
													anchor: '100%',
													name: 'thumbnails',
													allowBlank: false,
													fieldLabel: 'Product images',
													description: 'Here you can define what thumbnails should be created when you upload a new product picture. Define this <b>BEFORE</b> uploading any category pictures because if you change this afterwards the behaviour is very unpredictable.<br /><br />Format:<br /><b>w</b>=widthInPixels,<b>h</b>=heightInPixels,<b>prefix</b>=img_,<b>crop</b>=false'
												},
												{
													xtype: 'textarea',
													anchor: '100%',
													name: 'categoryThumbnails',
													allowBlank: false,
													fieldLabel: 'Category images',
													description: 'Here you can define what thumbnails should be created when you upload a new category picture. Define this <b>BEFORE</b> uploading any category pictures because if you change this afterwards the behaviour is very unpredictable.<br /><br />Format:<br /><b>w</b>=widthInPixels,<b>h</b>=heightInPixels,<b>prefix</b>=img_,<b>crop</b>=false'
												}
											]
										},
										{
											title: 'Orders',
											layout: 'form',
											autoHeight: true,
											items: [
												{
													xtype: 'fieldset',
													title: 'Order settings',	
													items: [
														{
															xtype: 'checkbox',
															fieldLabel: 'Enable ordering',
															name: 'enableOrdering',
															description: 'If ordering is disabled your webshop becomes a catalogue'
														},
														{
															xtype: 'textfield',
															fieldLabel: 'Free shipping boundary',
															name: 'freeShippingBoundary',
															allowBlank: false,
															description: 'If the total amount of an order is more then this boundary shipping will be free'
														},
														{
															xtype: 'checkbox',
															fieldLabel: 'Use minimum order amount',
															name: 'enableMinimumOrderAmount',
															description: 'Enabled/disable minimum order amount'
														},
														{
															xtype: 'textfield',
															fieldLabel: 'Minimum order amount',
															name: 'minimumOrderAmount',
															allowBlank: false,
															description: 'The minimum amount of a basket before a user can order'
														},
														{
															xtype: 'combo',
															displayField: 'option',
															valueField: 'value',
															width: 150,
															forceSelection: true,
															store: new Ext.data.SimpleStore({
														        fields: ['option', 'value'],
														        data: [
														        	['When paid', 'paid'],
														        	['When shipped', 'shipped']
														        ]
														    }),
															mode: 'local',
															triggerAction: 'all',
															fieldLabel: 'Decrease stock',
															name: 'stockDecrease',
															description: 'When paid = stock gets decreased after user has paid. When shipped = stock gets decreased after the admin has set the order to "Shipped"'
														}
													]
												},
												{
													xtype: 'fieldset',
													title: 'Order number settings',	
													items: [
														{
															xtype: 'textfield',
															fieldLabel: 'Order number format',
															name: 'orderNumberFormat',
															description: 'Format your ordernumber.<br /><b>Do not change this after the first order is placed!</b><br />You can use the following placeholders:<br /><b>[[+year]]</b> - The current year<br /><b>[[+orderNumber]]</b> - The current ordernumber',
															allowBlank: false
														},
														{
															xtype: 'combo',
															displayField: 'option',
															valueField: 'value',
															description: 'The length of your ordernumber.<br /><b>Do not change this after the first order is placed!</b>',
															width: 150,
															forceSelection: true,
															store: new Ext.data.SimpleStore({
														        fields: ['option', 'value'],
														        data: [
														        	['5', 5],
														        	['6', 6],
														        	['7', 7],
														        	['8', 8],
														        	['9', 9],
														        	['10', 10],
														        	['11', 11],
														        	['12', 12]
														        ]
														    }),
															mode: 'local',
															triggerAction: 'all',
															fieldLabel: 'Order number length',
															name: 'orderNumberLength'
														},
														{
															xtype: 'textfield',
															fieldLabel: 'Current order number',
															name: 'currentOrderNumber',
															description: 'Here you can set/change the current order number. <b>We strongly advice you to not change this unless you are starting a new year or a new administration.</b>',
															allowBlank: false
														}
													]
												}
											]
										},
										{
											title: 'Email',
											autoHeight: true, 
											layout: 'form',
											items: [
												{
													xtype: 'fieldset',
													title: 'General',
													items: [
														{
															xtype: 'textfield',
															fieldLabel: 'From name',
															name: 'emailFromName',
															allowBlank: false	
														},
														{
															xtype: 'textfield',
															fieldLabel: 'From address',
															name: 'emailFromAddress',
															allowBlank: false	
														}
													]
												},
												{
													xtype: 'fieldset',
													title: 'Out chunk configuration',
													items: [
														{
															xtype: 'combo',
															displayField: 'name',
															valueField: 'name',
															width: 150,
															forceSelection: true,
															store: vcCore.stores.emailChunks,
															mode: 'remote',
															triggerAction: 'all',
															fieldLabel: 'Outer email chunk',
															name: 'emailOuterChunk',
															allowBlank: false
														}
													]
												},
												{
													xtype: 'fieldset',
													title: 'Inner chunk configuration',
													items: [
														{
															xtype: 'textfield',
															fieldLabel: 'Status update subject',
															name: 'emailSubjectStatusUpdate',
															allowBlank: false,
															description: 'You can use any order object column as placeholder in the subject! (eg. [[+ordernumber]])'
														}
													]
												}
											]
										},
										{
											title: 'Templates / Resources',
											layout: 'form',
											autoHeight: true,
											labelWidth: 200,
											items: [
												{
													xtype: 'combo',
													displayField: 'theme',
													valueField: 'theme',
													width: 150,
													forceSelection: true,
													store: vcCore.stores.themes,
													mode: 'remote',
													triggerAction: 'all',
													fieldLabel: 'Default theme',
													name: 'shopTheme',
													allowBlank: false
												},
												{
													xtype: 'combo',
													displayField: 'pagetitle',
													valueField: 'id',
													width: 150,
													forceSelection: true,
													store: vcCore.stores.resources,
													mode: 'remote',
													triggerAction: 'all',
													fieldLabel: 'Default category',
													name: 'categoryResource',
													allowBlank: false
												},
												{
													xtype: 'combo',
													displayField: 'pagetitle',
													valueField: 'id',
													width: 150,
													forceSelection: true,
													store: vcCore.stores.resources,
													mode: 'remote',
													triggerAction: 'all',
													fieldLabel: 'Default product',
													name: 'productResource',
													allowBlank: false
												},
												{
													xtype: 'combo',
													displayField: 'pagetitle',
													valueField: 'id',
													width: 150,
													forceSelection: true,
													store: vcCore.stores.resources,
													mode: 'remote',
													triggerAction: 'all',
													fieldLabel: 'Order process',
													name: 'orderProcessResource',
													allowBlank: false
												},
												{
													xtype: 'combo',
													displayField: 'pagetitle',
													valueField: 'id',
													width: 150,
													forceSelection: true,
													store: vcCore.stores.resources,
													mode: 'remote',
													triggerAction: 'all',
													fieldLabel: 'Order history',
													name: 'orderHistoryResource',
													allowBlank: false
												}
											]
										}
									]
								}
							]
						},
						{
							title: 'Payment modules',
							layout: 'form',
							items: [
								 this.paymentGrid
							],
							listeners: {
								activate: {
									scope: this,
									fn: function() {
										if (Ext.isArray(this.shopConfigPayment)) {
											if (this.shopConfigPayment.length > 0) {
												var paymentModules = Ext.query('input[name^=vc_payment_]');
												Ext.each(paymentModules, function(item) {
													item.checked = false;
													var paymentId = item.getAttribute('name');
													paymentId = paymentId.split('_');
													paymentId = paymentId[2];
													Ext.each(this.shopConfigPayment, function(paymentModule) {
														if (paymentModule.id == paymentId && paymentModule.active == 1) {
															item.checked = true;
														}
													}, this);
												}, this);
											}
										}
									}
								}
							}
						},
						{
							title: 'Shipping modules',
							layout: 'form',
							items: [
								this.shippingGrid
							],
							listeners: {
								activate: {
									scope: this,
									fn: function() {
										if (Ext.isArray(this.shopConfigShipping)) {
											if (this.shopConfigShipping.length > 0) {
												var shippingModules = Ext.query('input[name^=vc_shipping_]');
												Ext.each(shippingModules, function(item) {
													item.checked = false;
													var shippingId = item.getAttribute('name');
													shippingId = shippingId.split('_');
													shippingId = shippingId[2];
													Ext.each(this.shopConfigShipping, function(shippingModule) {
														if (shippingModule.id == shippingId && shippingModule.active == 1) {
															item.checked = true;
														}
													}, this);
												}, this);
											}
										}
									}
								}
							}
						},
						{
							title: 'Manager settings',
							layout: 'form',
							items: [
								{
									xtype: 'checkbox',
									name: 'hideSkus',
									fieldLabel: 'Hide sku\'s',
									description: 'If checked the SKU\'s will be hidden by default in the product list'
								}
							]
						}
					]	
				}
			]
		});
		
		this.newShopWindow = new Ext.Window({
			padding: 10,
			title: 'Shop properties',
			width: 750,
			y: 15,
			modal: true,
			autoHeight: true,
			closeAction: 'hide',
			items: [
				this.newShopForm
			],
			listeners: {
				show: {
					scope: this,
					fn: function() {
						vcCore.stores.paymentModules.load();
	    				vcCore.stores.shippingModules.load();
	    				Ext.getCmp('vc_shops_tab').setActiveTab(0);
					}	
				},
				hide: {
					scope: this,
					fn: function() {
						this.shopConfigPayment = [];	
						this.shopConfigShipping = [];
					}	
				}
			}
		});

		// The mainpanel always has to be in the "this.mainPanel" variable
		this.mainPanel = new Ext.Panel({
			renderTo: 'visioncart-content',
			padding: 15,
			border: true,
			items: [
				new Ext.grid.GridPanel({
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
				    tbar: {
				    	items: [
				    		new Ext.Button({
				    			text: 'New',
				    			scope: this,
				    			handler: function() {
				    				this.newShopForm.getForm().setValues({
				    					name: '',
				    					description: '',
				    					alias: '',
				    					active: true,
				    					context: 'web',
				    					defaultBtw: 'in',
				    					enableOrdering: true,
				    					enableMinimumOrderAmount: false,
				    					minimumOrderAmount: 0,
				    					freeShippingBoundary: 0,
				    					stockDecrease: 'paid',
				    					thumbnails: 'w=75,h=75,prefix=thumb_'+"\n"+'w=250,h=250,prefix=medium_'+"\n"+'w=600,h=600,prefix=big_',
				    					categoryThumbnails: 'w=75,h=75,prefix=thumb_'+"\n"+'w=250,h=250,prefix=big_',
				    					hideSkus: true,
				    					orderNumberLength: 5,
				    					orderNumberFormat: 'ORD[[+year]][[+orderNumber]]',
				    					currentOrderNumber: 0,
				    					decimalSeparator: ',',
				    					calculateShippingTaxes: 1,
				    					calculatePaymentTaxes: 0,
				    					currency: '$'
				    				});
				    				this.newShopWindow.show();
				    				this.newShopForm.getForm().clearInvalid();
				    			}
				    		})
				    	]
				    },
				    store: vcCore.stores.shops,
				    columns: [
				   			 {
				                xtype: 'gridcolumn',
				                dataIndex: 'id',
				                header: 'ID',
				                sortable: true,
				                width: 20
				            },
				            {
				                xtype: 'gridcolumn',
				                dataIndex: 'name',
				                header: 'Shop',
				                sortable: true,
				                id: 'shop-name'
				            },
				            {
				                xtype: 'gridcolumn',
				                dataIndex: 'context',
				                header: 'Context',
				                sortable: true,
				                width: 100
				            }
			        ],
				    autoExpandColumn: 'shop-name',
				    listeners: {
				    	rowContextMenu: {
				    		scope: this,
				    		fn: function(grid, rowIndex, event) {
				    			// Set the database ID in the menu's base params so we can access it when an action is performed
				    			this.rowMenu.baseParams.rowId = vcCore.stores.shops.getAt(rowIndex).get('id');
				    			this.rowMenu.showAt(event.xy);
				    			event.stopEvent();
				    		}
				    	}
				    }
				})
			]
		});
	}
});

Ext.onReady(function() {
	// Set page title and load main panel
	vcCore.setTitle('Manage shops');
	
	// this makes the main class accessible through vcCore.pageClass and the panel through vcCore.pagePanel
	vcCore.loadPanel(vcPageShops); 
	
	// Load the datastore for the shops
	vcCore.stores.shops.load();
});