vcCore.stores.shops = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/shops'
    },
    fields: [
        'id', 'name', 'context', 'active'
    ]
});

vcCore.stores.context = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/context'
    },
    fields: [
        'key'
    ]
});

vcCore.stores.paymentModules = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/paymentmodules'
    },
    fields: [
        'id', 'type', 'name', 'description', 'controller', 'config', 'active', 'checkbox'
    ]
});

vcCore.stores.shippingModules = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/shippingmodules'
    },
    fields: [
        'id', 'type', 'name', 'description', 'controller', 'config', 'active', 'checkbox'
    ]
});

vcCore.stores.categoryExtraFields = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'name',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/categoryextrafields',
    	categoryId: 0
    },
    fields: [
        'name', 'type', 'mandatory'
    ]
});

vcCore.stores.categories = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/categories',
    	shopId: 0,
    	parent: 0
    },
    fields: [
        'id', 'name', 'pricechange'
    ]
});

vcCore.stores.categoryConfigFields = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'name',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/categoryconfigfields',
    	categoryId: 0
    },
    fields: [
        'key', 'value'
    ]
});

vcCore.stores.options = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/options',
    	shopId: 0,
    	prodId: 0
    },
    fields: [
        'id', 'shopid', 'name', 'inputsnippet', 'outputsnippet'
    ]
});

vcCore.stores.inputsnippets = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/snippets',
    	type: 'input'
    },
    fields: [
        'id', 'name', 'description', 'properties'
    ]
});

vcCore.stores.outputsnippets = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    totalProperty: 'total',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/snippets',
    	type: 'output'
    },
    fields: [
        'id', 'name', 'description', 'properties'
    ]
});

vcCore.stores.optionvalues = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/optionvalues',
    	optionId: 0
    },
    fields: [
        'id', 'value', 'weight', 'price', 'shippingprice'
    ]
});

vcCore.stores.orders = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    remoteSort: true,
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/orders',
    	shopId: vcCore.getUrlVar('shopid'),
    	status: 0,
    	search: ''
    },
    fields: [
        'id', 'fullname', 'ordernumber', 'totalweight', 'totalorderamountex', 'totalorderamountin', , 'paidamount', 'ordertime', 'status'
    ]
});

vcCore.stores.orderItems = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/orderitems',
    	shopId: vcCore.getUrlVar('shopid'),
    	orderId: vcCore.getUrlVar('id')
    },
    fields: [
        'id', 'articlenumber', 'name', 'quantity', 'price', 'totalprice'
    ]
});

vcCore.stores.productoptions = new Ext.data.JsonStore({
	root: 'results',
	idProperty: 'id',
    fields: [
        'id', 'productid', 'optionid', 'valueid', 'option', 'value', 'sort'
    ],
    sortInfo: {
    	field: 'sort',
    	direction: 'ASC'
    },
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/productoptions',
    	shopId: vcCore.getUrlVar('shopid'),
    	prodId: vcCore.getUrlVar('prodid')
    }
});

vcCore.stores.emailChunks = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/emailchunks'
    },
    fields: [
        'id', 'name', 'description'
    ]
});

vcCore.stores.sku = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/sku',
    	prodId: 0
    },
    fields: [
        'id', 'name', 'alias', 'description', 'articlenumber', 'price', 'weight', 'shippingprice', 'stock', 'active', 'qtip'
    ]
});

vcCore.stores.resources = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/resources'
    },
    fields: [
        'id', 'pagetitle'
    ]
});

vcCore.stores.chunks = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/chunks'
    },
    fields: [
        'id', 'name'
    ]
});

vcCore.stores.themes = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/themes'
    },
    fields: [
        'theme'
    ]
});
 
vcCore.stores.categoryTierPricing = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/categorytierpricing',
    	categoryId: 0
    },
    fields: [
        'quantity', 'amount', 'modifier'
    ]
});

vcCore.stores.productTierPricing = new Ext.data.JsonStore({
    root: 'results',
    idProperty: 'id',
    url: vcCore.config.connectorUrl,
    baseParams: {
    	action: 'mgr/stores/producttierpricing',
    	productId: 0
    },
    fields: [
        'quantity', 'amount', 'modifier'
    ]
});
