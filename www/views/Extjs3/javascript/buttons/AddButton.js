Ext.ns('GO.buttons');

GO.buttons.AddButton = Ext.extend(Ext.Button,{
	
	buttonParams : null,
	ignoreButtonParams : false,
	
	initComponent : function(){
		
		Ext.applyIf(this,{
			iconCls: 'btn-add',
			itemId:'add',
			disabled:!this.ignoreButtonParams,
			text: t("Add"),
			cls: 'primary'
		});
		
		if(this.grid && !this.ignoreButtonParams){
			this.grid.store.on('load', function(){
				this.buttonParams = this.grid.store.reader.jsonData.buttonParams;
				
				this.setDisabled(!this.buttonParams);

			}, this);
		}
		
		GO.buttons.AddButton.superclass.initComponent.call(this);
	}
});

Ext.reg('addbutton', GO.buttons.AddButton);
