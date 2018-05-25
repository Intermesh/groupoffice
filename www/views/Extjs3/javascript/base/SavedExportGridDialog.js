GO.base.SavedExportGridDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	jsonPost: true,
	className : null,
	
	initComponent : function(){
		
		Ext.apply(this, {
			loadOnNewModel : false,
			title:'Saved Exports',
			formControllerUrl: 'core/export',
			height:500,
			enableOkButton : false,
			enableApplyButton : false,
			enableCloseButton : true
		});
		
		GO.base.SavedExportGridDialog.superclass.initComponent.call(this);	
	},
	buildForm : function () {

		this.savedExportGrid = new GO.base.SavedExportGrid();

		this.addPanel(this.savedExportGrid);
	},
	setClass : function(className){
	
		this.className = className;
		this.savedExportGrid.setClass(className);

	}
});
