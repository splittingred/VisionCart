var vcPageCategories = Ext.extend(Ext.Panel, {
	initComponent: function() {
		this.expanded = [];
		this.oldParent = false;
		this.newParent = false;
		this.currentNode = false;
		
		this.treeMenu = new Ext.menu.Menu({
			baseParams: {
				parent: 'category:0|parent:0'
			},
			items: [
				{
					text: 'New category',
					scope: this,
					handler: function() {
						this.newCategoryWindow.show();
						this.newCategoryForm.getForm().reset();
						Ext.getCmp('vc_category_id').setValue(0);
	    				this.newCategoryForm.getForm().setValues({
	    					extra_type: 'textfield',
	    					active: true,
	    					pricechange: 0,
	    					pricechangepercent: 0,
	    					resource: 0,
	    					chunk: 0
	    				});
	    				
	    				this.newCategoryForm.getComponent(3).getComponent(1).disable();
	    				this.newCategoryForm.getComponent(3).getComponent(2).disable();
	    				this.newCategoryForm.getComponent(3).getComponent(3).disable();					
					}	
				},
				{
					text: 'Update category',
					scope: this,
					handler: function() {
						vcCore.ajax.request({
							url: vcCore.config.connectorUrl,
							params: {
								id: this.treeMenu.baseParams.parent,
								action: 'mgr/categories/get'
							},
							scope: this,
							success: function(response) {
								var categoryConfig = Ext.decode(response.responseText);
								categoryConfig = categoryConfig.object;
								
								// Set form values
								this.newCategoryForm.getForm().setValues(categoryConfig);
								this.newCategoryForm.getForm().setValues(categoryConfig.config);
								
								this.newCategoryForm.getForm().setValues({
									extra_type: 'textfield',
									mandatory: 1
								});
								this.newCategoryForm.getComponent(3).getComponent(1).enable();
								this.newCategoryForm.getComponent(3).getComponent(2).enable();
								this.newCategoryForm.getComponent(3).getComponent(3).enable();

			    				this.newCategoryWindow.show();
							}
						});
					}
				},
				{
					text: 'Remove category',
					scope: this,
					handler: function() {
						Ext.Msg.show({
							title: 'Remove category?',
							msg: 'Removing this category will <b>remove all subcategories</b> and <b>remove all values from the extra fields</b> as well, continue?',
							buttons: Ext.Msg.YESNO,
							fn: function(response) {
								if (response == 'yes') {
									vcCore.ajax.request({
										url: vcCore.config.connectorUrl,
										params: {
											categoryId: this.treeMenu.baseParams.parent,
											action: 'mgr/categories/remove'
										},
										scope: this,
										success: function(response) {
											this.categoryTree.getRootNode().reload();
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

		this.extraFieldsMenu = new Ext.menu.Menu({
			baseParams: {
				fieldName: ''
			},
			items: [
				{
					text: 'Update',
					scope: this,
					handler: function() {
						vcCore.ajax.request({
							url: vcCore.config.connectorUrl,
							params: {
								categoryId: this.treeMenu.baseParams.parent,
								field: this.extraFieldsMenu.baseParams.fieldName,
								action: 'mgr/categories/getextrafield'
							},
							scope: this,
							success: function(response) {
								var extraField = Ext.decode(response.responseText);
								Ext.getCmp('extra-name').setValue(extraField.object.name);
								Ext.getCmp('extra-type').setValue(extraField.object.type);
								Ext.getCmp('extra-mandatory').setValue(extraField.object.mandatory);
								Ext.getCmp('extra-values').setValue(extraField.object.values);	
								
								if (extraField.object.type == 'combobox') {
									Ext.getCmp('extra-values').showItem();	
					    			Ext.getCmp('extra-values').show();		
								} else {
									Ext.getCmp('extra-values').hideItem();	
					    			Ext.getCmp('extra-values').hide();			
								}
								
								Ext.getCmp('vc_extrafield_name').setValue(extraField.object.name);
								
								Ext.getCmp('extra-field-cancel').show();
								Ext.getCmp('extra-field-save').show();
								Ext.getCmp('extra-field-add').hide();
							}
						});
					}	
				},
				{
					text: 'Remove',
					scope: this,
					handler: function() {
						Ext.Msg.show({
							title: 'Remove category field?',
							msg: 'Removing this field also <b>removes the value</b> filled in for each product in this category, continue?',
							buttons: Ext.Msg.YESNO,
							fn: function(response) {
								if (response == 'yes') {
									vcCore.ajax.request({
										url: vcCore.config.connectorUrl,
										params: {
											categoryId: this.treeMenu.baseParams.parent,
											name: this.extraFieldsMenu.baseParams.fieldName,
											action: 'mgr/categories/removeextrafield'
										},
										scope: this,
										success: function(response) {
											this.extraFieldsGrid.getStore().load();
											vcCore.showMessage('Field removed.');
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
		
		this.categoryConfigMenu = new Ext.menu.Menu({
			baseParams: {
				index: 0
			},
			items: [
				{
					text: 'Update',
					scope: this,
					handler: function() {
						var key = this.categoryConfigFields.getStore().getAt(this.categoryConfigMenu.baseParams.index).get('key');
						var value = this.categoryConfigFields.getStore().getAt(this.categoryConfigMenu.baseParams.index).get('value');
						
						Ext.getCmp('vc_configfield_name').setValue(key);
						Ext.getCmp('config-key').setValue(key);
						Ext.getCmp('config-value').setValue(value);
						Ext.getCmp('config-field-cancel').show();
						Ext.getCmp('config-field-save').show();
						Ext.getCmp('config-field-add').hide();
					}	
				},
				{
					text: 'Remove',
					scope: this,
					handler: function() {
						Ext.Msg.show({
							title: 'Remove category config field?',
							msg: 'Are you sure?',
							buttons: Ext.Msg.YESNO,
							fn: function(response) {
								if (response == 'yes') {
									vcCore.ajax.request({
										url: vcCore.config.connectorUrl,
										params: {
											categoryId: this.treeMenu.baseParams.parent,
											key: this.categoryConfigFields.getStore().getAt(this.categoryConfigMenu.baseParams.index).get('key'),
											action: 'mgr/categories/removeconfigfield'
										},
										scope: this,
										success: function(response) {
											this.categoryConfigFields.getStore().load();
											vcCore.showMessage('Field removed.');
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
		
		this.extraFieldsGrid = new Ext.grid.GridPanel({
			store: vcCore.stores.categoryExtraFields,
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
			height: 150,
			autoExpandColumn: 'field-name',
		    columns: [
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'name',
	                id: 'field-name',
	                header: 'Field name'
	            },
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'type',
	                header: 'Field type'
	            },
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'mandatory',
	                header: 'Mandatory',
	                renderer: function(value) {
	                	if (value == 1) {
	                		return 'Yes';	
	                	}	
	                	return 'No';
	                }
	            }
	        ],
		    listeners: {
		    	rowContextMenu: {
		    		scope: this,
		    		fn: function(grid, rowIndex, event) {
		    			// Set the database ID in the menu's base params so we can access it when an action is performed
		    			this.extraFieldsMenu.baseParams.fieldName = grid.getStore().getAt(rowIndex).get('name');
		    			this.extraFieldsMenu.showAt(event.xy);
		    			event.stopEvent();
		    		}
		    	}
		    }		    
		});
		
		this.categoryConfigFields = new Ext.grid.GridPanel({
			style: {
				marginTop: '10px'
			},
			store: vcCore.stores.categoryConfigFields,
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
			height: 150,
			autoExpandColumn: 'config-field-name',
		    columns: [
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'key',
	                id: 'config-field-name',
	                header: 'Key'
	            },
	            {
	                xtype: 'gridcolumn',
	                dataIndex: 'value',
	                header: 'Value'
	            }
	        ],
		    listeners: {
		    	rowContextMenu: {
		    		scope: this,
		    		fn: function(grid, rowIndex, event) {
		    			// Set the database ID in the menu's base params so we can access it when an action is performed
		    			this.categoryConfigMenu.baseParams.index = rowIndex;
		    			this.categoryConfigMenu.showAt(event.xy);
		    			event.stopEvent();
		    		}
		    	}
		    }		    
		});
		
		this.newCategoryForm = new Ext.form.FormPanel({
			plugins: [
				new Ext.ux.FormClear()
			],
			fileUpload: true,
			border: false,
			labelWidth: 150,
			monitorValid: true,
			buttons: [
				{
					text: 'Cancel',
					scope: this,
					handler: function() {
						this.newCategoryWindow.hide();	
					}
				},
				{
					text: 'Save',
					formBind: true,
					scope: this,
					handler: function() {
						var parent = this.treeMenu.baseParams.parent;
						var categoryConfig = Ext.encode(this.newCategoryForm.getForm().getFieldValues());
						
						var postData = {
							parent: parent,
							categoryConfig: categoryConfig,
							shop: vcCore.getUrlVar('shopid'),
							action: 'mgr/categories/save'
						}
						
						vcCore.ajax.request({
							url: vcCore.config.connectorUrl,
							params: postData,
							scope: this,
							success: function() {
								this.categoryTree.getRootNode().reload();
								this.newCategoryWindow.hide();
							}
						});
					}
				}
			],
			items: [
				{
					xtype: 'hidden',
					id: 'vc_category_id',
					name: 'id',
					defaultValue: 0	
				},
				{
					xtype: 'hidden',
					id: 'vc_extrafield_name',
					name: 'field_oldname',
					defaultValue: ''	
				},
				{
					xtype: 'hidden',
					id: 'vc_configfield_name',
					name: 'configfield_oldname',
					defaultValue: ''	
				},
				{
					xtype: 'tabpanel',
					plain: true,
					id: 'vc_categories_tab',
					deferredRender: false,
					padding: 10,
					activeTab: 0,
					defaults: {
						autoHeight: true						
					},
					items: [
						{
							title: 'Category details',
							layout: 'form',
							autoHeight: true,
							items: [
								{
									xtype: 'fieldset',
									title: 'General',
									autoHeight: true,
									items: [
										{
											xtype: 'textfield',
											fieldLabel: 'Category name',
											name: 'name',
											allowBlank: false,
											listeners: {
												blur: {
													scope: this,
													fn: function(field) {
														if (Ext.getCmp('category-alias').getValue() == '') {
															Ext.getCmp('category-alias').setValue(vcCore.createAlias(field.getValue()));
														}
													}	
												}
											}
										},
										{
											xtype: 'textfield',
											fieldLabel: 'Category alias',
											id: 'category-alias',
											name: 'alias',
											allowBlank: false
										},
										{
											xtype: 'htmleditor',
											fieldLabel: 'Category description',
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
												'Verdana'
											],
											plugins: [
												new Ext.ux.form.HtmlEditor.Formatblock()
											]
										},
										{
											xtype: 'checkbox',
											fieldLabel: 'Active',
											name: 'active'
										}
									]
								},
								{
									xtype: 'fieldset',
									title: 'Product price changes',
									autoHeight: true,
									items: [
										{
											xtype: 'textfield',
											fieldLabel: 'Price change',
											name: 'pricechange',
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
										        	['By value', 0],
										        	['By percentage', 1]
										        ]
										    }),
											mode: 'local',
											triggerAction: 'all',
											fieldLabel: 'Price modifier',
											name: 'pricepercent'
										}	
									]
								}
							]
						},
						{
							title: 'Category config',
							layout: 'form',
							autoHeight: true,
							items: [
								{
									xtype: 'combo',
									displayField: 'pagetitle',
									valueField: 'id',
									width: 200,
									forceSelection: true,
									store: vcCore.stores.resources,
									mode: 'remote',
									triggerAction: 'all',
									fieldLabel: 'Resource',
									allowBlank: false,
									name: 'resource'
								},
								{
									xtype: 'combo',
									displayField: 'name',
									valueField: 'id',
									width: 200,
									forceSelection: true,
									store: vcCore.stores.chunks,
									mode: 'remote',
									triggerAction: 'all',
									fieldLabel: 'Default chunk',
									allowBlank: false,
									name: 'chunk'
								},
								{
									xtype: 'fieldset',
									title: 'Custom fields',
									items: [
										{
											xtype: 'textfield',
											fieldLabel: 'Key',
											name: 'config_key',
											id: 'config-key'
										},
										{
											xtype: 'textarea',
											fieldLabel: 'Value',
											anchor: '100%',
											name: 'config_value',
											id: 'config-value'
										},
										{
											xtype: 'toolbar',
											items: [
												{
													xtype: 'button',
													text: 'Add',
													id: 'config-field-add',
													scope: this,
													handler: function() {
														if (Ext.getCmp('config-key').getValue() == '') {
															Ext.Msg.alert('Key is required', 'Enter the config key.');
														} else {
															this.saveConfigField(false);
														}
													}
												},
												{
													xtype: 'button',
													text: 'Cancel',
													id: 'config-field-cancel',
													hidden: true,
													scope: this,
													handler: function() {
														Ext.getCmp('config-key').setValue('');
														Ext.getCmp('config-value').setValue('');
														Ext.getCmp('config-key').reset();
														Ext.getCmp('config-value').reset();
														Ext.getCmp('config-field-cancel').hide();
														Ext.getCmp('config-field-save').hide();
														Ext.getCmp('config-field-add').show();
														
													}
												},
												{
													xtype: 'button',
													text: 'Save',
													id: 'config-field-save',
													hidden: true,
													scope: this,
													handler: function() {
														if (Ext.getCmp('config-key').getValue() == '') {
															Ext.Msg.alert('Key is required', 'Enter the config key.');
														} else {
															Ext.getCmp('config-field-cancel').hide();
															Ext.getCmp('config-field-save').hide();
															Ext.getCmp('config-field-add').show();
															
															this.saveConfigField(true);
														}
													}
												}
											]
										},
										this.categoryConfigFields
									]
								}
							],
							listeners: {
								activate: {
									scope: this,
									fn: function() {
										this.categoryConfigFields.getStore().baseParams.categoryId = this.treeMenu.baseParams.parent;
										this.categoryConfigFields.getStore().load();
									}	
								}
							}
						},
						{
							title: 'Category image',
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
														this.newCategoryForm.getForm().getEl().set({
															target: 'file-upload', 
															action: '/assets/components/visioncart/connector.php?action=mgr/categories/fileupload&HTTP_MODAUTH='+vcCore.siteId+'&shopid='+vcCore.getUrlVar('shopid')
														});
														this.newCategoryForm.getForm().getEl().dom.submit();
														
														Ext.Msg.wait('Uploading image...', 'Please wait');
													}	
												}	
											}
										}
									]
								},
								{
									xtype: 'fieldset',
									title: 'Current image',
									autoHeight: true,
									items: [
										{
											xtype: 'panel',
											id: 'category-image-panel',
											html: '<div id="current-category-image"></div>'
										},
										{
											xtype: 'button',
											style: {
												marginTop: '5px'
											},
											text: 'Remove image',
											handler: function() {
												Ext.Msg.show({
													title: 'Remove image?',
													msg: 'All of the associated thumbnails will be deleted as well, continue?',
													buttons: Ext.Msg.YESNO,
													fn: function(response) {
														if (response == 'yes') {
															vcCore.ajax.request({
																url: vcCore.config.connectorUrl,
																params: {
																	action: 'mgr/categories/removeimage',
																	shopid: vcCore.getUrlVar('shopid'),
																	id: Ext.getCmp('vc_category_id').getValue()
																},
																scope: this,
																success: function(response) {
																	this.getCategoryImage();
																}
															});
														}
													},
													icon: Ext.MessageBox.QUESTION,
													scope: this
												});
											},
											scope: this	
										}
									]
								}
							],
							listeners: {
								activate: {
									scope: this,
									fn: function() {
										this.getCategoryImage();
									}	
								}	
							}
						},
						{
							title: 'Extra fields',
							layout: 'form',
							autoHeight: true,
							items: [
								{
									xtype: 'fieldset',
									title: 'Create a new field',
									collapsible: true,
									items: [
										{
											xtype: 'textfield',
											fieldLabel: 'Field name',
											name: 'extra_name',
											id: 'extra-name'
										},
										{
											xtype: 'combo',
											displayField: 'option',
											valueField: 'value',
											id: 'extra-type',
											width: 150,
											forceSelection: true,
											store: new Ext.data.SimpleStore({
										        fields: ['option', 'value'],
										        data: [
										        	['Textfield', 'textfield'],
										        	['Textarea', 'textarea'],
										        	['HTML editor', 'htmleditor'],
										        	['Checkbox', 'checkbox'],
										        	['Combobox', 'combobox']
										        ]
										    }),
										    listeners: {
										    	select: function(combo, record) {
										    		if (record.get('value') == 'combobox') {
										    			Ext.getCmp('extra-values').showItem();	
										    			Ext.getCmp('extra-values').show();	
										    		} else {
										    			Ext.getCmp('extra-values').hideItem();	
										    			Ext.getCmp('extra-values').hide();	
										    		}
										    	}	
										    },
											mode: 'local',
											triggerAction: 'all',
											fieldLabel: 'Field type',
											name: 'extra_type',
											description: 'When using the type "Combobox" you can use the <b>@SNIPPET</b> binding.<br /><br />This will execute the snippet and split the returned value on newlines to use as values. (For example: @SNIPPET getValues).<br /><br />The snippet will receive the parameters "product" which contains the xPDO object with the corresponding product.'
										},
										{
											xtype: 'textarea',
											fieldLabel: 'Values',
											anchor: '100%',
											name: 'extra_values',
											id: 'extra-values',
											hidden: true
										},
										{
											xtype: 'checkbox',
											fieldLabel: 'Mandatory',
											id: 'extra-mandatory',
											name: 'extra_mandatory'
										},
										{
											xtype: 'toolbar',
											items: [
												{
													xtype: 'button',
													text: 'Add field',
													id: 'extra-field-add',
													scope: this,
													handler: function() {
														if (Ext.getCmp('extra-name').getValue() == '') {
															Ext.Msg.alert('Name is required', 'Fill in the field\'s name!');
														} else {
															this.saveExtraField(false);
														}
													}
												},
												{
													xtype: 'button',
													text: 'Cancel',
													id: 'extra-field-cancel',
													hidden: true,
													scope: this,
													handler: function() {
														Ext.getCmp('extra-field-cancel').hide();
														Ext.getCmp('extra-field-save').hide();
														Ext.getCmp('extra-field-add').show();
														Ext.getCmp('extra-name').setValue('');
														Ext.getCmp('extra-type').setValue('');
														Ext.getCmp('extra-mandatory').setValue(false);
														this.newCategoryForm.getForm().reset();
													}
												},
												{
													xtype: 'button',
													text: 'Save',
													id: 'extra-field-save',
													hidden: true,
													scope: this,
													handler: function() {
														if (Ext.getCmp('extra-name').getValue() == '') {
															Ext.Msg.alert('Name is required', 'Fill in the field\'s name!');
														} else {
															Ext.getCmp('extra-field-cancel').hide();
															Ext.getCmp('extra-field-save').hide();
															Ext.getCmp('extra-field-add').show();
															
															this.saveExtraField(true);
														}
													}
												}
											]
										}
									]
								},
								{
									xtype: 'fieldset',
									title: 'Current fields',
									items: [
										this.extraFieldsGrid
									]	
								}
							],
							listeners: {
								activate: {
									scope: this,
									fn: function() {
										this.extraFieldsGrid.getStore().baseParams.categoryId = this.treeMenu.baseParams.parent;
										this.extraFieldsGrid.getStore().load();
									}	
								}
							}
						}
					]
				}
			]
		});
		
		this.newCategoryWindow = new Ext.Window({
			padding: 10,
			title: 'Category properties',
			width: 750,
			modal: true,
			autoHeight: true,
			closeAction: 'hide',
			
			items: [
				this.newCategoryForm
			],
			listeners: {
				show: {
					scope: this,
					fn: function() {
						Ext.getCmp('vc_categories_tab').setActiveTab(0);	
						Ext.getCmp('extra-values').hideItem();	
		    			Ext.getCmp('extra-values').hide();	
					}	
				}	
			}
		});
		
		this.categoryTree = new Ext.tree.TreePanel({
		    dataUrl: vcCore.config.connectorUrl+'?action=mgr/categories/getnodes&shop='+vcCore.getUrlVar('shopid'),
			border: false,
	        autoScroll: true,
	        animate: false,
	        enableDD: true,
	        useArrows: true,
	        containerScroll: true,
			root: {
		        nodeType: 'async',
		        text: 'Webshop root',
		        draggable: false,
		        id: 'category:0|parent:0'
		    },
		    listeners: {
		    	movenode: {
		    		scope: this,
		    		fn: function(tree, node, oldParent, newParent, index) {
			    		// Calculate the place where the category should be
			    		var currentTarget = 0;
			    		this.oldParent = oldParent;
						this.newParent = newParent;
						this.currentNode = node;
						
			    		newParent.eachChild(function(item) {
			    			if (item == node) {
			    				return false;
			    			}
			    			currentTarget += 1;
			    		}, this);
			    		
			    		vcCore.ajax.request({
							url: vcCore.config.connectorUrl,
							params: {
								sourceId: node.id,
								targetId: newParent.id,
								targetSort: currentTarget,
								action: 'mgr/categories/savenodes'
							},
							scope: this,
							success: function(response) {
								this.categoryTree.getRootNode().reload();
							}
						});
		    		}	
		    	},
		    	expandnode: {
		    		scope: this,
		    		fn: function(node) { 
		    			this.categoryTreeSorter.doSort(node);
		    		}
		    	},
		    	checkchange: function(node, checked) {
		    		vcCore.ajax.request({
						url: vcCore.config.connectorUrl,
						params: {
							id: node.id,
							checked: checked,
							action: 'mgr/categories/updatenode'
						},
						scope: this,
						success: function(response) {

						}
					});
		    	},
		    	contextmenu: {
		    		scope: this,
		    		fn: function(node, event) {
			    		event.stopEvent();
			    		this.treeMenu.baseParams.parent = node.id;
			    		this.treeMenu.baseParams.node = node;
		    			this.treeMenu.showAt(event.xy);	
			    	}
		    	}
		    }
		});
		
		this.categoryTreeSorter = new Ext.tree.TreeSorter(this.categoryTree, {
			folderSort: true,
			dir: 'asc',
			property: 'sort',
			sortType: function(sortValue) {
				return sortValue;	
			}
		});
		
		this.categoryTree.getRootNode().expand();
		
		// The mainpanel always has to be in the "this.mainPanel" variable
		this.mainPanel = new Ext.Panel({
			renderTo: 'visioncart-content',
			padding: 15,
			layout: 'anchor',
			border: true,
			items: [
				this.categoryTree
			]
		});
	},
	saveExtraField: function(isUpdate) {
		if (isUpdate) {
			var oldField = Ext.getCmp('vc_extrafield_name').getValue();
		} else {
			var oldField = '';
		}
		
		var postData = {
			oldField: oldField,
			isUpdate: isUpdate,
			name: Ext.getCmp('extra-name').getValue(),
			type: Ext.getCmp('extra-type').getValue(),
			values: Ext.getCmp('extra-values').getValue(),
			mandatory: Ext.getCmp('extra-mandatory').getEl().dom.checked,
			action: 'mgr/categories/addextrafield',
			categoryId: this.treeMenu.baseParams.parent
		}
		
		vcCore.ajax.request({
			url: vcCore.config.connectorUrl,
			params: postData,
			scope: this,
			success: function() {
				this.extraFieldsGrid.getStore().load();
				Ext.getCmp('extra-name').setValue('');
				Ext.getCmp('extra-type').setValue('');
				Ext.getCmp('extra-values').setValue('');
				Ext.getCmp('extra-mandatory').setValue(false);

				Ext.getCmp('extra-name').reset();
				Ext.getCmp('extra-type').reset();
				Ext.getCmp('extra-values').reset();
				Ext.getCmp('extra-mandatory').reset();
				
				if (isUpdate) {
					vcCore.showMessage('Field updated.');
				} else {
					vcCore.showMessage('Field saved.');
				}
			}
		});	
	},
	saveConfigField: function(isUpdate) {
		if (isUpdate) {
			var oldField = Ext.getCmp('vc_configfield_name').getValue();
		} else {
			var oldField = '';
		}
		
		var postData = {
			oldField: oldField,
			isUpdate: isUpdate,
			key: Ext.getCmp('config-key').getValue(),
			value: Ext.getCmp('config-value').getValue(),
			action: 'mgr/categories/addconfigfield',
			categoryId: this.treeMenu.baseParams.parent
		}
		
		vcCore.ajax.request({
			url: vcCore.config.connectorUrl,
			params: postData,
			scope: this,
			success: function() {
				this.categoryConfigFields.getStore().load();
				Ext.getCmp('config-key').setValue('');
				Ext.getCmp('config-value').setValue('');
				Ext.getCmp('config-key').reset();
				Ext.getCmp('config-value').reset();
			
				if (isUpdate) {
					vcCore.showMessage('Field updated.');
				} else {
					vcCore.showMessage('Field saved.');
				}
			}
		});	
	},
	hideUploader: function() {
		Ext.Msg.hide();	
		this.getCategoryImage();
	},
	getCategoryImage: function() {
		vcCore.ajax.request({
			url: vcCore.config.connectorUrl,
			params: {
				action: 'mgr/categories/getimage',
				id: Ext.getCmp('vc_category_id').getValue(),
				shopid: vcCore.getUrlVar('shopid')
			},
			scope: this,
			success: function(response) {
				var images = response.responseText;
				Ext.get('current-category-image').update(images);
			}
		});
	}
});

Ext.onReady(function() {
	// Set page title and load main panel
	vcCore.setTitle(vcCore.config.currentShop + ' - Categories');
	
	// this makes the main class accessible through vcCore.pageClass and the panel through vcCore.pagePanel
	vcCore.loadPanel(vcPageCategories); 
	
	// Preload stores
	vcCore.stores.resources.load();
	vcCore.stores.chunks.load();
});