go.flux.Store = Ext.extend(Ext.util.Observable,{
	constructor: function(config) {		
		go.flux.Store.superclass.constructor.call(this);
		
		config = config || {};
		
		this.data = {};
		this.scope = this;
		
		Ext.apply(this, config);
		
		this.addEvents("destroy");
		
		go.flux.Dispatcher.register(this);		
	},
	
	data: null,
	
	entity: null,
	scope: null,
	
	receive: function(action) {},
	
	destroy : function() {		
		this.fireEvent('destroy', this);
		
		this.purgeListeners();
	}
});

