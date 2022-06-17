GO.calendar.CategoryDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	hidePermissions : true,
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'category',
			title:t("Category", "calendar"),
			formControllerUrl: 'calendar/category',
      width: 550,
      height: 600
		});
		
		GO.calendar.CategoryDialog.superclass.initComponent.call(this);	
	},
	
	buildForm : function () {
		
		this.colorField = new GO.form.ColorField({
			fieldLabel : t("Color"),
			value : 'EBF1E2',
			anchor:'50%',
			name : 'color'
		});
		
		
		this.propertiesPanel = new Ext.Panel({
			title:t("Properties"),			
			cls:'go-form-panel',
			layout:'form',
			items:[{
				xtype: 'textfield',
				fieldLabel: t("Name"),
				name: 'name',
				anchor:'95%',
				allowBlank:false
			},
			this.colorField
		]});

		this.addPanel(this.propertiesPanel);
		this.addPermissionsPanel(new GO.grid.PermissionsPanel({}));
	},
	
	setCalendarId : function(id){
		
		var hide = true;
		
		if(id == 0){
			hide = false;
		}
				
		this.hidePermissionsTab(hide);
		
		this.addBaseParam('calendar_id',id);
	},
	
	afterRender : function(){
		GO.calendar.CategoryDialog.superclass.afterRender.call(this);
				
		if(this.hidePermissions){
			this._tabPanel.hideTabStripItem(this.permissionsPanel);
		}
	},
	
	hidePermissionsTab : function(hide){
		
		this.hidePermissions = hide;

		if(hide){
			this._tabPanel.hideTabStripItem(this.permissionsPanel);
		} else {
			this._tabPanel.unhideTabStripItem(this.permissionsPanel);
		}
	}
});
