var vcPageOrders = Ext.extend(Ext.Panel, {
	initComponent: function() {
		// The mainpanel always has to be in the "this.mainPanel" variable
		this.mainPanel = new Ext.Panel({
			renderTo: 'visioncart-content',
			padding: 15,
			border: false,
			items: [

			]
		});
	}
});

Ext.onReady(function() {
	// Set page title and load main panel
	vcCore.setTitle(vcCore.config.currentShop+' - Product Properties');
	
	// this makes the main class accessible through vcCore.pageClass and the panel through vcCore.pagePanel
	vcCore.loadPanel(vcPageOrders); 
});