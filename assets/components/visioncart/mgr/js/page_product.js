var vcPageProduct = Ext.extend(Ext.Panel, {
	initComponent: function() {
		vcCore.stores.options.baseParams.shopId = vcCore.getUrlVar('shopid');
		vcCore.stores.sku.baseParams.prodId = vcCore.config.productParent;
		vcCore.stores.options.baseParams.prodId = vcCore.config.productParent;
		vcCore.stores.categories.baseParams.parent = vcCore.config.taxesCategory;
		vcCore.stores.categories.baseParams.shopId = vcCore.getUrlVar('shopid');
		
		this.newProduct = false;
		
		// Check if product is SKU
		if (vcCore.getUrlVar('sku') != '' || vcCore.getUrlVar('prodid') != vcCore.config.productParent) {
			this.isSku = true;
		}
		
		var productId = 0;
		this.fileUploadingMessage = null;
		Ext.onReady(function() {
			this.loadCustomTabs();
		}, this);
		
		// Rightclick mouse menu for the images
		this.imageMenu = new Ext.menu.Menu({
			baseParams: {
				image: ''
			},
			items: [
				{
					text: 'Remove',
					listeners: {
						click: {
							scope: this,
							fn: function() {
								Ext.Msg.show({
									title: 'Remove image?',
									msg: 'All of the associated thumbnails will be deleted as well, continue?',
									buttons: Ext.Msg.YESNO,
									fn: function(response) {
										if (response == 'yes') {
											vcCore.ajax.request({
												url: vcCore.config.connectorUrl,
												params: {
													image: this.imageMenu.baseParams.image,
													action: 'mgr/products/removeimage',
													shopid: vcCore.getUrlVar('shopid'),
													id: vcCore.getUrlVar('prodid')
												},
												scope: this,
												success: function(response) {
													this.getProductImages();
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
		
		// Right click menu for the options
		this.optionMenu = new Ext.menu.Menu({
			baseParams: {
				optionId: ''
			},
			items: [
				{
					text: 'Remove option',
					listeners: {
						click: {
							scope: this,
							fn: function() {
								Ext.Msg.show({
									title: 'Remove option?',
									msg: 'The option values from underlying SKU\'s will be removed as well, continue?',
									buttons: Ext.Msg.YESNO,
									fn: function(response) {
										if (response == 'yes') {
											vcCore.ajax.request({
												url: vcCore.config.connectorUrl,
												params: {
													optionId: this.optionMenu.baseParams.optionId,
													action: 'mgr/products/removeoption',
													shopid: vcCore.getUrlVar('shopid'),
													prodid: vcCore.getUrlVar('prodid')
												},
												scope: this, 
												success: function(response) {
													this.optionGrid.getStore().load();
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
		
		// Rightclick mouse menu for the SKU grid
		this.skugridMenu = new Ext.menu.Menu({
			baseParams: {
				rowId: ''
			},
			items: [
				{
					text: 'Update SKU',
					listeners: {
						click: {
							scope: this,
							fn: function() {
								var productId = this.skugridMenu.baseParams.rowId;
								if (productId == vcCore.getUrlVar('prodid')) {
									Ext.Msg.alert('Information', 'You are already editing this SKU.');
								} else {
									// First save the product
									if (this.categoryTree.getChecked().length == 0 && this.newProduct == true) {
										Ext.Msg.alert('Error', 'A product must have <b>at least one</b> linked category.');
									} else {
										if (productId == vcCore.config.productParent) {
											this.saveProduct(function() {
												window.location = '?a='+vcCore.getUrlVar('a')+'&action='+vcCore.getUrlVar('action')+'&shopid='+vcCore.getUrlVar('shopid')+'&prodid='+productId+'&parent='+vcCore.config.productParent;
											});
										} else {
											this.saveProduct(function() {
												window.location = '?a='+vcCore.getUrlVar('a')+'&action='+vcCore.getUrlVar('action')+'&shopid='+vcCore.getUrlVar('shopid')+'&prodid='+productId+'&sku=true&parent='+vcCore.config.productParent;
											});
										}	
									}
								}
							}
						}
					}
				},
				{
					text: 'Remove SKU',
					listeners: {
						click: {
							scope: this,
							fn: function() {
								var productId = this.skugridMenu.baseParams.rowId;
								if (productId == vcCore.getUrlVar('prodid')) {
									Ext.Msg.alert('Information', 'You can not delete the product you are editing. Switch to another product or delete the main product in the product tree.');
								} else if (vcCore.config.productParent == productId) {
									Ext.Msg.alert('Information', 'You can not delete the main product. You must delete the main product from the product tree.');
								} else {
									Ext.Msg.show({
										title: 'Remove product?',
										msg: 'Are you sure you want to <b>remove this product</b> and all it\'s data?)',
										buttons: Ext.Msg.YESNO,
										scope: this,
										fn: function(response) {
											if (response == 'yes') {
												vcCore.ajax.request({
													url: vcCore.config.connectorUrl,
													params: {
														action: 'mgr/products/removeproduct',
														prodid: productId
													},
													scope: this,
													success: function(response) {
														this.skuGrid.getStore().load();
													}
												});
											}
										}
									});	
								}
							}
						}
					}
				}
			]
		});
		
		this.categoryTree = new Ext.tree.TreePanel({
		    dataUrl: vcCore.config.connectorUrl+'?action=mgr/products/getnodes&productId='+vcCore.getUrlVar('prodid')+'&shop='+vcCore.getUrlVar('shopid')+'&sku='+vcCore.getUrlVar('sku'),
			border: false,
	        autoScroll: true,
	        animate: false,
	        height: 150,
	        anchor: '100%',
	        autoHeight: true,
	        enableDD: true,
	        useArrows: true,
	        fieldLabel: 'Categories',
	        containerScroll: true,
			root: {
		        nodeType: 'async',
		        text: 'Webshop root',
		        draggable: false,
		        id: 'category:0|parent:0'
		    },
		    listeners: {
		    	expandnode: function() {
		    		if (vcCore.getUrlVar('sku') != '') {
			    		var checkBoxes = Ext.query('input[class*=x-tree-node-cb]');
			    		
			    		Ext.each(checkBoxes, function(item) {
			    			item.setAttribute('disabled', 'disabled');
			    		});
		    		}
		    	},
		    	checkchange: {
		    		scope: this,
		    		fn: function(node, checked) {
		    			var id = node.id;
		    			
						vcCore.ajax.request({
							url: vcCore.config.connectorUrl,
							params: {
								action: 'mgr/products/updatecategory',
								prodid: vcCore.getUrlVar('prodid'),
								shopid: vcCore.getUrlVar('shopid'),
								catid: id,
								checked: checked
							},
							scope: this,
							success: function(response) {
								var responseObject = Ext.decode(response.responseText);
								
								if (!responseObject.success) {
									Ext.Msg.alert('Error', 'A product must have <b>at least one</b> linked category.');
									node.getUI().toggleCheck(true);
								}
							}
						});
		    		}	
		    	}
		    }
		});
		
		this.categoryTree.getRootNode().expand();

		//Options grid
		this.optionGrid = new Ext.grid.GridPanel({
			store: vcCore.stores.productoptions,
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
			autoExpandColumn: 'option-value',
		    columns: [
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'option',
		            sortable: true,
		            header: 'Option'
		        },
		    	{
		            xtype: 'gridcolumn',
		            sortable: true,
		            dataIndex: 'value',
		            id: 'option-value',
		            header: 'Value'
		        }
		    ],
		    listeners: {
		    	rowContextMenu: {
			    	scope: this,
		    		fn: function(grid, rowIndex, event) {
		    			// Set the database ID in the menu's base params so we can access it when an action is performed
		    			this.optionMenu.baseParams.optionId = grid.getStore().getAt(rowIndex).get('optionid');
		    			this.optionMenu.showAt(event.xy);
		    			event.stopEvent();
		    		}
				},
				added: {
					scope: this,
					fn: function() {
						this.optionGrid.getStore().load();
					}
				}
		    }
		});
		
		this.skuGrid = new Ext.grid.GridPanel({
			store: vcCore.stores.sku,
			region: 'east',
			columnWidth: .80,
			autoHeight: true,
			loadMask: true,
			viewConfig: {
	            forceFit: true,
	            enableRowBody: true,
	            autoFill: true,
	            showPreview: true,
	            scrollOffset: 0,
	            deferEmptyText: false,
	            emptyText: _('ext_emptymsg'),
	            getRowClass: function(record, index) {
	            	if (record.get('id') == vcCore.getUrlVar('prodid')) {
	            		return 'vc-grid-current';
	            	}	
	            }
	        },
			autoExpandColumn: 'name',
		    columns: [
		    	{
		    		xtype: 'gridcolumn',
		            dataIndex: 'id',
		            sortable: false,
		            header: 'ID',
		            width: 15
		    	},
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'name',
		            id: 'name',
		            sortable: true,
		            header: 'Name'
		        },
		        {
		            xtype: 'gridcolumn',
		            sortable: true,
		            dataIndex: 'articlenumber',
		            header: 'Article number'
		        },
		    	{
		            xtype: 'gridcolumn',
		            sortable: true,
		            dataIndex: 'price',
		            width: 50,
		            header: 'Price (ex)'
		        },
		    	{
		            xtype: 'gridcolumn',
		            sortable: true,
		            dataIndex: 'stock',
		            width: 50,
		            header: 'Stock'
		        },
		    	{
		            xtype: 'gridcolumn',
		            sortable: true,
		            dataIndex: 'active',
		            width: 20,
		            header: 'Active'
		        }
		    ],
		    listeners: {
		    	load: function() {
		    		Ext.QuickTips.init();
		    	},
		    	rowcontextmenu: {
			    	scope: this,
		    		fn: function(grid, rowIndex, event) {
		    			// Set the database ID in the menu's base params so we can access it when an action is performed
		    			this.skugridMenu.baseParams.rowId = vcCore.stores.sku.getAt(rowIndex).get('id');
		    			this.skugridMenu.showAt(event.xy);
		    			event.stopEvent();
		    		}
				},
				celldblclick: {
					scope: this,
					fn: function(grid, rowIndex, columnIndex, event) {
						if (columnIndex == 0) {
							return false;	
						}
						
						var productId = vcCore.stores.sku.getAt(rowIndex).get('id');
						if (productId == vcCore.getUrlVar('prodid')) {
							Ext.Msg.alert('Information', 'You are already editing this SKU.');
						} else {
							// First save the product
							if (this.categoryTree.getChecked().length == 0 && this.newProduct == true) {
								Ext.Msg.alert('Error', 'A product must have <b>at least one</b> linked category.');
							} else {
								if (productId == vcCore.config.productParent) {
									this.saveProduct(function() {
										window.location = '?a='+vcCore.getUrlVar('a')+'&action='+vcCore.getUrlVar('action')+'&shopid='+vcCore.getUrlVar('shopid')+'&prodid='+productId+'&parent='+vcCore.config.productParent;
									});
								} else {
									this.saveProduct(function() {
										window.location = '?a='+vcCore.getUrlVar('a')+'&action='+vcCore.getUrlVar('action')+'&shopid='+vcCore.getUrlVar('shopid')+'&prodid='+productId+'&sku=true&parent='+vcCore.config.productParent;
									});
								}	
							}
						}
					}
				},
				added: {
					scope: this,
					fn: function() {
						this.skuGrid.getStore().load();
					}
				},
				mouseover: {
					scope: this,
					fn: function(event) {
						event.stopEvent();
						
						var row = event.getTarget('.x-grid3-row');
						var col = event.getTarget('.x-grid3-col');
						
						if (Ext.get(row)) {
							row = Ext.get(row);
							
							var skuId = parseFloat(Ext.util.Format.stripTags(row.child('td').getAttribute('innerHTML')));
							
							var record = vcCore.stores.sku.getAt(vcCore.stores.sku.findExact('id', skuId));
							
							Ext.get('sku-details').update(record.get('qtip'));
						}
					}
				}
		    }
		});
		
		this.toolbarItems = new Array();
		
		this.toolbarItems.push({
			text: 'Save',
			scope: this,
			handler: function() {
				if (this.categoryTree.getChecked().length == 0 && this.newProduct == true) {
					Ext.Msg.alert('Error', 'A product must have <b>at least one</b> linked category.');
				} else {
					this.saveProduct(false);
				}
			}
		});

		if (!this.isSku) {
			this.toolbarItems.push({
				text: 'Cancel',
				scope: this,
				handler: function() {
					window.location = '?a='+vcCore.getUrlVar('a')+'&action=products&shopid='+vcCore.getUrlVar('shopid');		
				}
			});
			
			this.toolbarItems.push('-');
			
			this.toolbarItems.push({
				text: 'Back to categories',
				scope: this,
				handler: function() {
					window.location = '?a='+vcCore.getUrlVar('a')+'&action=products&shopid='+vcCore.getUrlVar('shopid');	
				}	
			});
		} else { 
			this.toolbarItems.push('-');
			this.toolbarItems.push({
				text: 'Back to main product',
				scope: this,
				handler: function() {
					window.location = '?a='+vcCore.getUrlVar('a')+'&action=product&shopid='+vcCore.getUrlVar('shopid')+'&prodid='+vcCore.config.productParent;
				}
			});
		}	
		
		
		new Ext.Toolbar({
			defaults: {
				style: {
					marginLeft: '5px',
					marginRight: '5px'	
				}
			},
			items: [
				this.toolbarItems				
			],
			id: 'modx-action-buttons',
			renderTo: Ext.fly('modAB')
		});
		
		if (!this.isSku) {
			this.attributesTab = {
				title: 'Attributes', 
				autoHeight: true,
				layout: 'form',
				items: [
					{
						xtype: 'fieldset',
						title: 'Add value to product',
						items: [
							{
								xtype: 'combo',
								displayField: 'name',
								valueField: 'id',
								width: 200,
								forceSelection: true,
								store: vcCore.stores.options,
								mode: 'remote',
								triggerAction: 'all',
								fieldLabel: 'Option',
								id: 'option-option',
								name: 'option',
								listeners: {
									focus: function() {
										vcCore.stores.options.load();
									},
									select: function(combo, record) {
										vcCore.stores.optionvalues.baseParams.optionId = record.get('id');
										vcCore.stores.optionvalues.load();
									}	
								}
							},
							{
								xtype: 'combo',
								displayField: 'value',
								valueField: 'id',
								id: 'option-value',
								width: 200,
								forceSelection: true,
								store: vcCore.stores.optionvalues,
								mode: 'remote',
								triggerAction: 'all',
								fieldLabel: 'Value',
								name: 'optionvalue'
							},
							{
								xtype: 'button',
								text: 'Add',
								name: 'add',
								scope: this,
								handler: function() {
									var postData = {
										formData: Ext.encode(this.newProductForm.getForm().getFieldValues()),
										shopId: vcCore.getUrlVar('shopid'),
										prodId: vcCore.getUrlVar('prodid'),
										action: 'mgr/products/saveoptionvalue'
									}
									
									vcCore.stores.optionvalues.baseParams.optionId = '';
									vcCore.stores.optionvalues.load([], false);
									
									vcCore.ajax.request({
										url: vcCore.config.connectorUrl,
										params: postData,
										scope: this,
										success: function() {
											Ext.getCmp('option-option').reset();
											Ext.getCmp('option-value').reset();
											this.optionGrid.getStore().load();	
											this.skuGrid.getStore().load();	
										}
									});
								}
							}
						]
					},
					this.optionGrid
				]
			};
		} else {
			this.attributesTab = {
				title: 'Attributes', 
				autoHeight: true,
				id: 'vc-attributes-tab',
				layout: 'form',
				items: [
				
				],
				listeners: {
					render: {
						scope: this,
						fn: function() {
							vcCore.ajax.request({
								url: vcCore.config.connectorUrl,
								params: {
									action: 'mgr/products/getproductoptions',
									id: vcCore.getUrlVar('prodid')
								}, 
								scope: this,
								success: function(response) {
									var options = Ext.decode(response.responseText);
									Ext.each(options, function(item) {
										// Create a new combobox and add to child
										var combo = new Ext.form.ComboBox({
											displayField: 'value',
											valueField: 'id',
											value: item.valueid,
											width: 200,
											forceSelection: true,
											store: new Ext.data.JsonStore({
										    	root: 'results',
											    idProperty: 'id',
											    url: vcCore.config.connectorUrl,
											    baseParams: {
											    	action: 'mgr/stores/optionvalues',
											    	optionId: item.optionid
											    },
											    fields: [
											        'id', 'value', 'optionid'
											    ]
											}),
											mode: 'remote',
											triggerAction: 'all',
											fieldLabel: item.name,
											name: 'productoption_'+item.optionid,
											baseParams: {
												optionId: item.optionid
											},
											listeners: {
												focus: function(combo) {
													vcCore.stores.optionvalues.baseParams.optionId = combo.baseParams.optionId;
													vcCore.stores.optionvalues.load();
												}
											}
										});
										Ext.getCmp('vc-attributes-tab').add(combo);
									}, this);
									
									Ext.getCmp('vc-attributes-tab').doLayout(false, true);
								}
								
								/*
								[{id:49, shopid:33, name:"Size (GB)", inputsnippet:0, outputsnippet:0, productid:14, optionid:8, valueid:25, weight:0, price:0, shippingprice:0},
								*/
							});	
						}	
					}	
				}
			};
		}
		
		// Main formpanel
		this.newProductForm = new Ext.form.FormPanel({
			fileUpload: true,
			border: false,
			labelWidth: 150,
			monitorValid: true,
			items: [
				{
					xtype: 'hidden',
					fieldLabel: 'Id',
					name: 'id',
					id: 'product-id',
					value: vcCore.getUrlVar('prodid')
				},
				{
					xtype: 'tabpanel',
					plain: true,
					id: 'vc-product-tab',
					deferredRender: false,
					autoHeight: true,
					padding: 10,
					activeTab: 0,
					defaults: {
						autoHeight: true
					},
					items: [
						{
							title: 'Product',
							layout: 'form',
							autoHeight: true,
							items: [
								{
									xtype: 'textfield',
									fieldLabel: 'Title',
									name: 'name',
									anchor: '80%',
									allowBlank: false,
									listeners: {
										blur: {
											scope: this,
											fn: function(field) {
												if (Ext.getCmp('product-alias').getValue() == '') {
													Ext.getCmp('product-alias').setValue(vcCore.createAlias(field.getValue()));
												}
											}	
										}
									}
								},								
								{
									xtype: 'textfield',
									fieldLabel: 'Alias',
									name: 'alias',
									anchor: '80%',
									id: 'product-alias',
									allowBlank: false
								},
								{
									xtype: 'htmleditor',
									fieldLabel: 'Description',
									name: 'description',
									id: 'product-description',
									rows: 4,
									height: 250,
									anchor: '80%',
									allowBlank: false,
									enableFont: false,
									style: {
										fontFamily: 'Verdana'
									},
									fontFamilies: [
									],
									plugins: [
										new Ext.ux.form.HtmlEditor.Formatblock()
									]
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Article number',
									name: 'articlenumber',
									allowBlank: false
								},
								{
									xtype: 'compositefield',
									fieldLabel: 'Price (ex)',
									items: [
										{
											xtype: 'textfield',
											name: 'price',
											id: 'product-price',
											allowBlank: false
										},
										{
											xtype: 'button',
											text: 'Subtract taxes',
											scope: this,
											handler: function() {
												var price = Ext.getCmp('product-price').getValue();
												var tax = Ext.getCmp('product-taxcategory').getValue();
												var store = Ext.getCmp('product-taxcategory').getStore();
												
												var priceChange = 0;
												store.each(function(item) {
													if (item.get('id') == tax) {
														priceChange = item.get('pricechange');	
													}
												}, this);
												
												priceChange = parseFloat('1.'+priceChange);
												
												var newPrice = price / priceChange;
												Ext.getCmp('product-price').setValue(Math.round(newPrice*100) / 100);
											}
										}
									]
								},
						        { 
									xtype: 'combo',
									displayField: 'name',
									valueField: 'id',
									width: 150,
									id: 'product-taxcategory',
									forceSelection: true,
									store: vcCore.stores.categories,
									mode: 'remote',
									triggerAction: 'all',
									fieldLabel: 'Tax category',
									name: 'taxcategory',
									allowBlank: false
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Weight',
									name: 'weight',
									allowBlank: false
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Shipping price',
									dateFormat: 'm/d/Y',
									name: 'shippingprice',
									allowBlank: false
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Stock',
									name: 'stock',
									allowBlank: false
								},
								{
									xtype: 'datefield',
									fieldLabel: 'Publish date',
									dateFormat: 'm/d/Y',
									width: 100,
									id: 'vc-product-publishdate',
									name: 'publishdate',
									allowBlank: true,
									listeners: {
										change: {
											scope: this,
											fn: function(field, newValue, oldValue) {
												if (newValue == '') {
													if (Ext.getCmp('vc-product-unpublishdate').getValue() == '') {
														this.unlockActive();
													}
												} else {
													this.lockActive();	
												}
											}
										}
									}
								},
								{
									xtype: 'datefield',
									fieldLabel: 'Unpublish date',
									dateFormat: 'U',
									width: 100,
									id: 'vc-product-unpublishdate',
									name: 'unpublishdate',
									allowBlank: true,
									listeners: {
										change: {
											scope: this,
											fn: function(field, newValue, oldValue) {
												if (newValue == '') {
													if (Ext.getCmp('vc-product-publishdate').getValue() == '') {
														this.unlockActive();
													}
												} else {
													this.lockActive();	
												}
											}
										}
									}
								},
								{
									xtype: 'checkbox',
									name: 'emptyCache',
									fieldLabel: 'Empty cache',
									checked: true
								},
								{
									
									fieldLabel: 'Active',
									frameBorder: false,
									unstyled: true,
									items: [
										{
											xtype: 'checkbox',
											id: 'product-active',
											name: 'active'
										},
										{
											frameBorder: false,
											unstyled: true,
											id: 'product-active-lock',
											hidden: true,
											html: '<div class="vc-lock"></div>'
										}
									]
								},
								this.categoryTree
							]
						},
						{
							title: 'Images', 
							layout: 'form',
							autoHeight: true,
							items: [
								{
									xtype: 'fieldset',
									title: 'Upload new image',
									autoHeight: true,
									items: [
										{
											xtype: 'fileuploadfield',
											emptyText: 'select image',
											buttonOnly: true,
											fieldLabel: 'Upload image',
											name: 'image',
											allowBlank: true,
											listeners: {
												fileselected: {
													scope: this,
													fn: function(field) {
														this.newProductForm.getForm().getEl().set({
															target: 'file-upload', 
															action: vcCore.config.assetsUrl+'connector.php?action=mgr/products/fileupload&HTTP_MODAUTH='+vcCore.siteId+'&shopid='+vcCore.getUrlVar('shopid')
														});
														this.newProductForm.getForm().getEl().dom.submit();
														
														Ext.Msg.wait('Uploading image...', 'Please wait');
													}	
												}	
											}
										}
									]
								},
								{
									xtype: 'fieldset',
									title: 'Current images',
									autoHeight: true,
									items: [
										{
											xtype: 'panel',
											id: 'product-images-panel',
											html: '<div id="current-product-images"></div>'
										}
									]
								}
							],
							listeners: {
								activate: {
									scope: this,
									fn: function() {
										this.getProductImages();
									}	
								}	
							}
						},
						{
							title: 'Custom fields', 
							layout: 'form',
							autoHeight: true,
							id: 'extra-info-form',
							listeners: {
								activate: {
									scope: this,
									fn: function() {
										this.loadExtraFields();
									}
								},
								deactivate: {
									scope: this,
									fn: function() {
										this.saveProduct();
									}
								}
							},
							items: [
								
							]
						},
						{
							title: 'SKU\'s',
							layout: 'form',
							autoHeight: true,
							items: [
								{
									xtype: 'fieldset',
									autoHeight: true,
									items: [
										{
											xtype: 'button',
											text: 'Add sku',
											scope: this,
											handler: function() {
												// First save the product
												this.saveProduct(function() {
														// Redirect to create a new SKU
														window.location = '?a='+vcCore.getUrlVar('a')+'&action=product&shopid='+vcCore.getUrlVar('shopid')+'&parent='+vcCore.config.productParent+'&sku=true';		
												});
											}	
										}
									]
								},
								{
									xtype: 'panel',
									layout: 'column',
									border: false,
									unstyled: true,
									items: [
										this.skuGrid,
										{
											html: '<div id="sku-details" class="vc-sku-details"></div>',
											unstyled: true,
											border: false,
											style: {
												paddingLeft: '15px'
											},
											columnWidth: .20,
											region: 'east'
										}
									]
								}
							]
						},
						this.attributesTab
					]
				}
			]
		});
		
		// 02-11-2010 (b03tz): Event changed from afterlayout to render (because form gets doLayout after each form inject)
		this.newProductForm.on('render', function() {
			vcCore.ajax.request({
				url: vcCore.config.connectorUrl,
				params: {
					action: 'mgr/products/get',
					id: vcCore.getUrlVar('prodid')
				},
				scope: this,
				success: function(response) {
					var product = Ext.decode(response.responseText);
					this.newProductForm.getForm().reset();
					this.newProductForm.getForm().setValues(product.object);
					
					if (product.object.active == 2 && product.object.parent == 0) {
						this.newProduct = true;	
						this.newProductForm.getForm().setValues({active: true});
						
						vcCore.stores.categories.on('load', function() {
							Ext.getCmp('product-taxcategory').setValue(vcCore.stores.categories.getAt(0).get('id'));
						}, this);
						vcCore.stores.categories.load();
						
						this.newProductForm.getForm().clearInvalid();
					}
				}
			});
		}, this);
				
		// The mainpanel always has to be in the "this.mainPanel" variable
		this.mainPanel = new Ext.Panel({
			renderTo: 'visioncart-content',
			border: false,
			unstyled: true,
			items: [
				this.newProductForm
			]
		});
	},
	hideUploader: function() {
		Ext.Msg.hide();	
		this.getProductImages();
	},
	getProductImages: function() {
		vcCore.ajax.request({
			url: vcCore.config.connectorUrl,
			params: {
				action: 'mgr/products/getimages',
				id: vcCore.getUrlVar('prodid'),
				shopid: vcCore.getUrlVar('shopid')
			},
			scope: this,
			success: function(response) {
				var images = response.responseText;
				Ext.get('current-product-images').update(images);
				this.addImageEvents();
			}
		});
	},
	addImageEvents: function() {
		var images = Ext.getBody().select('div.product-image');
		
		images.each(function(el, ce, index) {
			el.dd = new Ext.dd.DDProxy(el.dom.id);
			
			el.on('contextmenu', function(event, element) {
				var id = element.id;
				if (id == '') {
					var parent = Ext.get(element).up('div');
					id = parent.dom.id;
				}
				
				this.imageMenu.baseParams.image = id;
				
				event.stopEvent();
				this.imageMenu.showAt(event.xy);
			}, this);
			
			Ext.apply(el.dd, {
				itemIndex: index,
				startDrag: function(x, y) {
					var dragEl = Ext.get(this.getDragEl());
					var el = Ext.get(this.getEl());
					 
					dragEl.applyStyles({border:'','z-index':Ext.get('current-product-images').lastZIndex + 1});
					dragEl.update(el.dom.innerHTML);
					dragEl.addClass(el.dom.className + ' dd-proxy');
					 
					this.constrainTo(Ext.get('product-images-panel'));
				},
				onDragOver: function(event, id) {
					var target = Ext.get(id);
					var source = Ext.get(this.getEl());
					
					this.overElement = target;
					source.insertBefore(target);
				},
				onMouseUp: function(event) {
					var dragEl = Ext.get(this.getDragEl());
					var images = Ext.getBody().select('div.product-image');
					var source = Ext.get(this.getDragEl());
					dragEl.applyStyles({display: 'none'});
					dragEl.appendTo(Ext.getBody());
					
					images.applyStyles({
						float: 'left', 
						position: 'static'
					});
					
					var order = '';
					var images = Ext.getBody().select('div.product-image');
					images.each(function(el) {
						order += el.dom.id+',';
					});
					
					vcCore.ajax.request({
						url: vcCore.config.connectorUrl,
						params: {
							action: 'mgr/products/saveimageorder',
							id: vcCore.getUrlVar('prodid'),
							order: order
						},
						scope: this
					});
				}
			});
		}, this);
	},
	loadExtraFields: function() {
		vcCore.ajax.request({
			url: vcCore.config.connectorUrl,
			params: {
				prodid: vcCore.getUrlVar('prodid'),
				shopid: vcCore.getUrlVar('shopid'),
				action: 'mgr/products/getcategoryextrainfo'
			},
			scope: this,
			success: function(response) {
				var extraFields = Ext.decode(response.responseText);
				extraFields = extraFields.object;
				Ext.getCmp('extra-info-form').removeAll();
				
				this.newProductForm.doLayout(false, true);
				Ext.getCmp('extra-info-form').doLayout(false, true);
				
				if(extraFields.length > 0) {
					
					var currentFieldset = null;
					Ext.each(extraFields, function(item, key) {
						switch(item.type) {
							case 'fieldset':
								if (currentFieldset != null) {
									Ext.getCmp('extra-info-form').add(currentFieldset);	
								}
								
								currentFieldset = new Ext.form.FieldSet({
									title: item.fieldLabel,
									collapsible: true
								});
								break;
							default:
							case 'textfield':
								var xtype = 'textfield';
								var allowBlank = true;
								if (item.mandatory) {
									allowBlank = false;	
								}
								currentFieldset.add({
									xtype: xtype,
									name: 'extraconfig_'+item.key,
									value: item.value,
									allowBlank: allowBlank,
									fieldLabel: item.fieldLabel
								});
								break;
							case 'htmleditor':
								var allowBlank = true;
								if (item.mandatory) {
									allowBlank = false;	
								}
								var xtype = 'htmleditor';
								currentFieldset.add({
									xtype: xtype,
									enableFont: false,
									anchor: '100%',
									value: item.value,
									style: {
										fontFamily: 'Verdana'
									},
									name: 'extraconfig_'+item.key,
									allowBlank: allowBlank,
									fieldLabel: item.fieldLabel,
									fontFamilies: [
									],
									plugins: [
										new Ext.ux.form.HtmlEditor.Formatblock()
									]
								});
								break;
							case 'textarea':
								var allowBlank = true;
								if (item.mandatory) {
									allowBlank = false;	
								}
								var xtype = 'textarea';
								currentFieldset.add({
									xtype: xtype,
									anchor: '100%',
									value: item.value,
									name: 'extraconfig_'+item.key,
									allowBlank: allowBlank,
									fieldLabel: item.fieldLabel
								});
								break;
							case 'combobox':
								var allowBlank = true;
								if (item.mandatory) {
									allowBlank = false;	
								}
								var store = new Array();
								Ext.each(item.values, function(comboBoxItem, comboBoxKey) {
									store.push([comboBoxItem.option, comboBoxItem.value]);
								});
								currentFieldset.add({
									xtype: 'combo',
									displayField: 'option',
									valueField: 'value',
									value: item.value,
									allowBlank: allowBlank,
									width: 150,
									forceSelection: true,
									store: new Ext.data.SimpleStore({
								        fields: ['option', 'value'],
								        data: store
								    }),
									mode: 'local',
									triggerAction: 'all',
									fieldLabel: item.fieldLabel,
									name: 'extraconfig_'+item.key
								});
								break;
							// radio case does not work jet on save we get a recusion error (too much recursion)
							case 'radio':
								var radios = new Array();
								Ext.each(item.values, function(radioItem, radioKey) {
									var checked = false;
									if (radioItem.value == item.value) {
										var checked = true;	
									}
									radios.push(new Ext.form.Radio({
										inputValue: radioItem.value,
										name: 'extraconfig_'+item.key,
										boxLabel: radioItem.option,
										checked: checked
									}));
								});
								currentFieldset.add({
									xtype: 'radiogroup',
									fieldLabel: item.fieldLabel,
									items: radios
								});
								break;
							case 'checkbox':
								currentFieldset.add({
									xtype: 'checkbox',
									name: 'extraconfig_'+item.key,
									fieldLabel: item.fieldLabel,
									value: true,
									checked: item.value
								});
								break;	
						}
						
					}, this);
					
					Ext.getCmp('extra-info-form').add(currentFieldset);	
				}
				
				// Set form values
				//this.newModuleForm.getForm().setValues(moduleConfig);
				//this.newModuleWindow.show();
				
				this.newProductForm.doLayout(false, true);
				Ext.getCmp('extra-info-form').doLayout(false, true);
			}
		});
	},
	loadCustomTabs: function() {
		vcCore.ajax.request({
			url: vcCore.config.connectorUrl,
			params: {
				action: 'mgr/products/getcustomtabs'
			},
			scope: this,
			success: function(response) {
				var customTabs = Ext.decode(response.responseText);
				customTabs = customTabs.object;
				
				var tabPanel = Ext.getCmp('vc-product-tab');
				
				if(customTabs.length > 0) {
					var count = 5;
					Ext.each(customTabs, function(item, key) {
						item.autoLoad = {
							url: vcCore.config.connectorUrl+'?action=mgr/products/getcustomtab&id='+item.id+'&tab='+count+'&prodid='+vcCore.getUrlVar('prodid'),
							scripts: true
						}
						tabPanel.add(item);
						count += 1;
					}, this);
					
					tabPanel.setActiveTab(4);
					tabPanel.setActiveTab(0);
					/*// Preload all tabs
					tabPanel.items.each(function(item, key) {
						if (key > 3) {
							tabPanel.setActiveTab(key);
						}
					});
					tabPanel.setActiveTab(0);*/
				}
			}
		});
	},
	addField: function(field, tab) {
		var currentTab = Ext.getCmp('vc-product-tab').getItem(tab);
		
		var returnValue = currentTab.add(field);
		this.newProductForm.doLayout(false, true);
		currentTab.doLayout(false, true);
		
		return returnValue;
	},
	addFields: function(fields, tab) {
		Ext.each(fields, function(item) {
			var item = this.addField(item, tab);
			if (item.items) {
				item.doLayout(false, true);
			}
		}, this);	
	},
	saveProduct: function(callBack) {
		var formData = this.newProductForm.getForm().getFieldValues();
		formData.price = Ext.getCmp('product-price').getValue();
		
		var postData = {
			formData: Ext.encode(formData),
			shopId: vcCore.getUrlVar('shopid'),
			action: 'mgr/products/save',
			sku: vcCore.getUrlVar('sku')
		}
		
		vcCore.ajax.request({
			url: vcCore.config.connectorUrl,
			params: postData,
			scope: this,
			success: function() {
				if (callBack) {
					callBack();	
				}
				this.skuGrid.getStore().load();
			}
		});
	},
	unlockActive: function() {
		Ext.getCmp('product-active').show();	
		Ext.getCmp('product-active-lock').hide();
	},
	lockActive: function() {
		Ext.getCmp('product-active').hide();	
		Ext.getCmp('product-active-lock').show();
	}
});

Ext.onReady(function() {
	// Set page title and load main panel
	vcCore.setTitle(vcCore.config.currentShop+' - Product Properties');
	
	// this makes the main class accessible through vcCore.pageClass and the panel through vcCore.pagePanel
	vcCore.loadPanel(vcPageProduct); 
	
	vcCore.pageClass.newProductForm.getForm().clearInvalid();
});