var vcPageProducts = Ext.extend(Ext.Panel, {
	initComponent: function() {

		if (vcCore.config.shop.config.hideSkus) {
			this.defaultDataUrl = vcCore.config.connectorUrl+'?action=mgr/products/getproductnodes&shop='+vcCore.getUrlVar('shopid')+'&hideskus=1';	
			this.skusHidden = 1;
		} else {
			this.skusHidden = 0;	
			this.defaultDataUrl = vcCore.config.connectorUrl+'?action=mgr/products/getproductnodes&shop='+vcCore.getUrlVar('shopid')+'&hideskus=0';	
		}
		
		this.treeMenu = new Ext.menu.Menu({
			baseParams: {
				parent: 'category:0|parent:0',
				node: null
			},
			items: [
				{
					text: 'Update product',
					handler: function() {
						window.location = '?a='+vcCore.getUrlVar('a')+'&action=product&shopid='+vcCore.getUrlVar('shopid')+'&prodid='+this.treeMenu.baseParams.node.attributes.id;
					},
					scope: this
				},
				'-',
				{
					text: 'Remove product from category',
					scope: this,
					handler: function() {
						Ext.Msg.show({
							title: 'Remove product from category?',
							msg: 'Are you sure you want to <b>remove</b> this product <b>from this category</b>?',
							buttons: Ext.Msg.YESNO,
							scope: this,
							fn: function(response) {
								if (response == 'yes') {
									var productId = this.treeMenu.baseParams.parent;
									var parent = this.treeMenu.baseParams.node.getPath();
									parent = parent.split('/');
									this.nodeParent = parent[parent.length-2];
									
									vcCore.ajax.request({
										url: vcCore.config.connectorUrl,
										params: {
											action: 'mgr/products/updatecategory',
											prodid: productId,
											shopid: vcCore.getUrlVar('shopid'),
											parent: this.nodeParent,
											categoryView: true
										},
										scope: this,
										success: function(response) {
											var responseObject = Ext.decode(response.responseText);
																			
											if (!responseObject.success) {
												Ext.Msg.alert('Error', 'A product must have <b>at least one</b> linked category.');
											} else {
												this.productTree.getNodeById(this.nodeParent).reload();	
											}
										}
									});
								}
							}
						});
					}
				},
				{
					text: 'Remove product',
					scope: this,
					handler: function() {
						Ext.Msg.show({
							title: 'Remove product?',
							msg: 'Are you sure you want to <b>remove this product</b> and all it\'s data? (It will be <b>removed from all categories</b> and if this product is an SKU parent <b>all of it\'s children will be removed</b> as well!)',
							buttons: Ext.Msg.YESNO,
							scope: this,
							fn: function(response) {
								if (response == 'yes') {
									var productId = this.treeMenu.baseParams.parent;
									var parent = this.treeMenu.baseParams.node.getPath();
									parent = parent.split('/');
									this.nodeParent = parent[parent.length-2];
									
									vcCore.ajax.request({
										url: vcCore.config.connectorUrl,
										params: {
											action: 'mgr/products/removeproduct',
											prodid: productId
										},
										scope: this,
										success: function(response) {
											this.productTree.getNodeById(this.nodeParent).reload();	
										}
									});
								}
							}
						});
					}
				}
			]
		});
		
		this.categoryTreeMenu = new Ext.menu.Menu({
			baseParams: {
				parent: 'category:0|parent:0',
				node: null
			},
			items: [
				{
					text: 'New product',
					handler: function() {
						var categoryId = this.categoryTreeMenu.baseParams.parent;
						categoryId = categoryId.split('|');
						categoryId = categoryId[0].split(':');
						categoryId = categoryId[1];

						window.location = '?a='+vcCore.getUrlVar('a')+'&action=product&shopid='+vcCore.getUrlVar('shopid')+'&catid='+categoryId;
					},
					scope: this
				}
			]
		});
		
		this.productTree = new Ext.tree.TreePanel({
		    dataUrl: this.defaultDataUrl,
			border: false,
	        autoScroll: true,
	        animate: false,
	        autoHeight: true,
	        anchor: '100%',
	        enableDD: true,
	        useArrows: true,
	        fieldLabel: 'Categories',
	        containerScroll: true,
			root: {
		        nodeType: 'async',
		        text: 'Webshop categories',
		        draggable: false,
		        id: 'category:0|parent:0',
		        allowDrop: false
		    },
		    listeners: {
		    	nodedragover: {
		    		scope: this,
		    		fn: function(event) {
		    			// Do not allow dropping a node outside it's own parent
		    			var sourceNode = event.dropNode;
		    			var targetNode = event.target;
		    			
		    			var sourcePath = sourceNode.getPath();
		    			var targetPath = targetNode.getPath();
		    			
		    			sourcePath = sourcePath.split('/');
		    			targetPath = targetPath.split('/');
		    			sourcePath.pop();
		    			targetPath.pop();
		    			if (sourcePath.pop() != targetPath.pop() || targetNode.attributes.cls.indexOf('folder') != -1) {
		    				event.cancel = true;
		    			}
		    		}	
		    	},
		    	contextmenu: {
		    		scope: this,
		    		fn: function(node, event) {
		    			if (node.attributes.cls.indexOf('folder') == -1) {
			    			this.treeMenu.baseParams.parent = node.id;
			    			this.treeMenu.baseParams.node = node;
			    			this.treeMenu.showAt(event.xy);
		    			} else {
		    				this.categoryTreeMenu.baseParams.parent = node.id;
		    				this.categoryTreeMenu.baseParams.node = node;
			    			this.categoryTreeMenu.showAt(event.xy);
		    			} 
		    		}	
		    	},
		    	dblclick: function(node, event) {
		    		if (node.attributes.cls.indexOf('folder') == -1) {
		    			window.location = '?a='+vcCore.getUrlVar('a')+'&action=product&shopid='+vcCore.getUrlVar('shopid')+'&prodid='+node.attributes.id;	
		    		}
		    	},
		    	load: {
		    		scope: this,
		    		fn: function() {
						
		    		}
		    	},
		    	expandnode: {
		    		scope: this,
		    		fn: function(node) { 
		    			this.productTreeSorter.doSort(node);
		    		}
		    	},
		    	movenode: {
		    		scope: this,
		    		fn: function(tree, node, oldParent, newParent, index) {
			    		// Calculate the place where the category should be
			    		var currentTarget = 0;
			    		this.oldParent = oldParent;
						this.newParent = newParent;
						this.currentNode = newParent;
						
			    		newParent.eachChild(function(item) {
			    			if (item == node) {
			    				return false;
			    			}
			    			
			    			if (item.attributes.cls.indexOf('folder') == -1) {
			    				currentTarget += 1;
			    			}
			    		}, this);
			    		
			    		vcCore.ajax.request({
							url: vcCore.config.connectorUrl,
							params: {
								sourceId: node.id,
								targetId: newParent.id,
								targetSort: currentTarget,
								hideSku: this.skusHidden,
								action: 'mgr/products/savenodes'
							},
							scope: this,
							success: function(response) {
								//alert(this.currentNode);
								this.currentNode.reload();
							}
						});
		    		}	
		    	}
		    }
		});

		this.productTreeSorter = new Ext.tree.TreeSorter(this.productTree, {
			folderSort: false,
			dir: 'asc',
			property: 'sort',
			sortType: function(sortValue) {
				return sortValue;	
			}
		});

		this.productTree.getRootNode().expand();
		
		if (this.skusHidden == 0) {
			var buttonText = 'Hide SKU\'s';	
			var buttonPressed = 1;
		} else {
			var buttonText = 'Show SKU\'s';	
			var buttonPressed = 0;
		}
		
		// The mainpanel always has to be in the "this.mainPanel" variable
		this.mainPanel = new Ext.Panel({
			renderTo: 'visioncart-content',
			padding: 15,
			border: true,
			items: [
				{
					xtype: 'toolbar',
					style: {
						border: 'none',
						backgroundColor: 'transparent'
					},
					items: [
						{
							xtype: 'button',
							text: buttonText,
							pressed: buttonPressed,
							enableToggle: true,
							style: {
								marginBottom: '10px'
							},
							listeners: {
								toggle: {
									scope: this,
									fn: function(button, pressed) {
										if (!pressed) {
											button.setText('Show SKU\'s');
											this.skusHidden = 1;
											Ext.apply(this.productTree.getLoader(), {
												dataUrl: vcCore.config.connectorUrl+'?action=mgr/products/getproductnodes&shop='+vcCore.getUrlVar('shopid')+'&hideskus=1'
											});
										} else {
											button.setText('Hide SKU\'s');
											this.skusHidden = 0;
											Ext.apply(this.productTree.getLoader(), {
												dataUrl: vcCore.config.connectorUrl+'?action=mgr/products/getproductnodes&shop='+vcCore.getUrlVar('shopid')+'&hideskus=0'
											});	
										}
										
										this.productTree.getRootNode().reload();
									}
								}
							}
						},
						'->',
						{
							text: 'New product',
							scope: this,
							handler: function() {
								window.location = '?a='+vcCore.getUrlVar('a')+'&action=product&shopid='+vcCore.getUrlVar('shopid');
							}
						}
					]
				},
				this.productTree
			]
		});
	}
});

Ext.onReady(function() {
	// Set page title and load main panel
	vcCore.setTitle(vcCore.config.currentShop+' - Manage products');
	
	// this makes the main class accessible through vcCore.pageClass and the panel through vcCore.pagePanel
	vcCore.loadPanel(vcPageProducts); 
});