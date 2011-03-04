var vcPageOptions = Ext.extend(Ext.Panel, {
	initComponent: function() {
		vcCore.stores.options.baseParams.shopId = vcCore.getUrlVar('shopid');
		
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
								this.initTabs('update');
								
								vcCore.stores.optionvalues.baseParams.optionId = this.rowMenu.baseParams.rowId;
								this.optionValueGrid.getStore().load();
								
								vcCore.ajax.request({
									url: vcCore.config.connectorUrl,
									params: {
										id: this.rowMenu.baseParams.rowId,
										action: 'mgr/options/get'
									},
									scope: this,
									success: function(response) {
										var optionConfig = Ext.decode(response.responseText);
										optionConfig = optionConfig.object;
										
										// Set form values
										this.newOptionForm.getForm().setValues(optionConfig);
										
					    				this.newOptionWindow.show();
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
									title: 'Remove option?',
									msg: 'All of the <b>values will be removed</b> as well as <b>every link to any products that this option has</b>, continue?',
									buttons: Ext.Msg.YESNO,
									fn: function(response) {
										if (response == 'yes') {
											vcCore.ajax.request({
												url: vcCore.config.connectorUrl,
												params: {
													id: this.rowMenu.baseParams.rowId,
													action: 'mgr/options/remove'
												},
												scope: this,
												success: function(response) {
													this.optionsGrid.getStore().load();
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
		
		this.optionValuesMenu = new Ext.menu.Menu({
			baseParams: {
				rowId: ''
			},
			items: [
				{
					text: 'Update',
					scope: this,
					handler: function() {
						vcCore.ajax.request({
							url: vcCore.config.connectorUrl,
							params: {
								id: this.optionValuesMenu.baseParams.id,
								action: 'mgr/options/getoptionvalue'
							},
							scope: this,
							success: function(response) {
								var extraField = Ext.decode(response.responseText);
								
								this.newOptionForm.getForm().setValues({
									'value': extraField.object.value
								});

								Ext.getCmp('value-save').show();
								Ext.getCmp('value-cancel').show();
								Ext.getCmp('value-add').hide();
							}
						});
					}	
				},
				{
					text: 'Remove',
					scope: this,
					handler: function() {
						Ext.Msg.show({
							title: 'Remove option value?',
							msg: 'Are you sure you want to remove this option value?',
							buttons: Ext.Msg.YESNO,
							fn: function(response) {
								if (response == 'yes') {
									vcCore.ajax.request({
										url: vcCore.config.connectorUrl,
										params: {
											id: this.optionValuesMenu.baseParams.id,
											action: 'mgr/options/removeoptionvalue'
										},
										scope: this,
										success: function(response) {
											this.optionValueGrid.getStore().load();
										}
									});
								}
							},
							icon: Ext.MessageBox.QUESTION,
							scope: this
						});
					}	
				}
			]
		});
		
		this.optionValueGrid = new Ext.grid.GridPanel({
			store: vcCore.stores.optionvalues,
			loadMask: true,
			viewConfig: {
	            forceFit: true,
	            enableRowBody: true,
	            autoFill: true,
	            showPreview: true,
	            scrollOffset: 0,
	            deferEmptyText: false,
	            emptyText: _('ext_emptymsg')
	        },
			height: 150,
			autoExpandColumn: 'field-name',
		    columns: [
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'value',
	                id: 'field-name',
	                header: 'Option value'
	            }
	        ],
		    listeners: {
		    	rowContextMenu: {
		    		scope: this,
		    		fn: function(grid, rowIndex, event) {
		    			// Set the database ID in the menu's base params so we can access it when an action is performed
		    			this.optionValuesMenu.baseParams.id = grid.getStore().getAt(rowIndex).get('id');
		    			this.optionValuesMenu.showAt(event.xy);
		    			event.stopEvent();
		    		}
		    	}
		    }		    
		});
		
		this.newOptionForm = new Ext.form.FormPanel({
			plugins: [
				new Ext.ux.FormClear()
			],
			border: false,
			labelWidth: 150,
			monitorValid: true,
			buttons: [
				{
					text: 'Cancel',
					scope: this,
					handler: function() {
						this.newOptionWindow.hide();	
					}
				},
				{
					text: 'Save',
					formBind: true,
					scope: this,
					handler: function() {
						var postData = {
							formData: Ext.encode(this.newOptionForm.getForm().getFieldValues()),
							shopId: vcCore.getUrlVar('shopid'),
							action: 'mgr/options/save'
						}
						
						vcCore.ajax.request({
							url: vcCore.config.connectorUrl,
							params: postData,
							scope: this,
							success: function() {
								this.optionsGrid.getStore().load();
								this.newOptionWindow.hide();
							}
						});
					}
				}
			],
			items: [
				{
					xtype: 'tabpanel',
					plain: true,
					id: 'vc_shops_tab',
					deferredRender: false,
					padding: 10,
					activeTab: 0,
					defaults: {
						autoHeight: true
					},
					items: [
						{
							layout: 'form',
							title: 'Option',
							items: [
								{
									xtype: 'hidden',
									name: 'id',
									id: 'option-id'
								},
								{
									xtype: 'textfield',
									fieldLabel: 'Option name',
									name: 'name',
									allowBlank: false
								},
								{
									xtype: 'combo',
									displayField: 'name',
									valueField: 'id',
									width: 200,
									forceSelection: true,
									store: vcCore.stores.inputsnippets,
									mode: 'remote',
									triggerAction: 'all',
									fieldLabel: 'Input snippet',
									name: 'inputsnippet'
								},
								{
									xtype: 'combo',
									displayField: 'name',
									valueField: 'id',
									width: 200,
									forceSelection: true,
									store: vcCore.stores.outputsnippets,
									mode: 'remote',
									triggerAction: 'all',
									fieldLabel: 'Output snippet',
									name: 'outputsnippet'
								}
							]
						},
						{
							layout: 'form',
							title: 'Option values',
							items: [
								{
									xtype: 'fieldset',
									title: 'New option',
									collapsible: true,
									items: [
										{
											xtype: 'textarea',
											fieldLabel: 'Value',
											name: 'value',
											anchor: '100%'
										},
										{
											xtype: 'toolbar',
											items: [
									            {
									            	xtype: 'button',
									            	text: 'Add value',
									            	id: 'value-add',
									            	scope: this,
									            	handler: function() {
														var postData = {
															formData: Ext.encode(this.newOptionForm.getForm().getFieldValues()),
															shopId: vcCore.getUrlVar('shopid'),
															optionId: this.rowMenu.baseParams.rowId,
															action: 'mgr/options/saveoptionvalue'
														}
														
														vcCore.ajax.request({
															url: vcCore.config.connectorUrl,
															params: postData,
															scope: this,
															success: function() {
																this.optionValueGrid.getStore().load();
																vcCore.showMessage('Option value saved.');
															}
														});	
									            	}
									            },
									            {
									            	xtype: 'button',
									            	id: 'value-cancel',
									            	text: 'Cancel',
									            	hidden: true,
									            	scope: this,
									            	handler: function() {
														Ext.getCmp('value-save').hide();
														Ext.getCmp('value-cancel').hide();
														Ext.getCmp('value-add').show();
									            	}
									            },
									            {
									            	xtype: 'button',
									            	id: 'value-save',
									            	text: 'Save',
									            	hidden: true,
									            	scope: this,
									            	handler: function() {
														var postData = {
															formData: Ext.encode(this.newOptionForm.getForm().getFieldValues()),
															shopId: vcCore.getUrlVar('shopid'),
															optionId: this.rowMenu.baseParams.rowId,
															valueId: this.optionValuesMenu.baseParams.id,
															action: 'mgr/options/saveoptionvalue'
														}
														
														vcCore.ajax.request({
															url: vcCore.config.connectorUrl,
															params: postData,
															scope: this,
															success: function() {
																this.optionValueGrid.getStore().load();
																Ext.getCmp('value-save').hide();
																Ext.getCmp('value-cancel').hide();
																Ext.getCmp('value-add').show();
															}
														});	
									            	}
									            }
											]
										}
									]
								},
								this.optionValueGrid
							]
						}
					]
				}
			]
		});
		
		this.newOptionWindow = new Ext.Window({
			padding: 10,
			title: 'Option properties',
			width: 750,
			y: 20,
			autoHeight: true,
			closeAction: 'hide',
			items: [
				this.newOptionForm
			]
		});
		
		this.optionsGrid = new Ext.grid.GridPanel({
			store: vcCore.stores.options,
			autoExpandColumn: 'option-name',
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
			tbar: [
				{
					xtype: 'button',
					text: 'New',
					scope: this,
					handler: function() {
						this.initTabs('new');
						this.newOptionForm.getForm().setValues({
							id: 0,
							name: '',
							inputsnippet: '',
							outputsnippet: ''
						});
						this.newOptionForm.getForm().reset();
						Ext.getCmp('option-id').setValue(0);
						this.newOptionWindow.show();	
					}
				}
			],
		    columns: [
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'name',
	                id: 'option-name',
	                header: 'Option name'
	            },
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'inputsnippet',
	                width: 150,
	                header: 'Input snippet'
	            },
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'outputsnippet',
	                width: 150,
	                header: 'Output snippet'
	            }
	        ],
	        listeners: {
	        	added: {
	        		scope: this,
	        		fn: function() {
		        		this.optionsGrid.getStore().load();
		        	}
	        	},
		    	rowContextMenu: {
		    		scope: this,
		    		fn: function(grid, rowIndex, event) {
		    			// Set the database ID in the menu's base params so we can access it when an action is performed
		    			this.rowMenu.baseParams.rowId = vcCore.stores.options.getAt(rowIndex).get('id');
		    			this.rowMenu.showAt(event.xy);
		    			event.stopEvent();
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
				this.optionsGrid
			]
		});
	},
	initTabs: function(type) {
		this.newOptionForm.getComponent(0).setActiveTab(0);
		if (type == 'new') {
			this.newOptionForm.getComponent(0).getComponent(1).disable();
		} else {
			this.newOptionForm.getComponent(0).getComponent(1).enable();
		}	
	}
});

Ext.onReady(function() {
	// Set page title and load main panel
	vcCore.setTitle(vcCore.config.currentShop + ' - Manage options');
	
	// this makes the main class accessible through vcCore.pageClass and the panel through vcCore.pagePanel
	vcCore.loadPanel(vcPageOptions); 
});