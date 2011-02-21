// Ext JS extensions used

/**
 * This plugin can be either used on BasicForm or FormPanel.
 * In both cases it adds a method clear() to the object
 * which allows to clear (empty) all the forms values.
 * This is opposed to the reset() method which resets all values
 * to the last values loaded through a call to load().
 * But sometimes (e.g. when you want to recycle an existing form for use
 * for a new record, you have to clear the form and also corectly reset
 * the isDirty() flag.
 *
 *
 */

Ext.ux.FormClear=function()
{

    this.init=function(_object)
    {
        if (typeof _object.form=="object")
        { //we are a formpanel and have a form, be kind and also add the method to the basic form
      	
            //clear method for the underlying form:
            _object.form.clear=function()
            {
                var data={};
                this.items.each(function(item)
                {
                    data[item.getName()]=null;
                });

                var emptyRecord=new Ext.data.Record(data);
                this.loadRecord(emptyRecord);
                this.reset();
            };

            //clear method for the forpanel itself
            _object.clear=function()
            {
                var data={};
                this.items.each(function(item)
                {
                    data[item.getName()]=null;
                });

                var emptyRecord=new Ext.data.Record(data);
                this.form.loadRecord(emptyRecord);
                this.getForm().reset();
            };

        }
        else
        { //we are a basicform
            _object.clear=function()
            {
                var data={};
                this.items.each(function(item)
                {
                    data[item.getName()]=null;
                });

                var emptyRecord=new Ext.data.Record(data);
                this.loadRecord(emptyRecord);
            };
        }
    };

};

/** add clearDirty to basicform */
Ext.override(Ext.form.BasicForm,{
    clearDirty : function(nodeToRecurse){
        nodeToRecurse = nodeToRecurse || this;
        nodeToRecurse.items.each(function(f){
            if(f.items){
                this.clearDirty(f);
            } else if(f.originalValue != f.getValue()){
                f.originalValue = f.getValue();
            }
        },this);
    }
});

function findTreeChild(tree, value) {
	for(var i = 0; i < tree.childNodes.length; i++) {
		if (tree.childNodes[i].id == value) {
			return tree.childNodes[i];	
		} else {
			if(found = findTreeChild(tree.childNodes[i], value)) {
                return found;
            } 	
		}
	}
    return null;
}  

/*!
 * Ext JS Library 3.2.1
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.ns('Ext.ux.form');

/**
 * @class Ext.ux.form.FileUploadField
 * @extends Ext.form.TextField
 * Creates a file upload field.
 * @xtype fileuploadfield
 */
Ext.ux.form.FileUploadField = Ext.extend(Ext.form.TextField,  {
    /**
     * @cfg {String} buttonText The button text to display on the upload button (defaults to
     * 'Browse...').  Note that if you supply a value for {@link #buttonCfg}, the buttonCfg.text
     * value will be used instead if available.
     */
    buttonText: 'Browse...',
    /**
     * @cfg {Boolean} buttonOnly True to display the file upload field as a button with no visible
     * text field (defaults to false).  If true, all inherited TextField members will still be available.
     */
    buttonOnly: false,
    /**
     * @cfg {Number} buttonOffset The number of pixels of space reserved between the button and the text field
     * (defaults to 3).  Note that this only applies if {@link #buttonOnly} = false.
     */
    buttonOffset: 3,
    /**
     * @cfg {Object} buttonCfg A standard {@link Ext.Button} config object.
     */

    // private
    readOnly: true,

    /**
     * @hide
     * @method autoSize
     */
    autoSize: Ext.emptyFn,

    // private
    initComponent: function(){
        Ext.ux.form.FileUploadField.superclass.initComponent.call(this);

        this.addEvents(
            /**
             * @event fileselected
             * Fires when the underlying file input field's value has changed from the user
             * selecting a new file from the system file selection dialog.
             * @param {Ext.ux.form.FileUploadField} this
             * @param {String} value The file value returned by the underlying file input field
             */
            'fileselected'
        );
    },

    // private
    onRender : function(ct, position){
        Ext.ux.form.FileUploadField.superclass.onRender.call(this, ct, position);

        this.wrap = this.el.wrap({cls:'x-form-field-wrap x-form-file-wrap'});
        this.el.addClass('x-form-file-text');
        this.el.dom.removeAttribute('name');
        this.createFileInput();

        var btnCfg = Ext.applyIf(this.buttonCfg || {}, {
            text: this.buttonText
        });
        this.button = new Ext.Button(Ext.apply(btnCfg, {
            renderTo: this.wrap,
            cls: 'x-form-file-btn' + (btnCfg.iconCls ? ' x-btn-icon' : '')
        }));

        if(this.buttonOnly){
            this.el.hide();
            this.wrap.setWidth(this.button.getEl().getWidth());
        }

        this.bindListeners();
        this.resizeEl = this.positionEl = this.wrap;
    },
    
    bindListeners: function(){
        this.fileInput.on({
            scope: this,
            mouseenter: function() {
                this.button.addClass(['x-btn-over','x-btn-focus'])
            },
            mouseleave: function(){
                this.button.removeClass(['x-btn-over','x-btn-focus','x-btn-click'])
            },
            mousedown: function(){
                this.button.addClass('x-btn-click')
            },
            mouseup: function(){
                this.button.removeClass(['x-btn-over','x-btn-focus','x-btn-click'])
            },
            change: function(){
                var v = this.fileInput.dom.value;
                this.setValue(v);
                this.fireEvent('fileselected', this, v);    
            }
        }); 
    },
    
    createFileInput : function() {
        this.fileInput = this.wrap.createChild({
            id: this.getFileInputId(),
            name: this.name||this.getId(),
            cls: 'x-form-file',
            tag: 'input',
            type: 'file',
            size: 1
        });
    },
    
    reset : function(){
        this.fileInput.remove();
        this.createFileInput();
        this.bindListeners();
        Ext.ux.form.FileUploadField.superclass.reset.call(this);
    },

    // private
    getFileInputId: function(){
        return this.id + '-file';
    },

    // private
    onResize : function(w, h){
        Ext.ux.form.FileUploadField.superclass.onResize.call(this, w, h);

        this.wrap.setWidth(w);

        if(!this.buttonOnly){
            var w = this.wrap.getWidth() - this.button.getEl().getWidth() - this.buttonOffset;
            this.el.setWidth(w);
        }
    },

    // private
    onDestroy: function(){
        Ext.ux.form.FileUploadField.superclass.onDestroy.call(this);
        Ext.destroy(this.fileInput, this.button, this.wrap);
    },
    
    onDisable: function(){
        Ext.ux.form.FileUploadField.superclass.onDisable.call(this);
        this.doDisable(true);
    },
    
    onEnable: function(){
        Ext.ux.form.FileUploadField.superclass.onEnable.call(this);
        this.doDisable(false);

    },
    
    // private
    doDisable: function(disabled){
        this.fileInput.dom.disabled = disabled;
        this.button.setDisabled(disabled);
    },


    // private
    preFocus : Ext.emptyFn,

    // private
    alignErrorIcon : function(){
        this.errorIcon.alignTo(this.wrap, 'tl-tr', [2, 0]);
    }

});

Ext.reg('fileuploadfield', Ext.ux.form.FileUploadField);

// backwards compat
Ext.form.FileUploadField = Ext.ux.form.FileUploadField;


Ext.override(Ext.form.Field, {
	hideItem: function(){
	if (this.getForm(this)) {
	this.getForm(this).addClass('x-hide-' + this.hideMode);
	}
	},
	showItem: function(){
	if (this.getForm(this)) {
	this.getForm(this).removeClass('x-hide-' + this.hideMode);
	}
	},
	setFieldLabel: function(text) {
	if (this.getForm(this)) {
	var label = this.getForm(this).first('label.x-form-item-label');
	label.update(text);
	}
	},
	getForm: function() {
	return this.el.findParent('.x-form-item', 3, true);
	}
});


/*!
 * Ext JS Library 3.3.0
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.ns('Ext.ux.tree');

/**
 * @class Ext.ux.tree.TreeGridSorter
 * @extends Ext.tree.TreeSorter
 * Provides sorting of nodes in a {@link Ext.ux.tree.TreeGrid}.  The TreeGridSorter automatically monitors events on the
 * associated TreeGrid that might affect the tree's sort order (beforechildrenrendered, append, insert and textchange).
 * Example usage:<br />
 * <pre><code>
 new Ext.ux.tree.TreeGridSorter(myTreeGrid, {
     folderSort: true,
     dir: "desc",
     sortType: function(node) {
         // sort by a custom, typed attribute:
         return parseInt(node.id, 10);
     }
 });
 </code></pre>
 * @constructor
 * @param {TreeGrid} tree
 * @param {Object} config
 */
Ext.ux.tree.TreeGridSorter = Ext.extend(Ext.tree.TreeSorter, {
    /**
     * @cfg {Array} sortClasses The CSS classes applied to a header when it is sorted. (defaults to <tt>['sort-asc', 'sort-desc']</tt>)
     */
    sortClasses : ['sort-asc', 'sort-desc'],
    /**
     * @cfg {String} sortAscText The text displayed in the 'Sort Ascending' menu item (defaults to <tt>'Sort Ascending'</tt>)
     */
    sortAscText : 'Sort Ascending',
    /**
     * @cfg {String} sortDescText The text displayed in the 'Sort Descending' menu item (defaults to <tt>'Sort Descending'</tt>)
     */
    sortDescText : 'Sort Descending',

    constructor : function(tree, config) {
        if(!Ext.isObject(config)) {
            config = {
                property: tree.columns[0].dataIndex || 'text',
                folderSort: true
            }
        }

        Ext.ux.tree.TreeGridSorter.superclass.constructor.apply(this, arguments);

        this.tree = tree;
        tree.on('headerclick', this.onHeaderClick, this);
        tree.ddAppendOnly = true;

        var me = this;
        this.defaultSortFn = function(n1, n2){

            var desc = me.dir && me.dir.toLowerCase() == 'desc',
                prop = me.property || 'text',
                sortType = me.sortType,
                caseSensitive = me.caseSensitive === true,
                leafAttr = me.leafAttr || 'leaf',
                attr1 = n1.attributes,
                attr2 = n2.attributes;

            if(me.folderSort){
                if(attr1[leafAttr] && !attr2[leafAttr]){
                    return 1;
                }
                if(!attr1[leafAttr] && attr2[leafAttr]){
                    return -1;
                }
            }
            var prop1 = attr1[prop],
                prop2 = attr2[prop],
                v1 = sortType ? sortType(prop1) : (caseSensitive ? prop1 : prop1.toUpperCase());
                v2 = sortType ? sortType(prop2) : (caseSensitive ? prop2 : prop2.toUpperCase());
                
            if(v1 < v2){
                return desc ? +1 : -1;
            }else if(v1 > v2){
                return desc ? -1 : +1;
            }else{
                return 0;
            }
        };

        tree.on('afterrender', this.onAfterTreeRender, this, {single: true});
        tree.on('headermenuclick', this.onHeaderMenuClick, this);
    },

    onAfterTreeRender : function() {
        if(this.tree.hmenu){
            this.tree.hmenu.insert(0,
                {itemId:'asc', text: this.sortAscText, cls: 'xg-hmenu-sort-asc'},
                {itemId:'desc', text: this.sortDescText, cls: 'xg-hmenu-sort-desc'}
            );
        }
        this.updateSortIcon(0, 'asc');
    },

    onHeaderMenuClick : function(c, id, index) {
        if(id === 'asc' || id === 'desc') {
            this.onHeaderClick(c, null, index);
            return false;
        }
    },

    onHeaderClick : function(c, el, i) {
        if(c && !this.tree.headersDisabled){
            var me = this;

            me.property = c.dataIndex;
            me.dir = c.dir = (c.dir === 'desc' ? 'asc' : 'desc');
            me.sortType = c.sortType;
            me.caseSensitive === Ext.isBoolean(c.caseSensitive) ? c.caseSensitive : this.caseSensitive;
            me.sortFn = c.sortFn || this.defaultSortFn;

            this.tree.root.cascade(function(n) {
                if(!n.isLeaf()) {
                    me.updateSort(me.tree, n);
                }
            });

            this.updateSortIcon(i, c.dir);
        }
    },

    // private
    updateSortIcon : function(col, dir){
        var sc = this.sortClasses,
            hds = this.tree.innerHd.select('td').removeClass(sc);
        hds.item(col).addClass(sc[dir == 'desc' ? 1 : 0]);
    }
});

/*!
 * Ext JS Library 3.3.0
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
/**
 * @class Ext.tree.ColumnResizer
 * @extends Ext.util.Observable
 */
Ext.tree.ColumnResizer = Ext.extend(Ext.util.Observable, {
    /**
     * @cfg {Number} minWidth The minimum width the column can be dragged to.
     * Defaults to <tt>14</tt>.
     */
    minWidth: 14,

    constructor: function(config){
        Ext.apply(this, config);
        Ext.tree.ColumnResizer.superclass.constructor.call(this);
    },

    init : function(tree){
        this.tree = tree;
        tree.on('render', this.initEvents, this);
    },

    initEvents : function(tree){
        tree.mon(tree.innerHd, 'mousemove', this.handleHdMove, this);
        this.tracker = new Ext.dd.DragTracker({
            onBeforeStart: this.onBeforeStart.createDelegate(this),
            onStart: this.onStart.createDelegate(this),
            onDrag: this.onDrag.createDelegate(this),
            onEnd: this.onEnd.createDelegate(this),
            tolerance: 3,
            autoStart: 300
        });
        this.tracker.initEl(tree.innerHd);
        tree.on('beforedestroy', this.tracker.destroy, this.tracker);
    },

    handleHdMove : function(e, t){
        var hw = 5,
            x = e.getPageX(),
            hd = e.getTarget('.x-treegrid-hd', 3, true);
        
        if(hd){                                 
            var r = hd.getRegion(),
                ss = hd.dom.style,
                pn = hd.dom.parentNode;
            
            if(x - r.left <= hw && hd.dom !== pn.firstChild) {
                var ps = hd.dom.previousSibling;
                while(ps && Ext.fly(ps).hasClass('x-treegrid-hd-hidden')) {
                    ps = ps.previousSibling;
                }
                if(ps) {                    
                    this.activeHd = Ext.get(ps);
    				ss.cursor = Ext.isWebKit ? 'e-resize' : 'col-resize';
                }
            } else if(r.right - x <= hw) {
                var ns = hd.dom;
                while(ns && Ext.fly(ns).hasClass('x-treegrid-hd-hidden')) {
                    ns = ns.previousSibling;
                }
                if(ns) {
                    this.activeHd = Ext.get(ns);
    				ss.cursor = Ext.isWebKit ? 'w-resize' : 'col-resize';                    
                }
            } else{
                delete this.activeHd;
                ss.cursor = '';
            }
        }
    },

    onBeforeStart : function(e){
        this.dragHd = this.activeHd;
        return !!this.dragHd;
    },

    onStart : function(e){
        this.dragHeadersDisabled = this.tree.headersDisabled;
        this.tree.headersDisabled = true;
        this.proxy = this.tree.body.createChild({cls:'x-treegrid-resizer'});
        this.proxy.setHeight(this.tree.body.getHeight());

        var x = this.tracker.getXY()[0];

        this.hdX = this.dragHd.getX();
        this.hdIndex = this.tree.findHeaderIndex(this.dragHd);

        this.proxy.setX(this.hdX);
        this.proxy.setWidth(x-this.hdX);

        this.maxWidth = this.tree.outerCt.getWidth() - this.tree.innerBody.translatePoints(this.hdX).left;
    },

    onDrag : function(e){
        var cursorX = this.tracker.getXY()[0];
        this.proxy.setWidth((cursorX-this.hdX).constrain(this.minWidth, this.maxWidth));
    },

    onEnd : function(e){
        var nw = this.proxy.getWidth(),
            tree = this.tree,
            disabled = this.dragHeadersDisabled;
        
        this.proxy.remove();
        delete this.dragHd;
        
        tree.columns[this.hdIndex].width = nw;
        tree.updateColumnWidths();
        
        setTimeout(function(){
            tree.headersDisabled = disabled;
        }, 100);
    }
});

/*!
 * Ext JS Library 3.3.0
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
/**
 * @class Ext.ux.tree.TreeGridNodeUI
 * @extends Ext.tree.TreeNodeUI
 */
Ext.ux.tree.TreeGridNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
    isTreeGridNodeUI: true,

    renderElements : function(n, a, targetNode, bulkRender){
        var t = n.getOwnerTree(),
            cols = t.columns,
            c = cols[0],
            i, buf, len;

        this.indentMarkup = n.parentNode ? n.parentNode.ui.getChildIndent() : '';

        buf = [
             '<tbody class="x-tree-node">',
                '<tr ext:tree-node-id="', n.id ,'" class="x-tree-node-el x-tree-node-leaf ', a.cls, '">',
                    '<td class="x-treegrid-col">',
                        '<span class="x-tree-node-indent">', this.indentMarkup, "</span>",
                        '<img src="', this.emptyIcon, '" class="x-tree-ec-icon x-tree-elbow" />',
                        '<img src="', a.icon || this.emptyIcon, '" class="x-tree-node-icon', (a.icon ? " x-tree-node-inline-icon" : ""), (a.iconCls ? " "+a.iconCls : ""), '" unselectable="on" />',
                        '<a hidefocus="on" class="x-tree-node-anchor" href="', a.href ? a.href : '#', '" tabIndex="1" ',
                            a.hrefTarget ? ' target="'+a.hrefTarget+'"' : '', '>',
                        '<span unselectable="on">', (c.tpl ? c.tpl.apply(a) : a[c.dataIndex] || c.text), '</span></a>',
                    '</td>'
        ];

        for(i = 1, len = cols.length; i < len; i++){
            c = cols[i];
            buf.push(
                    '<td class="x-treegrid-col ', (c.cls ? c.cls : ''), '">',
                        '<div unselectable="on" class="x-treegrid-text"', (c.align ? ' style="text-align: ' + c.align + ';"' : ''), '>',
                            (c.tpl ? c.tpl.apply(a) : a[c.dataIndex]),
                        '</div>',
                    '</td>'
            );
        }

        buf.push(
            '</tr><tr class="x-tree-node-ct"><td colspan="', cols.length, '">',
            '<table class="x-treegrid-node-ct-table" cellpadding="0" cellspacing="0" style="table-layout: fixed; display: none; width: ', t.innerCt.getWidth() ,'px;"><colgroup>'
        );
        for(i = 0, len = cols.length; i<len; i++) {
            buf.push('<col style="width: ', (cols[i].hidden ? 0 : cols[i].width) ,'px;" />');
        }
        buf.push('</colgroup></table></td></tr></tbody>');

        if(bulkRender !== true && n.nextSibling && n.nextSibling.ui.getEl()){
            this.wrap = Ext.DomHelper.insertHtml("beforeBegin", n.nextSibling.ui.getEl(), buf.join(''));
        }else{
            this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf.join(''));
        }

        this.elNode = this.wrap.childNodes[0];
        this.ctNode = this.wrap.childNodes[1].firstChild.firstChild;
        var cs = this.elNode.firstChild.childNodes;
        this.indentNode = cs[0];
        this.ecNode = cs[1];
        this.iconNode = cs[2];
        this.anchor = cs[3];
        this.textNode = cs[3].firstChild;
    },

    // private
    animExpand : function(cb){
        this.ctNode.style.display = "";
        Ext.ux.tree.TreeGridNodeUI.superclass.animExpand.call(this, cb);
    }
});

Ext.ux.tree.TreeGridRootNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
    isTreeGridNodeUI: true,

    // private
    render : function(){
        if(!this.rendered){
            this.wrap = this.ctNode = this.node.ownerTree.innerCt.dom;
            this.node.expanded = true;
        }

        if(Ext.isWebKit) {
            // weird table-layout: fixed issue in webkit
            var ct = this.ctNode;
            ct.style.tableLayout = null;
            (function() {
                ct.style.tableLayout = 'fixed';
            }).defer(1);
        }
    },

    destroy : function(){
        if(this.elNode){
            Ext.dd.Registry.unregister(this.elNode.id);
        }
        delete this.node;
    },

    collapse : Ext.emptyFn,
    expand : Ext.emptyFn
});

/*!
 * Ext JS Library 3.3.0
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
/**
 * @class Ext.ux.tree.TreeGridLoader
 * @extends Ext.tree.TreeLoader
 */
Ext.ux.tree.TreeGridLoader = Ext.extend(Ext.tree.TreeLoader, {
    createNode : function(attr) {
        if (!attr.uiProvider) {
            attr.uiProvider = Ext.ux.tree.TreeGridNodeUI;
        }
        return Ext.tree.TreeLoader.prototype.createNode.call(this, attr);
    }
});

/*!
 * Ext JS Library 3.3.0
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
(function() {
    Ext.override(Ext.list.Column, {
        init : function() {    
            var types = Ext.data.Types,
                st = this.sortType;
                    
            if(this.type){
                if(Ext.isString(this.type)){
                    this.type = Ext.data.Types[this.type.toUpperCase()] || types.AUTO;
                }
            }else{
                this.type = types.AUTO;
            }

            // named sortTypes are supported, here we look them up
            if(Ext.isString(st)){
                this.sortType = Ext.data.SortTypes[st];
            }else if(Ext.isEmpty(st)){
                this.sortType = this.type.sortType;
            }
        }
    });

    Ext.tree.Column = Ext.extend(Ext.list.Column, {});
    Ext.tree.NumberColumn = Ext.extend(Ext.list.NumberColumn, {});
    Ext.tree.DateColumn = Ext.extend(Ext.list.DateColumn, {});
    Ext.tree.BooleanColumn = Ext.extend(Ext.list.BooleanColumn, {});

    Ext.reg('tgcolumn', Ext.tree.Column);
    Ext.reg('tgnumbercolumn', Ext.tree.NumberColumn);
    Ext.reg('tgdatecolumn', Ext.tree.DateColumn);
    Ext.reg('tgbooleancolumn', Ext.tree.BooleanColumn);
})();


/*!
 * Ext JS Library 3.3.0
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
/**
 * @class Ext.ux.tree.TreeGrid
 * @extends Ext.tree.TreePanel
 * 
 * @xtype treegrid
 */
Ext.ux.tree.TreeGrid = Ext.extend(Ext.tree.TreePanel, {
    rootVisible : false,
    useArrows : true,
    lines : false,
    borderWidth : Ext.isBorderBox ? 0 : 2, // the combined left/right border for each cell
    cls : 'x-treegrid',

    columnResize : true,
    enableSort : true,
    reserveScrollOffset : true,
    enableHdMenu : true,
    
    columnsText : 'Columns',

    initComponent : function() {
        if(!this.root) {
            this.root = new Ext.tree.AsyncTreeNode({text: 'Root'});
        }
        
        // initialize the loader
        var l = this.loader;
        if(!l){
            l = new Ext.ux.tree.TreeGridLoader({
                dataUrl: this.dataUrl,
                requestMethod: this.requestMethod,
                store: this.store
            });
        }else if(Ext.isObject(l) && !l.load){
            l = new Ext.ux.tree.TreeGridLoader(l);
        }
        this.loader = l;
                            
        Ext.ux.tree.TreeGrid.superclass.initComponent.call(this);                    
        
        this.initColumns();
        
        if(this.enableSort) {
            this.treeGridSorter = new Ext.ux.tree.TreeGridSorter(this, this.enableSort);
        }
        
        if(this.columnResize){
            this.colResizer = new Ext.tree.ColumnResizer(this.columnResize);
            this.colResizer.init(this);
        }
        
        var c = this.columns;
        if(!this.internalTpl){                                
            this.internalTpl = new Ext.XTemplate(
                '<div class="x-grid3-header">',
                    '<div class="x-treegrid-header-inner">',
                        '<div class="x-grid3-header-offset">',
                            '<table style="table-layout: fixed;" cellspacing="0" cellpadding="0" border="0"><colgroup><tpl for="columns"><col /></tpl></colgroup>',
                            '<thead><tr class="x-grid3-hd-row">',
                            '<tpl for="columns">',
                            '<td class="x-grid3-hd x-grid3-cell x-treegrid-hd" style="text-align: {align};" id="', this.id, '-xlhd-{#}">',
                                '<div class="x-grid3-hd-inner x-treegrid-hd-inner" unselectable="on">',
                                     this.enableHdMenu ? '<a class="x-grid3-hd-btn" href="#"></a>' : '',
                                     '{header}<img class="x-grid3-sort-icon" src="', Ext.BLANK_IMAGE_URL, '" />',
                                 '</div>',
                            '</td></tpl>',
                            '</tr></thead>',
                        '</table>',
                    '</div></div>',
                '</div>',
                '<div class="x-treegrid-root-node">',
                    '<table class="x-treegrid-root-table" cellpadding="0" cellspacing="0" style="table-layout: fixed;"></table>',
                '</div>'
            );
        }
        
        if(!this.colgroupTpl) {
            this.colgroupTpl = new Ext.XTemplate(
                '<colgroup><tpl for="columns"><col style="width: {width}px"/></tpl></colgroup>'
            );
        }
    },

    initColumns : function() {
        var cs = this.columns,
            len = cs.length, 
            columns = [],
            i, c;

        for(i = 0; i < len; i++){
            c = cs[i];
            if(!c.isColumn) {
                c.xtype = c.xtype ? (/^tg/.test(c.xtype) ? c.xtype : 'tg' + c.xtype) : 'tgcolumn';
                c = Ext.create(c);
            }
            c.init(this);
            columns.push(c);
            
            if(this.enableSort !== false && c.sortable !== false) {
                c.sortable = true;
                this.enableSort = true;
            }
        }

        this.columns = columns;
    },

    onRender : function(){
        Ext.tree.TreePanel.superclass.onRender.apply(this, arguments);

        this.el.addClass('x-treegrid');
        
        this.outerCt = this.body.createChild({
            cls:'x-tree-root-ct x-treegrid-ct ' + (this.useArrows ? 'x-tree-arrows' : this.lines ? 'x-tree-lines' : 'x-tree-no-lines')
        });
        
        this.internalTpl.overwrite(this.outerCt, {columns: this.columns});
        
        this.mainHd = Ext.get(this.outerCt.dom.firstChild);
        this.innerHd = Ext.get(this.mainHd.dom.firstChild);
        this.innerBody = Ext.get(this.outerCt.dom.lastChild);
        this.innerCt = Ext.get(this.innerBody.dom.firstChild);
        
        this.colgroupTpl.insertFirst(this.innerCt, {columns: this.columns});
        
        if(this.hideHeaders){
            this.el.child('.x-grid3-header').setDisplayed('none');
        }
        else if(this.enableHdMenu !== false){
            this.hmenu = new Ext.menu.Menu({id: this.id + '-hctx'});
            if(this.enableColumnHide !== false){
                this.colMenu = new Ext.menu.Menu({id: this.id + '-hcols-menu'});
                this.colMenu.on({
                    scope: this,
                    beforeshow: this.beforeColMenuShow,
                    itemclick: this.handleHdMenuClick
                });
                this.hmenu.add({
                    itemId:'columns',
                    hideOnClick: false,
                    text: this.columnsText,
                    menu: this.colMenu,
                    iconCls: 'x-cols-icon'
                });
            }
            this.hmenu.on('itemclick', this.handleHdMenuClick, this);
        }
    },

    setRootNode : function(node){
        node.attributes.uiProvider = Ext.ux.tree.TreeGridRootNodeUI;        
        node = Ext.ux.tree.TreeGrid.superclass.setRootNode.call(this, node);
        if(this.innerCt) {
            this.colgroupTpl.insertFirst(this.innerCt, {columns: this.columns});
        }
        return node;
    },
    
    clearInnerCt : function(){
        if(Ext.isIE){
            var dom = this.innerCt.dom;
            while(dom.firstChild){
                dom.removeChild(dom.firstChild);
            }
        }else{
            Ext.ux.tree.TreeGrid.superclass.clearInnerCt.call(this);
        }
    },
    
    initEvents : function() {
        Ext.ux.tree.TreeGrid.superclass.initEvents.apply(this, arguments);

        this.mon(this.innerBody, 'scroll', this.syncScroll, this);
        this.mon(this.innerHd, 'click', this.handleHdDown, this);
        this.mon(this.mainHd, {
            scope: this,
            mouseover: this.handleHdOver,
            mouseout: this.handleHdOut
        });
    },
    
    onResize : function(w, h) {
        Ext.ux.tree.TreeGrid.superclass.onResize.apply(this, arguments);
        
        var bd = this.innerBody.dom;
        var hd = this.innerHd.dom;

        if(!bd){
            return;
        }

        if(Ext.isNumber(h)){
            bd.style.height = this.body.getHeight(true) - hd.offsetHeight + 'px';
        }

        if(Ext.isNumber(w)){                        
            var sw = Ext.num(this.scrollOffset, Ext.getScrollBarWidth());
            if(this.reserveScrollOffset || ((bd.offsetWidth - bd.clientWidth) > 10)){
                this.setScrollOffset(sw);
            }else{
                var me = this;
                setTimeout(function(){
                    me.setScrollOffset(bd.offsetWidth - bd.clientWidth > 10 ? sw : 0);
                }, 10);
            }
        }
    },

    updateColumnWidths : function() {
        var cols = this.columns,
            colCount = cols.length,
            groups = this.outerCt.query('colgroup'),
            groupCount = groups.length,
            c, g, i, j;

        for(i = 0; i<colCount; i++) {
            c = cols[i];
            for(j = 0; j<groupCount; j++) {
                g = groups[j];
                g.childNodes[i].style.width = (c.hidden ? 0 : c.width) + 'px';
            }
        }
        
        for(i = 0, groups = this.innerHd.query('td'), len = groups.length; i<len; i++) {
            c = Ext.fly(groups[i]);
            if(cols[i] && cols[i].hidden) {
                c.addClass('x-treegrid-hd-hidden');
            }
            else {
                c.removeClass('x-treegrid-hd-hidden');
            }
        }

        var tcw = this.getTotalColumnWidth();                        
        Ext.fly(this.innerHd.dom.firstChild).setWidth(tcw + (this.scrollOffset || 0));
        this.outerCt.select('table').setWidth(tcw);
        this.syncHeaderScroll();    
    },
                    
    getVisibleColumns : function() {
        var columns = [],
            cs = this.columns,
            len = cs.length,
            i;
            
        for(i = 0; i<len; i++) {
            if(!cs[i].hidden) {
                columns.push(cs[i]);
            }
        }        
        return columns;
    },

    getTotalColumnWidth : function() {
        var total = 0;
        for(var i = 0, cs = this.getVisibleColumns(), len = cs.length; i<len; i++) {
            total += cs[i].width;
        }
        return total;
    },

    setScrollOffset : function(scrollOffset) {
        this.scrollOffset = scrollOffset;                        
        this.updateColumnWidths();
    },

    // private
    handleHdDown : function(e, t){
        var hd = e.getTarget('.x-treegrid-hd');

        if(hd && Ext.fly(t).hasClass('x-grid3-hd-btn')){
            var ms = this.hmenu.items,
                cs = this.columns,
                index = this.findHeaderIndex(hd),
                c = cs[index],
                sort = c.sortable;
                
            e.stopEvent();
            Ext.fly(hd).addClass('x-grid3-hd-menu-open');
            this.hdCtxIndex = index;
            
            this.fireEvent('headerbuttonclick', ms, c, hd, index);
            
            this.hmenu.on('hide', function(){
                Ext.fly(hd).removeClass('x-grid3-hd-menu-open');
            }, this, {single:true});
            
            this.hmenu.show(t, 'tl-bl?');
        }
        else if(hd) {
            var index = this.findHeaderIndex(hd);
            this.fireEvent('headerclick', this.columns[index], hd, index);
        }
    },

    // private
    handleHdOver : function(e, t){                    
        var hd = e.getTarget('.x-treegrid-hd');                        
        if(hd && !this.headersDisabled){
            index = this.findHeaderIndex(hd);
            this.activeHdRef = t;
            this.activeHdIndex = index;
            var el = Ext.get(hd);
            this.activeHdRegion = el.getRegion();
            el.addClass('x-grid3-hd-over');
            this.activeHdBtn = el.child('.x-grid3-hd-btn');
            if(this.activeHdBtn){
                this.activeHdBtn.dom.style.height = (hd.firstChild.offsetHeight-1)+'px';
            }
        }
    },
    
    // private
    handleHdOut : function(e, t){
        var hd = e.getTarget('.x-treegrid-hd');
        if(hd && (!Ext.isIE || !e.within(hd, true))){
            this.activeHdRef = null;
            Ext.fly(hd).removeClass('x-grid3-hd-over');
            hd.style.cursor = '';
        }
    },
                    
    findHeaderIndex : function(hd){
        hd = hd.dom || hd;
        var cs = hd.parentNode.childNodes;
        for(var i = 0, c; c = cs[i]; i++){
            if(c == hd){
                return i;
            }
        }
        return -1;
    },
    
    // private
    beforeColMenuShow : function(){
        var cols = this.columns,  
            colCount = cols.length,
            i, c;                        
        this.colMenu.removeAll();                    
        for(i = 1; i < colCount; i++){
            c = cols[i];
            if(c.hideable !== false){
                this.colMenu.add(new Ext.menu.CheckItem({
                    itemId: 'col-' + i,
                    text: c.header,
                    checked: !c.hidden,
                    hideOnClick:false,
                    disabled: c.hideable === false
                }));
            }
        }
    },
                    
    // private
    handleHdMenuClick : function(item){
        var index = this.hdCtxIndex,
            id = item.getItemId();
        
        if(this.fireEvent('headermenuclick', this.columns[index], id, index) !== false) {
            index = id.substr(4);
            if(index > 0 && this.columns[index]) {
                this.setColumnVisible(index, !item.checked);
            }     
        }
        
        return true;
    },
    
    setColumnVisible : function(index, visible) {
        this.columns[index].hidden = !visible;        
        this.updateColumnWidths();
    },

    /**
     * Scrolls the grid to the top
     */
    scrollToTop : function(){
        this.innerBody.dom.scrollTop = 0;
        this.innerBody.dom.scrollLeft = 0;
    },

    // private
    syncScroll : function(){
        this.syncHeaderScroll();
        var mb = this.innerBody.dom;
        this.fireEvent('bodyscroll', mb.scrollLeft, mb.scrollTop);
    },

    // private
    syncHeaderScroll : function(){
        var mb = this.innerBody.dom;
        this.innerHd.dom.scrollLeft = mb.scrollLeft;
        this.innerHd.dom.scrollLeft = mb.scrollLeft; // second time for IE (1/2 time first fails, other browsers ignore)
    },
    
    registerNode : function(n) {
        Ext.ux.tree.TreeGrid.superclass.registerNode.call(this, n);
        if(!n.uiProvider && !n.isRoot && !n.ui.isTreeGridNodeUI) {
            n.ui = new Ext.ux.tree.TreeGridNodeUI(n);
        }
    }
});

Ext.reg('treegrid', Ext.ux.tree.TreeGrid);

// Fix ext shadow shim bug
Ext.override(Ext.TabPanel, {
	listeners: {
		tabchange: function(tabPanel) {
			if (tabPanel.findParentByType('window')) {
				tabPanel.findParentByType('window').syncShadow();
			}
		}
	}
});

Ext.ns('Ext.ux.form.HtmlEditor');
Ext.ux.form.HtmlEditor.Formatblock = Ext.extend(Ext.util.Observable , {

	// private
	init: function(cmp){
		this.cmp = cmp;
		this.cmd = 'FormatBlock';
		this.store = new Ext.data.SimpleStore({
			fields: ['tag', 'name'],
			data : [
				['p', 'P'],
				['h1', 'H1'],
				['h2', 'H2'],
				['h3', 'H3'],
				['h4', 'H4'],
				['pre', 'PRE']
			]
		});
		this.cmp.on('render', this.onRender, this);
		this.cmp.on('initialize', this.onInit, this, {delay:100, single: true});
	},
	// private
	onInit: function(){
		Ext.EventManager.on(this.cmp.getDoc(), {
			'mousedown': this.onEditorEvent,
			'dblclick': this.onEditorEvent,
			'click': this.onEditorEvent,
			'keyup': this.onEditorEvent,
			buffer:100,
			scope: this
		});
	},

	// private
	onRender: function() {
		var tb = this.cmp.getToolbar();
		this.formatSelector = this.createFormatSelector();
		tb.add(this.formatSelector);
	},

	//private
	createFormatSelector: function(){
		var combo = new Ext.form.ComboBox({
			store: this.store,
			displayField:'name',
			valueField:'tag',
			typeAhead: true,
			mode: 'local',
			width: 80,
			triggerAction: 'all',
			emptyText:'...',
			valueNotFoundText: '...',
			forceSelection: true,
			editable: false,
			listWidth: 120,
			selectOnFocus:true,
			listeners:{
				scope: this,
				'select': function(combo, record, index){
					tag = record.data.tag;
					this.insertFormatblock(tag);
					this.cmp.getToolbar().focus();
					this.cmp.deferFocus();
					this.formatSelector.reset();
				}
			}
		});
		return combo;
	},

	insertFormatblock : function(val) {
		if (val.indexOf('<') == -1)
			val = '<' + val + '>';
		if (Ext.isGecko)
			val = val.replace(/<(div|blockquote|code|dt|dd|dl|samp)>/gi, '$1');
		this.cmp.relayCmd('FormatBlock', val);
	},

	getSelection : function() {
	  win = this.cmp.getWin();
		return win.getSelection ? win.getSelection() : win.document.selection;
	},

	getRange : function() {
		var win = this.cmp.getWin(), s, r;

		try {
			if (s = this.getSelection())
				r = s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : win.document.createRange());
		} catch (ex) {	}

		// No range found then create an empty one
		// This can occur when the editor is placed in a hidden container element on Gecko
		// Or on IE when there was an exception
		if (!r)
			r = Exi.isIE ? win.document.body.createTextRange() : win.document.createRange();

		return r;
	},	

	getNode: function(){
		var elem, e, r = this.getRange(), s = this.getSelection();
		if (!Ext.isIE) {
			// Range maybe lost after the editor is made visible again
			if (!r)
				return this.cmp.getDoc().dom.getRoot();

			e = r.commonAncestorContainer;
			// Handle selection a image or other control like element such as anchors
			if (!r.collapsed) {
				// If the anchor node is a element instead of a text node then return this element
				if (Ext.isWebKit && s.anchorNode && s.anchorNode.nodeType == 1)
					return s.anchorNode.childNodes[s.anchorOffset]; 

				if (r.startContainer == r.endContainer) {
					if (r.startOffset - r.endOffset < 2) {
						if (r.startContainer.hasChildNodes())
							e = r.startContainer.childNodes[r.startOffset];
					}
				}
			}
			elem = e.parentNode;
		} else {
			if (r.item)
				elem = r.item(0);
			else
				elem = r.parentElement();
		}
		return Ext.get(elem);
	},

	getBlocklevelElement: function(n){
		if(n){
			if (/^(P|DIV|H[1-6]|ADDRESS|BODY|BLOCKQUOTE|PRE)$/.test(n.dom.nodeName)){
				return n;
			} else {
				return this.getBlocklevelElement(n.findParentNode());
			}
		}
		return null;
	},	

	// private
	onEditorEvent: function(){
		var fs = this.formatSelector, r, p;
		p = this.getBlocklevelElement(this.getNode());
		if (p && p.dom && p.dom.nodeName != 'BODY'){
			fs.setValue(p.dom.nodeName.toLowerCase());
		} else {
			fs.reset();
		}
	}

});