//TemplateSelectionDialog

GO.email.TemplateSelectionDialog = Ext.extend(GO.Window,{
	closeAction: 'hide',
	height: 340,
	width: 660,
	
	initComponent : function(){
		
		//TemplateGridPanel
		
		Ext.apply(this.grid,{
			region:'center'
		})
		
		
		this.grid = new GO.email.TemplateGridPanel(this.grid);
		
		
		Ext.apply(this,{
			title: t("E-mail template", "addressbook"),
			layout:"border",
			items:[
				this.grid
			]
		})
		
		GO.email.TemplateSelectionDialog.superclass.initComponent.call(this);
	}
})
