 /* global go, Ext */

go.tree.EntityLoader = Ext.extend(Ext.tree.TreeLoader, {

	entityStore: null,
	
	loading : false,	
	
	textTpl: '{name}',
	secondaryTextTpl: '',
	
	constructor : function(config) {

		Ext.applyIf(this, go.data.FilterTrait);

		this.initFilters();

		go.tree.EntityLoader.superclass.constructor.call(this, config);
		this.initEntityStore();
		
		if(!this.baseAttrs) {
			this.baseAttrs = {};
		}
		
		if(!this.baseAttrs.uiProvider) {
			this.baseAttrs.uiProvider = go.tree.TreeNodeUI;
		}
		
		this.textTpl = new Ext.XTemplate(this.textTpl);
		this.secondaryTextTpl = new Ext.XTemplate(this.secondaryTextTpl);
	},
	
	initEntityStore : function() {
		if(Ext.isString(this.entityStore)) {
			this.entityStore = go.Db.store(this.entityStore);
			if(!this.entityStore) {
				throw "Invalid 'entityStore' property given to component"; 
			}
		}
	},	
	
	
	load: function (node, callback, scope) {
		if (this.clearOnLoad) {
			while (node.firstChild) {
				node.removeChild(node.firstChild);
			}
		}
		if (this.doPreload(node)) { // preloaded json children
			this.runCallback(callback, scope || node, [node]);
		} else {			
			this.loading = true;			
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
	
	convertEntityToNode : function(entityData) {

		var encoded = go.util.Format.htmlEncode(entityData);
		return {
			id: this.entityStore.entity.name + "-" + entityData.id,			
			data: entityData,						
			entity:  this.entityStore.entity,
			text: this.textTpl.apply(encoded),
			secondaryText: this.secondaryTextTpl.apply(encoded),
			nodeType: 'groupoffice',
			loader: this
		};
	},

	doRequest: function (params, callback, scope, options) {

		//transfort sort parameters to jmap style
		// if(params.sort) {
		// 	params.sort = [params.sort + " " + params.dir];
		// 	delete params.dir;
		// }
		
		this.result = this.entityStore.query(params, function (response) {
			this.entityStore.get(response.ids, function (entities) {
				var response = {
					argument: {callback: callback, node: options.node, scope: scope},
					responseData: entities.map(this.convertEntityToNode, this)
				};
				
				this.handleResponse(response);
				
				this.loading = false;
			},this);
		}, this);
	}
});

