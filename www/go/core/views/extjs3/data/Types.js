/**
 * Relation field type.
 * 
 * Will lookup the relation by name from the entity declaration in Module.js.
 * 
 * When the relation is a has many you can also specify limit = 5 to limit the number of entities to resolve.
 * The total will be stored in record.json._meta[relName].total.
 */
Ext.data.Types.RELATION = {
	isRelation: true,
	prefetch: true, //used in EntityStoreProxy	
	convert: function(v, data) {
		return v;
	},
	sortType: Ext.data.SortTypes.none // You can sort on propery name for example with sortType: function(entity) {return entity.name;}
};


/**
 * Promise field will be resolved before render
 * 
 * {
					name: "promiseTest",
					type:"promise",
					promise:function(data) {
						return go.Db.store("Search").query({
							filter: {
								entities: ["Event"],
								link: {
									entity: "Contact",
									id: data.id
								}
							}
						}).then(function(result) {
							if(result.ids.length === 0) {
								return "-";
							}

							return go.Db.store("Search").single(result.ids[0]).then(function(data) {
								return go.util.Format.dateTime(data.modifiedAt);
							});

						});
					}
				}
 * 
 */

Ext.data.Types.PROMISE = {
	isRelation: true,	
	promise: function(data) {
		return Promise.resolve();
	},
	convert: function(v, data) {
		return v;
	},
	sortType: Ext.data.SortTypes.none // You can sort on propery name for example with sortType: function(entity) {return entity.name;}
};