GO.customfields.CustomFormPanel = function(config)
{
	if(!config)
	{
		config={};
	}
	
	config.autoScroll=true;
	config.border=false;
	config.hideLabel=true;
	config.hideMode='offsets';
	config.layout='form';
	config.defaultType = 'textfield';
	config.cls='go-form-panel';
	config.labelWidth=140;
	

	config.items=[];
	
	var formField;
	
	for(var i=0;i<config.customfields.length;i++)
	{
		var cf_cfg = {};
		if(config.customfields[i].readOnly) {
			cf_cfg.readOnly = config.customfields[i].readOnly;
			cf_cfg.hideTrigger = true;
		}
		formField = GO.customfields.getFormField(config.customfields[i],cf_cfg);
		if(formField)
			config.items.push(formField);
	}		
	

	GO.customfields.CustomFormPanel.superclass.constructor.call(this, config);		
}
Ext.extend(GO.customfields.CustomFormPanel, Ext.Panel,{

	setAllowBlank : function(item, allowBlank){
		item.allowBlank=allowBlank;
		
		//special datetime field that has twop fields
		if(item.df){
			item.df.allowBlank=allowBlank;
			item.tf.allowBlank=allowBlank;
		}
	},
	
	disableValidation : function(){
		this.items.each(function(i){
			
			this.setAllowBlank(i, true);
		}, this);
	},
	enableValidation : function(){
		for(var i=0;i<this.customfields.length;i++)
		{
			if(!GO.util.empty(this.customfields[i].required)){
				var index = this.items.findIndex('name', this.customfields[i].dataname);
				if (index<0)
					index = this.items.findIndex('hiddenName', this.customfields[i].dataname);
				if(index>=0){
					this.setAllowBlank(this.items.itemAt(index), false);
					
				}
			}
		}
	}
});

Ext.reg('customformpanel', GO.customfields.CustomFormPanel);
