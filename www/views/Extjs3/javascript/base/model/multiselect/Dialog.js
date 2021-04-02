GO.base.model.multiselect.dialog = function(config){
	
	
	this.multiselectPanel = new GO.base.model.multiselect.panel({
			url:config.url,
			columns:config.columns,
			fields:config.fields,
			model_id:config.model_id,
			addAttributes:config.addAttributes

	});
		
	delete config.url;
	delete config.columns;
	delete config.fields;
	delete config.model_id;
	delete config.addAttributes;

	
	Ext.apply(this, config);
	

	GO.base.model.multiselect.dialog.superclass.constructor.call(this, {
		layout: 'fit',
		modal:false,
		height:400,
		width:600,
		closeAction:'hide',
		title:config.title,
		items: this.multiselectPanel,
		buttons: [		
		{
			text: t("Close"),
			handler: function(){
				this.hide();
			},
			scope: this
		}]
	});
};

Ext.extend(GO.base.model.multiselect.dialog, GO.Window, {
	show : function(){
		GO.base.model.multiselect.dialog.superclass.show.call(this);
		this.multiselectPanel.store.load();
	}

});
