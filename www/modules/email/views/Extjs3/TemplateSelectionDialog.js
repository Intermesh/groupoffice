//TemplateSelectionDialog

GO.email.TemplateSelectionDialog = Ext.extend(GO.Window,{
	closeAction: 'hide',
	height: dp(600),
	width: dp(660),
	modal: true,
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
	},

	getSelected : function() {
		for(var i = 0, max = this.grid.store.getCount();i < max; i++) {
			var rec = this.grid.store.getAt(i);
			if (rec.get("checked")) {
				return rec;
			}
		}
		return null;
	}
})
