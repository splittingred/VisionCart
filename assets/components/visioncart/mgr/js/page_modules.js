var vcPageModules = Ext.extend(Ext.Panel, {
	initComponent: function() {
		this.currentTab = 'payment';
		
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
										action: 'mgr/modules/get'
									},
									scope: this,
									success: function(response) {
										var moduleConfig = Ext.decode(response.responseText);
										moduleConfig = moduleConfig.object;
										
										var extraConfig = moduleConfig.extraConfig;
										
										if(extraConfig.length > 0) {
											this.newModuleForm.getComponent(1).getComponent(1).enable();	
										
											Ext.each(extraConfig, function(item, key) {
												switch(item.type) {
													default:
													case 'textfield':
														var xtype = 'textfield';
														var allowBlank = true;
														if (item.mandatory) {
															allowBlank = false;	
														}
														this.moduleConfigPanel.add({
															xtype: xtype,
															name: 'config_'+item.key,
															value: item.value,
															allowBlank: allowBlank,
															fieldLabel: item.fieldLabel
														});
														break;
													case 'textarea':
														var allowBlank = true;
														if (item.mandatory) {
															allowBlank = false;	
														}
														var xtype = 'textarea';
														this.moduleConfigPanel.add({
															xtype: xtype,
															anchor: '100%',
															value: item.value,
															name: 'config_'+item.key,
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
														this.moduleConfigPanel.add({
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
															name: 'config_'+item.key
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
																name: 'config_'+item.key,
																boxLabel: radioItem.option,
																checked: checked
															}));
														});
														this.moduleConfigPanel.add({
															xtype: 'radiogroup',
															fieldLabel: item.fieldLabel,
															items: radios
														});
														break;
													case 'checkbox':
														this.moduleConfigPanel.add({
															xtype: 'checkbox',
															name: 'config_'+item.key,
															fieldLabel: item.fieldLabel,
															value: true,
															checked: item.value
														});
														break;	
												}
												
											}, this);
										} else {
											this.newModuleForm.getComponent(1).getComponent(1).disable();
										}

										// Set form values
										this.newModuleForm.getForm().setValues(moduleConfig);
					    				this.newModuleWindow.show();
					    				
					    				this.newModuleForm.doLayout(false, true);
					    				this.newModuleForm.getComponent(1).getComponent(1).doLayout();
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
									title: 'Remove module?',
									msg: 'Module will be removed! Continue?',
									buttons: Ext.Msg.YESNO,
									fn: function(response) {
										if (response == 'yes') {
											vcCore.ajax.request({
												url: vcCore.config.connectorUrl,
												params: {
													id: this.rowMenu.baseParams.rowId,
													action: 'mgr/modules/remove'
												},
												scope: this,
												success: function(response) {
													if (this.currentTab == 'payment'){
														vcCore.stores.paymentModules.load();
													} else {
														vcCore.stores.shippingModules.load();
													}
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
		
		// Payment modules grid		
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
			title: 'Payment modules',
		    autoExpandColumn: 'payment-description',
		    tbar: [
		    	{
		    		xtype: 'button',
		    		text: 'New',
		    		scope: this,
		    		handler: function() {
		    			this.newModuleForm.getForm().clearDirty();
		    			this.currentTab = 'payment';
		    			this.moduleConfigPanel.removeAll();
		    			this.newModuleForm.getComponent(1).setActiveTab(0);
		    			this.newModuleForm.getComponent(1).getComponent(1).disable();
		    			
		    			this.newModuleForm.getForm().setValues({
		    				name: '',
		    				description: '',
		    				controller: '',
		    				active: true
		    			});
		    			
		    			//this.newModuleForm.getForm().clear();
		    			this.newModuleForm.getForm().reset();
		    			this.newModuleForm.doLayout(false, true);
		    			
		    			this.newModuleWindow.show();
		    		}	
		    	}
			],
		    columns: [
		    	{
		            xtype: 'gridcolumn',
		            dataIndex: 'id',
		            id: 'payment-id',
		            header: 'id'
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'name',
		            id: 'payment-module',
		            header: 'Module'
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'description',
		            id: 'payment-description',
		            header: 'Description'
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'controller',
		            id: 'payment-controller',
		            header: 'Controller',
		            width: 250
		        },		        
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'active',
		            id: 'payment-active',
		            header: 'Active',
		            renderer: function(value) {
		            	if (value == true) {
		            		return 'Yes';	
		            	}
		            	return 'No';
		            }
		        }
		    ], 
		    listeners: {
		    	activate: {
		    		scope: this,
		    		fn: function() {
		    			this.currentTab = 'payment';
		    		}
		    	},
		    	rowContextMenu: {
			    	scope: this,
		    		fn: function(grid, rowIndex, event) {
		    			// Set the database ID in the menu's base params so we can access it when an action is performed
		    			this.rowMenu.baseParams.rowId = vcCore.stores.paymentModules.getAt(rowIndex).get('id');
		    			this.rowMenu.showAt(event.xy);
		    			event.stopEvent();
		    		}
				},
		    	added: {
		    		scope: this,
		    		fn: function() {
		        		this.paymentGrid.getStore().load();
		        	}
		    	}
		    }
		});
		
		//Shipping modules grid
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
			title: 'Shipping modules',
		    autoExpandColumn: 'shipping-description',
		    tbar: [
		    	{
		    		xtype: 'button',
		    		text: 'New',
		    		scope: this,
		    		handler: function() {
		    			this.newModuleWindow.show();
		    		}	
		    	}
			],
		    columns: [
		    	{
		            xtype: 'gridcolumn',
		            dataIndex: 'id',
		            id: 'shipping-id',
		            header: 'id'
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'name',
		            id: 'shipping-module',
		            header: 'Module'
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'description',
		            id: 'shipping-description',
		            header: 'Description'
		        },
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'controller',
		            id: 'shipping-controller',
		            header: 'Controller',
		            width: 250
		        },		        
		        {
		            xtype: 'gridcolumn',
		            dataIndex: 'active',
		            id: 'payment-active',
		            header: 'Active',
		            renderer: function(value) {
		            	if (value == true) {
		            		return 'Yes';	
		            	}
		            	return 'No';
		            }
		        }
		    ],
		    listeners: {
		    	activate: {
		    		scope: this,
		    		fn: function() {
		    			this.currentTab = 'shipping';
		    		}
		    	},
		    	rowContextMenu: {
			    	scope: this,
		    		fn: function(grid, rowIndex, event) {
		    			// Set the database ID in the menu's base params so we can access it when an action is performed
		    			this.rowMenu.baseParams.rowId = vcCore.stores.shippingModules.getAt(rowIndex).get('id');
		    			this.rowMenu.showAt(event.xy);
		    			event.stopEvent();
		    		}
				},
		    	added: {
		    		scope: this,
		    		fn: function() {
		        		this.shippingGrid.getStore().load();
		        	}
		    	}
		    }
		});
		
		// tabpanel that holds the 2 grids
		this.tabs = new Ext.TabPanel({
		    activeTab: 0,
			plain: true,
			id: 'vc_configuration_tab',
			deferredRender: false,
			padding: 10,
			activeTab: 0,
			autoHeight: true,
		    items: [
		        this.paymentGrid,
		        this.shippingGrid
		    ]
		});	
		
		//Formpanel for both grids
		this.newModuleForm = new Ext.form.FormPanel({
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
						this.newModuleWindow.hide();	
					}
				},
				{
					text: 'Save',
					//formBind: true,
					scope: this,
					handler: function() {
						var postData = {
							formData: Ext.encode(this.newModuleForm.getForm().getFieldValues()),
							currentTab: this.currentTab,						
							action: 'mgr/modules/save'
						}
						
						vcCore.ajax.request({
						url: vcCore.config.connectorUrl,
						params: postData,
						scope: this,
						success: function() {
							if (this.currentTab == 'payment'){
								vcCore.stores.paymentModules.load();	
							} else {
								vcCore.stores.shippingModules.load();
							}
							
							this.newModuleWindow.hide();
						}
						});
					}
				}
			],
			
			items: [
				{
					xtype: 'hidden',
					fieldLabel: 'Id',
					name: 'id',
					id: 'module-id'
				},
				{
					xtype: 'tabpanel',
					plain: true,
					id: 'vc_config_tab',
					deferredRender: false,
					padding: 10,
					activeTab: 0,
					items: [
						{
							title: 'Module settings',
							autoHeight: true,
							layout: 'form',
							items: [
								{
									xtype: 'textfield',
									fieldLabel: 'Module name',
									name: 'name',
									allowBlank: false
								},
								{
									xtype: 'htmleditor',
									fieldLabel: 'Module description',
									name: 'description',
									rows: 4,
									height: 100,
									anchor: '100%',
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
									fieldLabel: 'Controller',
									name: 'controller',
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
							title: 'Configuration', 
							layout: 'form',
							autoHeight: true,
							listeners: {
								added: {
									scope: this,
									fn: function() {
										//this.paymentGrid.getStore().load();
									}
								}
						    },
							items: [
								{
									xtype: 'fieldset',
									title: 'Config',
									layout: 'form',
									autoHeight: true,
									id: 'vc_module_config',
									items: [
																				
									]
								}
							]
						}
					]
				}
			]
		});
		
		this.newModuleWindow = new Ext.Window({
			padding: 10,
			title: 'Module properties',
			width: 750,
			y: 15,
			modal: true,
			closeAction: 'hide',
			listeners: {
				hide: {
					scope: this,
					fn: function() {
						Ext.getCmp('module-id').setValue(0);
						Ext.getCmp('module-id').setValue(0);
						this.moduleConfigPanel.removeAll();
					}	
				},
				show: {
					scope: this,
					fn: function() {	
						this.newModuleForm.getComponent(1).setActiveTab(0);	
					}
				}
			},
			items: [
				this.newModuleForm
			]
		});
		
		this.moduleConfigPanel = this.newModuleForm.getComponent(1).getComponent(1).getComponent(0);
		
		// The mainpanel always has to be in the "this.mainPanel" variable
		this.mainPanel = new Ext.Panel({
			renderTo: 'visioncart-content',
			border: false,
			unstyled: true,
			items: [
				this.tabs		
			]
		});
	}
});

Ext.onReady(function() {
	// Set page title and load main panel
	vcCore.setTitle('Modules');
	
	// this makes the main class accessible through vcCore.pageClass and the panel through vcCore.pagePanel
	vcCore.loadPanel(vcPageModules); 
});