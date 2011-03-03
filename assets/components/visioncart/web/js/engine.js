Ext.ns('VisionCart', 'vc');

VisionCart = Ext.extend(Object, {
	config: {},
	_connection: null,
	
	constructor: function(config) { 
		this.config = visionCartConfig;
		this.masterOption = null;
		this.events = {};
		this.events.options = {
			onBeforeLoad: null,
			onLoad: null
		};
	
		this.ajax = new Ext.data.Connection({
			url: this.config.connector,
			disableCaching: true,
			extraParams: {
				ctx: 'web',
				requestURL: window.location	
			}
		});
	},
	cfg: function(key, object, def) {
		if (!Ext.isDefined(object) || object == false || !object) {
			object = this.config;
		}
		
		if (Ext.isDefined(object[key])) {
			return object[key];
		}
		
		if (Ext.isDefined(def)) {
			return def;
		}
		
		return false;
	},
	request: function(args) {
		if (!Ext.isDefined(args.params) || Ext.isEmpty(args.params)) {
			return false;
		}
		
		this._connection = new Ext.data.Connection({
			defaultHeaders: {
				engine: 'VisionCart'
			},
			extraParams: {
				requestURL: window.location.toString(),
				ctx: 'web',
				method: 'ajax'
			},
			method: 'POST',
			url: this.config.connector
		});
		
		var parameters = {};
		if (Ext.isArray(args.params)) {
			Ext.each(args.params, function(params) {
				for(var key in params) {
					parameters[key] = params[key];
				}	
			});
		} else {
			for(var key in args.params) {
				parameters[key] = args.params[key];
			}
		}
		
		var options = {
			params: parameters
		};
		
		delete parameters; delete key; delete params;
		
		if (Ext.isDefined(args.url) && Ext.isPrimitive(args.url)) {
			option.url = args.url.toString();
		}
		
		this._connection.addListener({
			beforerequest: {scope: this, fn: function(connection, options) {
				if (Ext.isDefined(args.before) && Ext.isFunction(args.before)) {
					args.before.createDelegate((Ext.isDefined(args.scope)) ? args.scope : this, [
						args, {
							connection: connection,
							options: options
						}
					], false).defer(0, (Ext.isDefined(args.scope)) ? args.scope : this);
					
					return true;
				}
			}},
			requestcomplete: {scope: this, fn: function(connection, response, options) {
				if (Ext.isDefined(args.success) && Ext.isFunction(args.success)) {
					var result = response.responseText;
					
					try {
						result = Ext.decode(result);
					} catch(e) {}
					
					args.success.createDelegate((Ext.isDefined(args.scope)) ? args.scope : this, [
						result,
						args, {
							connection: connection,
							response: response,
							options: options
						}
					], false).defer(0, (Ext.isDefined(args.scope)) ? args.scope : this);
					
					return true;
				}
			}},
			requestexception: {scope: this, fn: function(connection, response, options) {
				if (Ext.isDefined(args.failure) && Ext.isFunction(args.failure)) {
					var result = response.responseText;
					
					try {
						result = Ext.decode(result);
					} catch(e) {}
					
					args.failure.createDelegate((Ext.isDefined(args.scope)) ? args.scope : this, [
						result,
						args, {
							connection: connection,
							response: response,
							options: options
						}
					], false).defer(0, (Ext.isDefined(args.scope)) ? args.scope : this);
					
					return true;
				}
			}}
		});
		
		this._connection.request(options);
		
		delete options; delete result;
	},
	orderBasketAdd: function(productId, method, update) {
		Ext.get(update).set({
			value: parseInt(Ext.get(update).getAttribute('value')) + 1
		});
		
		Ext.get(update).up('form').dom.submit();
	},
	basketAdd: function(productId, method, element) {
		var products = {};
		products[productId] = 1;
		
		this.request({
			params: {
				action: 'basket',
				basketAction: 'add',
				products: Ext.encode(products)				
			},
			success: function(result, args, extra) {
				Ext.fly('visioncart-shoppingcart').update(result.tpl);
			}
		});
		
		delete products; delete productId; delete method; delete element;
	},
	getOption: function(key, obj, def) {
		if (obj[key] || obj[key] != null) {
			return obj[key]; 
		}
		
		if (def || def != null) {
			return def;
		}
		
		return false;
	},
	setOptionValue: function(optionId, valueId, disableUpdate) {
		if (valueId == 0) {
			valueId = '';	
		}
		
		Ext.get('vc-product-option-'+optionId).set({
			value: valueId
		});
		
		var productOptions = Ext.query('input[id^=vc-product-option-]');
		if (productOptions.length > 0) {
			this.masterOption = Ext.get(productOptions[0]).getAttribute('id');
			this.masterOption = this.masterOption.split('-');
			this.masterOption = parseInt(this.masterOption[this.masterOption.length-1]);
		}
		
		if (!disableUpdate) {
			this.getProductOptions();
		}
	},
	getProductOptions: function() {
		if (this.events.options.onBeforeLoad != null) {
			this.events.options.onBeforeLoad();	
		}
		
		// Get current selected values
		var productOptions = Ext.query('input[id^=vc-product-option-]');
		var selectedOptions = new Array();
		Ext.each(productOptions, function(item, key) {
			// Get the option ID
			var id = item.getAttribute('id');
			id = id.split('-');
			id = parseInt(id[id.length-1]);
			
			item = Ext.get(item);
			
			selectedOptions.push({
				optionId: id,
				value: item.getValue()
			});
		}); 
		
		var jsonPost = Ext.encode(selectedOptions);
		
		this.ajax.request({
			params: {
				selectedOptions: jsonPost,
				action: 'productoptions'
			},
			scope: this,
			success: function(response) {
				var response = Ext.decode(response.responseText);	
				
				if (response.productId != false) {
					window.location = response.productLink+'?option=true';	
				}
				
				Ext.each(response.html, function(item, key) {
					if (item.option != this.masterOption) {
						var container = Ext.get('vc-product-optioncontent-'+item.option);
						container.update(item.response);
					}
				}, this);
				
				if (this.events.options.onLoad != null) {
					this.events.options.onLoad();	
				}
			}
		});
	}
});

vc = new VisionCart();	
var loader;
Ext.onReady(function() {
	loader = new Ext.LoadMask(Ext.getBody(), {msg:"Please wait..."});
	
	vc.events.options.onBeforeLoad = function() {
		loader.show();
	}
	
	vc.events.options.onLoad = function() {
		loader.hide();
	}
});

function setColor(div) {
	var div = Ext.get(div);
	var parent = div.up('div');
	
	var divs = parent.query('div');
	
	Ext.each(divs, function(item) {
		item = Ext.get(item);
		if (item != div) {
			item.removeClass('selected');	
		}
	});
	
	div.addClass('selected');
}