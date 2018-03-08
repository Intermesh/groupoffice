Ext.namespace('GO.menu');

GO.menu.JsonMenu = function(config) {
	GO.menu.JsonMenu.superclass.constructor.call(this,config);

	this.on('show', this.onMenuLoad, this);
	//this.store.on('beforeload', this.onBeforeLoad, this);
	this.store.on('load', this.onLoad, this);
	this.addEvents('load');
};

Ext.extend(GO.menu.JsonMenu, Ext.menu.Menu, {

	loadingText: t("Loading..."),

	loaded:      false,

	onMenuLoad: function(){

		if(!this.store.loaded)
			this.store.load();
		else if(!this.loaded){
			this.updateMenuItems();
		}
	},

	updateMenuItems: function() {
		if(this.rendered){
			this.removeAll();
			this.el.sync();

			var records = this.store.getRange();

			for(var i=0, len=records.length; i<len; i++){
				if (records[i].json.handler) {
					eval("records[i].json.handler = "+records[i].json.handler);
				}
				if (records[i].json.menu) {
					eval("records[i].json.menu = "+records[i].json.menu);
				}

				this.add(records[i].json);
			}

			this.fireEvent('load', this, records);
			this.loaded = true;
		}
	},

	onLoad: function(store, records){
		this.updateMenuItems();
	}
});
