var VisionCart = function(config) {
    config = config || {};
    VisionCart.superclass.constructor.call(this,config);
};

Ext.extend(VisionCart, Ext.Component, {
	initComponent: function() {
		this.loadMask = new Ext.LoadMask(Ext.getBody(), {msg: 'Loading, please wait...'});
		this.messageWindow = null;
		this.siteId = siteId;
		this.pagePanel = null;
		this.pageClass = null;
		this.stores = {};
		this.config = {};
		this.tasks = {};
		
		this.config.disableMask = false;
		
		Ext.onReady(function() {
			if (Ext.get('visioncart-container')) {
				this.mainPanel = new Ext.Panel({
					renderTo: 'visioncart-container',
					border: false,
					autoHeight: true,
					unstyled: true,
					baseCls: 'vc-mainpanel',
					items: [
						{	
							html: '<h2 id="visioncart-title"></h2>',
							border: false,
							cls: 'modx-page-header'	
						},
						{
							html: '<div id="visioncart-content"></div>',
							border: false,
							unstyled: true
						}
					]
				});
			}
		}, this);
		
		this.ajax = new Ext.data.Connection({
			disableCaching: true,
			extraParams: {
				HTTP_MODAUTH: this.siteId
			}
		});
		
		this.ajax.on('beforerequest', function() {
			if (!this.config.disableMask) {
				this.showAjaxLoader();
			}
		}, this);
		
		this.ajax.on('requestcomplete', function() {
			if (!this.config.disableMask) {
				this.hideAjaxLoader();
			}
		}, this);
		
		this.ajax.on('requestexception', function() {
			if (!this.config.disableMask) {
				this.hideAjaxLoader();
			}
		}, this);
	},
	showMessage: function(message, messageStay) {
		if (!this.messageWindow || this.messageWindow == null) {
			this.messageWindow = new Ext.Window({
				closable: false,
				resizable: false,
				unstyled: true,
				shadow: false,
				y: -50,
				bodyStyle: {
					backgroundColor: '#FFFFFF',
					padding: '10px',
					border: '2px solid #666666',
					borderRadius: '10px',
					'-moz-border-radius': '10px',
					'-webkit-border-radius': '10px',
					fontWeight: 'bold',
					zIndex: 999999
				}
			});
			
			this.messageWindow.show();
			this.messageWindowEl = this.messageWindow.getEl();
			
			this.tasks.hideMessage = new Ext.util.DelayedTask(function() {
				this.hideMessage();
			}, this);
			
			Ext.getBody().appendChild(this.messageWindowEl);
		}
		
		// Set styles
		Ext.get(this.messageWindow.getId()).setStyle('z-index', '999999');
		this.messageWindowEl.setOpacity(0);
		this.messageWindowEl.setOpacity(1, true);
		this.messageWindowEl.setY(10, true);
		
		// Update message
		this.messageWindow.update(message);
				
		// Hide after 3 seconds
		if (!messageStay) {
			this.tasks.hideMessage.delay(3000);
		}
	},
	hideMessage: function() {
		this.messageWindowEl.setY(-50, true);
		this.messageWindowEl.setOpacity(0, true);
	},
	showAjaxLoader: function() {
		// Commented out because chrome has a bug with ExtJS
		//this.loadMask.show();
		this.showMessage('<div class="vc-ajax-loading">Loading...</div>', true);
		
		if (Ext.get('vc-ajax-haze')) {
			var windowSize = getWindowScrollSize();
			Ext.get('vc-ajax-haze').setStyle({
				display: 'block',
				opacity: 0.5
			});
			
			Ext.getBody().setStyle({
				overflow: 'hidden'
			});
		}
	},
	hideAjaxLoader: function() {
		// Commented out because chrome has a bug with ExtJS
		this.hideMessage();
		
		if (Ext.get('vc-ajax-haze')) {
			Ext.get('vc-ajax-haze').setStyle({
				display: 'none'
			});
			
			Ext.getBody().setStyle({
				overflow: 'visible'
			});
		}
	},
	loadPanel: function(panelClass) {
		this.pageClass = new panelClass();
		this.pagePanel = this.pageClass.mainPanel;
	},
	setTitle: function(title) {
		Ext.get('visioncart-title').update(title);
	},
	getUrlVar: function(key) {
		// Thanks to: http://snipplr.com/users/Roshambo/
	    var vars = [], hash;
	    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	    for(var i = 0; i < hashes.length; i++)
	    {
	        hash = hashes[i].split('=');
	        if (hash[0] == key) {
	        	return hash[1];	
	        }
	    }
	    
	    return '';
	},
	createAlias: function(string) {
		string = string.toLowerCase();
		var allowed = 'abcdefghijklmnopqrstuvwxyz0123456789';
		var output = '';
		for(var i = 0; i < string.length; i++) {
			if (allowed.indexOf(string[i]) != -1) {
				output += string[i];
			} else {
				output += '-';	
			}
		}	
		
		while(output.indexOf('--') != -1) {
			output = output.replace(/--/gi, '-', output);	
		}
		
		if (output.substr(0, 1) == '-') {
			output = output.substr(1);	
		}
		
		if (output.substr(-1) == '-') {
			output = output.substr(0, (output.length)-1);	
		}
		
		return output;
	}
});

function getDocHeight() {
    var D = document;
    return Math.max(
        D.body.clientHeight,
        window.innerHeight,
        document.documentElement.clientHeight
    );
}

function getWindowScrollSize() {
    var D = document;
    var y = Math.max(
        Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
        Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
        Math.max(D.body.clientHeight, D.documentElement.clientHeight)
    );
    
    var x = Math.max(
        Math.max(D.body.scrollWidth, D.documentElement.scrollWidth),
        Math.max(D.body.offsetWidth, D.documentElement.offsetWidth),
        Math.max(D.body.clientWidth, D.documentElement.clientWidth)
    );
    
    return {x: x, y: y};
}

Ext.reg('visioncart', VisionCart);

var vcCore = new VisionCart();