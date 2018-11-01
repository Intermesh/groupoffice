GO.data.LiveStore = Ext.extend(Ext.ux.grid.livegrid.Store, {

	constructor : function(config){
		
		Ext.apply(this,{
			remoteSort: true,
			autoLoad: true,
			bufferSize : 100,
			sortInfo   : {field: 'id', direction: 'ASC'},
			reader : new Ext.ux.grid.livegrid.JsonReader({
                root            : 'results',
                //versionProperty : 'version',
                totalProperty   : 'total',
                id              : 'id'
			}, config.fields)
		});
		
		GO.data.LiveStore.superclass.constructor.call(this, config);
		
	},
	reload : function(options){
		
		if(this.lastOptions && this.lastOptions.params && this.lastOptions.params.add){
			delete this.lastOptions.params.add;
		}
		
		GO.data.LiveStore.superclass.reload.call(this, options);
	}
});
