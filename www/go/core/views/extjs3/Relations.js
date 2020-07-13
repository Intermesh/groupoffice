go.Relations = {

	/**
	 * @var {go.data.EntityStore}
	 */
  entityStore: null,
  get : function (entityStore, entity, relations) {

    this.entityStore = entityStore;
    this.watchRelations = {};

    var me = this;

    var promises = [];
    relations.forEach(function(relName) {
      promises.push(me.getRelation(relName, entity));
    });  

		return Promise.all(promises).then(function() {
			return {entity: entity, watch: me.watchRelations};
		});		
  },

  /**
	 * Create a promise that resolves the relational record data.
	 * 
	 * @param {string|object} relName Relation name or object with {name: "users", limit: 5}. This will only resolve the first 5 entities and put the total in record.json._meta.users.total
	 * @param {*any} record 
	 * 
	 * @retrun {Promise}
	 */
	getRelation : function(relName, entity) {

		var c = {};
		if(Ext.isObject(relName)) {
			c = relName;
			relName = c.name;
		}

		var relation = this.entityStore.entity.findRelation(relName);

		if(!relation) {
			return Promise.reject("Relation " + relName + " not found for " + this.entityStore.entity.name);
		}

		var key = this.resolveKey(relation.path + relation.fk, entity), me = this;

		if(!key) {
			console.warn("No key found for relation '" + this.entityStore.entity.name + "." +relName + "'", relation, entity);
      me.applyRelationEntity(relation.path + relName, entity, null);
			return Promise.resolve(null);
		}		

		if(Ext.isArray(key)) {

			if(c.limit) {
				entity._meta = entity._meta || {};
				entity._meta[relName] = {total: key.length};

				key = key.slice(0, c.limit);				
			}

			key.forEach(function(k) {
				me.watchRelation(relation.store, k);
			});

			return go.Db.store(relation.store).get(key).then(function(result) {
				me.applyRelationEntity(relName, entity, result.entities);
			});
		}

		this.watchRelation(relation.store, key);

		return go.Db.store(relation.store).single(key).then(function(relatedEntity) {
			me.applyRelationEntity(relName, entity, relatedEntity);
			return relatedEntity;
		});
	},

	/**
	 * Keeps record of relational entity stores and their id's. go.data.Stores uses this collection to listen for changes
	 * 
	 * @param {string} entity 
	 * @param {int} key 
	 */
	watchRelation : function(entity, key) {
		if(!this.watchRelations[entity]) {
			this.watchRelations[entity] = [];
		}

		if(this.watchRelations[entity].indexOf(key) === -1) {
			this.watchRelations[entity].push(key);
		}
	},

	/**
	 * Applies the entity data to the record.
	 * It also supports a path like "customFields.user"
	 * 
	 * This will become
	 * {
	 * 	"customFields" => {
	 * 		"user" => data
	 * 	}
	 * }
	 * @param {*} key 
	 * @param {*} record 
	 * @param {*} entities 
	 */
	applyRelationEntity : function(relName, record, entities) {
		var parts = relName.split("."),last = parts.pop(), current = record;

		parts.forEach(function(p) {
			if(!current[p]) {
				current[p] = {};
			}
			
			current = current[p];
		});

		if(Ext.isArray(current)) {
			current.forEach(function(item, index){
				item[last] = Ext.isArray(entities) ? entities[index] : entities;
			});
		}else{
			current[last] = entities;
		}
	},

	/**
	 * Resolves a key path eg. "customFields.user"
	 * 
	 * @param {*} key 
	 * @param {*} data 
	 */
	resolveKey : function(key, data) {
		if(!data) {
			return null;
		}
		var parts = key.split("."), part;
						
		for(var i = 0, l = parts.length; i < l; i++) {
			p = parts[i];
			if(Ext.isArray(data)) {
				var arr = [];
				data.forEach(function(i) {
					arr.push(i[p]);
				});
				data = arr;
			} else
			{
				if(!Ext.isDefined(data[p])) {
					return null;
				}
				data = data[p];
			}
			if(!data) {
				return null;
			}
		}
		
		return data;
	}
};