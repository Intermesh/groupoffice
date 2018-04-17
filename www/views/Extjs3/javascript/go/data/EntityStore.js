go.data.EntityStore = Ext.extend(go.flux.Store, {

	state: null,

	entity: null,
	
	constructor : function(config) {
		go.data.EntityStore.superclass.constructor.call(this, config);
		
		this.restoreState();		
	},
	
	restoreState : function() {
//		if(!window.localStorage.entityStores) {
//			window.localStorage.entityStores = {};
//		}
//		
//		var json = window.localStorage["entityStore-" + this.entity.name];		
//		if(json) {
//			var state = JSON.parse(json);			
//			this.data = state.data;
//			this.state = state.state;			
//		}
	},
	
	saveState : function() {
//		var state = JSON.stringify({
//			state: this.state,
//			data: this.data
//		});
		
//		if(!window.localStorage.entityStores) {
//			window.localStorage.entityStores = {};
//		}		
		//window.localStorage["entityStore-" + this.entity.name] = state;		
	},


	receive: function (action) {		

		switch (action.type) {
			case this.entity.name + "/get":
				
				// If no items are available, don't continue
				if(!action.payload.list){
					return;
				}
				
				//add data from get response
				for(var i=0,l=action.payload.list.length;i < l; i++) {
					var entity = action.payload.list[i];
					this.data[entity.id] = entity;
				};
				
				this.saveState();			
				
				break;

			case this.entity.name + "/query":
				//if a list call was made then fetch updates if state mismatch
				if (this.state && action.payload.state !== this.state) {
					this.getUpdates();
				}
				break;

			case this.entity.name + "/set":
				//update state from set we initiated
				this.state = action.payload.newState;
				break;
		}
	},

	getUpdates: function (cb, scope) {
		if (this.state) {
			var clientCallId = go.Jmap.request({
				method: this.entity.name + "/getUpdates",
				params: {
					sinceState: this.state
				}
			});

			go.Jmap.request({
				method: this.entity.name + "/get",
				params: {
					"#ids": {
						resultOf: clientCallId,
						path: '/changed'
					}
				},
				callback: function(options, success, response) {
					if(cb) {
						cb.call(scope || this, this);
					}
				},
				scope: this
			});
		} else
		{
			go.Jmap.request({
				method: this.entity.name + "/get",
				callback: function (options, success, response) {
					this.state = response.state;
				
					cb.call(scope || this, this);
				},
				scope: this
			});
		}

	},

	/**
	 * Get an entity
	 * 
	 * @param {type} ids
	 * @param {type} callback
	 * @returns {undefined}
	 */
	get: function (ids, callback, scope) {

		if(!ids){
			ids = [];
		}

		var entities = [],
						unknownIds = [];

		for (var i = 0; i < ids.length; i++) {
			var id = ids[i];
			this.data[id] ? entities.push(this.data[id]) : unknownIds.push(id);
		}

		if (unknownIds.length) {
			go.Jmap.request({
				method: this.entity.name + "/get",
				params: {
					ids: unknownIds
				},
				callback: function (options, success, response) {
					if(!success) {
						return;
					}
					
					if(!GO.util.empty(response.notFound)) {
						console.log("Item not found", response);
						return;
					}
					this.state = response.state;					
					this.get(ids, callback, scope);
					
				},
				scope: this
			});
		} else {
			callback.call(scope || this, entities);
		}
	},
	
	findBy : function(fn, scope, startIndex) {
		startIndex = startIndex || 0;
		var data = Object.values(this.data);
		for(var i = startIndex, l = data.length; i < l; i++) {
			if(fn.call(scope || this, data[i])) {
				return data[i];
			}
		}
	},

	/**
	 * Create or update entities
	 * 
	 * 
	 * ```
	 * 
	 * go.Stores.get("link").set({
	 *		create: links
	 *	}, function(options, success, response){}, this);
	 * 
	 * ```
	 * 
	 * @param {int} id
	 * @param {object} values Key value object with entity properties
	 * @param {type} callback A function called with success, values, response, options
	 * @returns {undefined}
	 */
	set: function (params, cb, scope) {
		params.ifInState = this.state;

		go.Jmap.request({
			method: this.entity.name + "/set",
			params: params,
			scope: this,
			callback: function (options, success, response) {
				
				var changes = [];
				
				if(response.created) {
					for(var clientId in response.created) {
						var entity = Ext.apply(params.create[clientId], response.created[clientId]);
						changes.push(entity);
						this.data[entity.id] = entity;
					}
				}
				
				if(response.updated) {
					for(var serverId in response.updated) {
						var entity = Ext.apply(params.update[serverId], response.updated[serverId]);
						changes.push(entity);
						this.data[entity.id] = entity;
					}
				}
				
				this.state = response.newState;
				
				this.saveState();
								
				go.flux.Dispatcher.dispatch(this.entity.name + "Updated", {
					state: this.state,
					list: changes
				});				
				
				
				if(response.destroyed) {
					for(var i =0, l = response.destroyed.length; i < l; i++) {
						delete this.data[response.destroyed[i]];
					}
				
					go.flux.Dispatcher.dispatch(this.entity.name + "Destroyed", {list: response.destroyed, state: response.newState});
				}

				if(cb) {
					cb.call(scope || this, options, success, response);
				}

			}
		});
	}
});
