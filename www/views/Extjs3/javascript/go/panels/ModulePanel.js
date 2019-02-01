go.panels.ModulePanel = Ext.extend(Ext.Panel, {
	route : function(id, entity) {
		
		//cast to int if nummeric
		var int = parseInt(id);
		if(int == id) {
			id = int;
		}
		
		if(!this.rendered) {
			//if module is not rendered then postpone untill after render.
			this.on("afterrender", function() {
				this.route(id, entity);
			}, this, {single: true});
			return;
		}
		
		//Try to find detail view for entity and load it.
		var detailViews = this.findBy(function(item) {
			return item.isXType("detailview") && item.entityStore && item.entityStore.entity === entity;
		});
		
		detailViews.forEach(function(dv){
			dv.load(id);

			//For responsive layout
			dv.show();
		});
		
		//try to find grid for entity and select correct row
		var grids = this.findBy(function(item) {
			return (item.isXType("gogrid") || item.isXType("goeditorgrid")) && item.store && item.store.entityStore && item.store.entityStore.entity === entity;
		});
		
		grids.forEach(function(g) {
			var selected = [];
			var record = g.store.getById(id);
			if(record) {
				selected.push(record);
			}
			g.getSelectionModel().selectRecords(selected);
		});		
	}
});

Ext.reg("modulepanel", go.panels.ModulePanel);
