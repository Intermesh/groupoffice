/* global Ext, go */

go.tree.Node = Ext.extend(Ext.tree.AsyncTreeNode, {
	secondaryText: "",
	secondaryIconCls: "",

	constructor : function(attributes) {
		
		if(!attributes.uiProvider) {
			attributes.uiProvider = go.tree.TreeNodeUI;
		}		
		
	/**
		* Read-only. The text for this node. To change it use <code>{@link #setText}</code>.
		* @type String
		*/
		this.secondaryText = attributes.secondaryText;
		
		
		/**
		* Read-only. The text for this node. To change it use <code>{@link #setText}</code>.
		* @type String
		*/
		this.secondaryIconCls = attributes.secondaryIconCls;
		
		go.tree.Node.superclass.constructor.call(this, attributes);		
	},
	
	 /**
     * Sets the text for this node
     * @param {String} text
     */
    setSecondaryText : function(text){
        var oldText = this.secondaryText;
        this.secondaryText = this.attributes.secondaryText = text;
        if(this.rendered){ // event without subscribing
            this.ui.onSecondaryTextChange(this, text, oldText);
        }
        this.fireEvent('textchange', this, text, oldText);
    },
    
    /**
     * Sets the icon class for this node.
     * @param {String} cls
     */
    setIconCls : function(cls){
        var old = this.attributes.iconCls;
        this.attributes.iconCls = cls;
        if(this.rendered){
            this.ui.onSecondaryIconClsChange(this, cls, old);
        }
    }

//	load: function (callback, scope) {
//		
//	},
//	
//	query : function(params) {
//		//transfort sort parameters to jmap style
//		if(params.sort) {
//			params.sort = [params.sort + " " + params.dir];
//			delete params.dir;
//		}
//
//		return this.entiyStore.query(params,
//			function(options, success, response) {
//				this.loadEntities(response, function() {
//					callback.call(scope);
//				}, this)
//			},
//			this);
//	},
//	
//	loadEntities: function(response, callback, scope) {
//					
//			this.entityStore.get(response.ids, function (items) {
//					var result = [];
//
//					items.forEach(function(entity) {
//						result.push({
//							id: this.entityStore.entity.name + "-" + entity.id,
//							entityId: entity.id||null,
//							entity: entity,
//							text: this.textTpl.aply(entity),		
//							secondaryText: this.secondaryTextTpl.aply(entity)
//						});
//					});
//					
//					this.processResponse(result);				
//
//					this.loading = false;
//				}, this);
//	},
//
//	processResponse: function (o) {
//		this.beginUpdate();
//		for (var i = 0, len = o.length; i < len; i++) {
//			var n = this.createNode(o[i]);
//			if (n) {
//				this.appendChild(n);
//			}
//		}
//		this.endUpdate();		
//	},
//
//	createNode: function (attr) {
//		if (this.loader.baseAttrs) {
//			Ext.applyIf(attr, this.loader.baseAttrs);
//		}
//		attr.loader = this.loader;
//		
//		if (Ext.isString(attr.uiProvider)) {
//			attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
//		}
//		
//		return new go.tree.Node(attr);
//	}

});

Ext.tree.TreePanel.nodeTypes.groupoffice = go.tree.Node;