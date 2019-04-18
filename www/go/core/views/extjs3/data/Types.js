Ext.data.Types.RELATION = {
	isRelation: true,
	prefetch: true, //used in EntityStoreProxy
	convert: function(v, data) {

		// var entity = this.entity, relName = this.name;
		// var relation = entity.relations[relName];
		// return go.Db.store(relation.store).data[data[relation.fk]];
		return v;
	},
	sortType: Ext.data.SortTypes.none
};