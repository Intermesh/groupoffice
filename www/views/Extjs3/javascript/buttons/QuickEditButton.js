Ext.ns('GO.buttons');

GO.buttons.QuickEditButton = Ext.extend(Ext.Button,{
	
	buttonParams : null,
	ignoreButtonParams : false,
	
	initComponent : function(){
		
		Ext.applyIf(this,{
			iconCls: 'btn-edit',
			itemId:'quickedit',
			text: GO.lang.quickEdit,
			cls: 'x-btn-text-icon'
		});
				
		GO.buttons.QuickEditButton.superclass.initComponent.call(this);
	}
});

Ext.reg('quickeditbutton', GO.buttons.QuickEditButton);