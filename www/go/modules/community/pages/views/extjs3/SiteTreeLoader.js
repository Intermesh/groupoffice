go.modules.community.pages.SiteTreeLoader = Ext.extend(Ext.tree.TreeLoader, {

    entityStore: null,

    loading: false,
    siteId: '',

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

	    this.loading = true;

	    if (node.attributes.isPage) {
//				 this.requestGroups(node, callback, scope || node);
		//todo: generate nodes for the page headers here
		//make sure to test with pages without any headers (thus no nodes).
		callback.call();
		this.loading = false;
	    } else
	    {
		this.requestEntityData(node, callback, scope || node);
	    }
	}
    },

//	requestGroups : function(node, callback, scope) {
//						
//		go.Stores.get("AddressBookGroup").get(node.attributes.entity.groups, function(groups){
//			var result = [];
//			
//			groups.forEach(function(group) {
//				result.push({
//					id: "group-" + group.id,
//					iconCls: 'ic-group',
//					text: group.name,
//					//leaf: true, don't use leaf because this doesn't allow dropping contacts anymore
//					children: [],
//					expanded: true,
//					entity: group,
//					isGroup: true
//				});
//			});
//			
//			var response = {
//				argument: {callback: callback, node: node, scope: scope},
//				responseData:result
//			};
//			
//			this.loading = false;
//			this.handleResponse(response);
//		}, this)
//		
//		
//	},

    requestEntityData: function (node, callback, scope) {

	if (this.fireEvent("beforeload", this, node, callback) !== false) {

	    var p = this.getParams(node);

	    if (node.attributes.params) {
		go.util.mergeObjects(p, node.attributes.params);
	    }

	    this.doRequest(p, callback, scope, {node: node});
	}
    },

    doRequest: function (params, callback, scope, options) {


	this.result = this.getItemList(this.entityStore.entity.name + "/query", params, function (getItemListResponse) {
	    this.entityStore.get(getItemListResponse.ids, function (items) {
		var result = [];

		items.forEach(function (entity) {
		    result.push({
			id: "page-" + entity.id,
			entityId: entity.id || null,
			entitySlug: entity.slug,
			entity: entity,
			isPage: true,
			sortOrder: entity.sortOrder,
//						expanded: entity.groups.length == 0,						
			text: entity.pageName,
			nodeType: 'async',
//						children: entity.groups.length == 0 ? [] : null
		    });
		});

		var response = {
		    argument: {callback: callback, node: options.node, scope: scope},
		    responseData: result
		};

		this.loading = false;
		this.handleResponse(response);
//				callback.call(scope, options, true, result); //????
	    }, this);

	}, this);
    },

    getItemList: function (method, params, callback, scope) {

	//transfort sort parameters to jmap style
	if (params.sort) {
	    params.sort = [params.sort + " " + params.dir];
	    delete params.dir;
	}

	return go.Jmap.request({
	    method: method,
	    params: params,
	    callback: function (options, success, response) {
		callback.call(this, response);
	    },
	    scope: scope
	});
    },

    createNode: function (attr) {
	Ext.applyIf(attr, this.baseAttrs || {});

	if (this.applyLoader !== false) {
	    attr.loader = this;
	}

	if (typeof attr.uiProvider == 'string') {
	    attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
	}

	return(attr.leaf ?
		new Ext.tree.TreeNode(attr) :
		new Ext.tree.AsyncTreeNode(attr));
    },

    getParams: function (node) {
	var filter = {
	    siteId: this.siteId
	};
	return {
	    filter: filter
	};
    }

});