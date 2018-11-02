/* global go, Ext */

//todo create base component for entities

go.modules.community.addressbook.TreeLoader = Ext.extend(Ext.tree.TreeLoader, {

	entityStore: null,
	
	loading : false,
	
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
			
			if(node.attributes.isAddressBook) {
				 this.requestGroups(node, callback, scope || node);
			} else
			{
				this.requestEntityData(node, callback, scope || node);
			}
		}
	},
	
	constructor : function(config) {		
		go.modules.community.addressbook.TreeLoader.superclass.constructor.call(this, config);
		this.initEntityStore();
	},
	
	initEntityStore : function() {
		if(Ext.isString(this.entityStore)) {
			this.entityStore = go.Stores.get(this.entityStore);
			if(!this.entityStore) {
				throw "Invalid 'entityStore' property given to component"; 
			}
		}
//		this.entityStore.on('changes',this.onChanges, this);		
//
//		this.on('beforedestroy', function() {
//			this.entityStore.un('changes', this.onChanges, this);
//		}, this);
	},

	
	requestGroups : function(node, callback, scope) {
						
		go.Stores.get("AddressBookGroup").get(node.attributes.entity.groups, function(groups){
			var result = [];
			
			groups.forEach(function(group) {
				result.push({
					id: "group-" + group.id,
					iconCls: 'ic-group',
					text: group.name,
					//leaf: true, don't use leaf because this doesn't allow dropping contacts anymore
					children: [],
					expanded: true,
					entity: group,
					isGroup: true
				});
			});
			
			var response = {
				argument: {callback: callback, node: node, scope: scope},
				responseData:result
			};			
			
			this.handleResponse(response);
			
			this.loading = false;
		}, this);
		
		
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

		
		this.result = this.getItemList(this.entityStore.entity.name + "/query", params, function (getItemListResponse) {
			this.entityStore.get(getItemListResponse.ids, function (items) {
				var result = [{
						leaf: true,
						iconCls: "ic-star",
						text: t("All contacts"),
						id: "all"
				}];
				
				items.forEach(function(entity) {
					result.push({
						id: "addressbook-" + entity.id,
						entityId: entity.id||null,
						entity: entity,
						isAddressBook: true,
						expanded: entity.groups.length === 0,						
						text: entity.name,
						nodeType: 'async',
						children: entity.groups.length === 0 ? [] : null
					});
				});
				
				var response = {
					argument: {callback: callback, node: options.node, scope: scope},
					responseData:result
				};
				
				this.handleResponse(response);
				
				this.loading = false;
//				callback.call(scope, options, true, result); //????
			},this);

		}, this);
	},

	getItemList: function (method, params, callback, scope) {	
		
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
			},
			scope: scope
		});
	},
	
	// Fix uiProvider loading when given in the baseAttrs config of nodes
	createNode: function (attr) {
		Ext.applyIf(attr, this.baseAttrs || {});

		if (this.applyLoader !== false) {
			attr.loader = this;
		}

		if (typeof attr.uiProvider === 'string') {
			attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
		}

		return(attr.leaf ?
						new Ext.tree.TreeNode(attr) :
						new Ext.tree.AsyncTreeNode(attr));
	}
});
