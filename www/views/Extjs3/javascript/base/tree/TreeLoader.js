GO.base.tree.TreeLoader = Ext.extend(Ext.tree.TreeLoader,{
	constructor : function(config){
		//config = config || {};
		GO.base.tree.TreeLoader.superclass.constructor.call(this, config);
		
		this.on('loadexception',function(loader, node, response ){
			var result = Ext.decode(response.responseText);
			if(result.feedback){
				GO.errorDialog.show(result.feedback);
			}
		}, this);
	}
})