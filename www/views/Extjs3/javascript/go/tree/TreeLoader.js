go.tree.TreeLoader = Ext.extend(Ext.tree.TreeLoader, {

	entityStore: null,

	load: function (node, callback, scope) {
		if (this.clearOnLoad) {
			while (node.firstChild) {
				node.removeChild(node.firstChild);
			}
		}
		if (this.doPreload(node)) { // preloaded json children
			this.runCallback(callback, scope || node, [node]);
		} else if (this.directFn || this.dataUrl || this.url) {
			this.requestData(node, callback, scope || node);
		} else if (this.entityStore) {
			this.requestEntityData(node, callback, scope || node);
		}
	},
	
	requestEntityData : function(node, callback, scope){

		if(this.fireEvent("beforeload", this, node, callback) !== false){
		
			var p = this.getParams(node);
			
			if(node.attributes.params) {
				go.util.mergeObjects(p, node.attributes.params);
			}
			
			this.doRequest(p,callback,scope,{node:node});
		}
	},

	doRequest: function (params, callback, scope, options) {

		var me = this;
		
		this.result = me.getItemList(this.entityStore.entity.name + "/query", params, function (getItemListResponse) {
			me.entityStore.get(getItemListResponse.ids, function (items) {
				var result = [];
				
				items.forEach(function(entity) {
					result.push({
//						id: entity.id,
						entityId: entity.id||null,
						entity: entity,
						leaf: !entity.hasChildren,
						text: entity.name //TODO this should be 
					});
				});
				
				var response = {
					argument: {callback: callback, node: options.node, scope: scope},
					responseData:result
				};

				me.handleResponse(response);
//				callback.call(scope, options, true, result); //????
			});

		});
	},

	getItemList: function (method, params, callback) {	
		
		//transfort sort parameters to jmap style
		if(params.sort) {
			params.sort = [params.sort + " " + params.dir];
			delete params.dir;
		}

		return go.Jmap.request({
			method: method,
			params: params,
			callback: function(options, success, response) {
				callback.call(this, response);
			}
		});
	},
	
	// Fix uiProvider loading when given in the baseAttrs config of nodes
	createNode: function (attr) {
		Ext.apply(attr, this.baseAttrs || {});

		if (this.applyLoader !== false) {
			attr.loader = this;
		}

		if (typeof attr.uiProvider == 'string') {
			attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
		}

		return(attr.leaf ?
						new Ext.tree.TreeNode(attr) :
						new Ext.tree.AsyncTreeNode(attr));
	}
});