GO.modules.ModulePermissionsWindow = Ext.extend(GO.Window,{
	
	module_id : '',
	initComponent : function(){
		
		var levelLabels={};
		levelLabels[GO.permissionLevels.read]=t("Use", "users");
		levelLabels[GO.permissionLevels.manage]=t("Manage", "users");
		
		this.permissionsTab = new GO.grid.PermissionsPanel({
					title : t("Use", "users"),
					levels:[
						GO.permissionLevels.read,
						GO.permissionLevels.manage
					],
					levelLabels:levelLabels
				});
				
		Ext.apply(this,{
			title : t("Permissions"),
			layout : 'fit',
			height : 600,
			width : 440,
			modal:true,
			closable:false,
			closeAction:'hide',
			items : [this.permissionsTab],
			buttons : [{
				text : t("Ok"),
				handler : function() {
					GO.request({
						timeout : 2 * 60 * 1000,
						url : 'modules/module/checkDefaultModels',
						params : {
							moduleId : this.module_id
						},
						success : function(response, options, result) {
							this.hide();
						},
						scope : this
					});
				},
				scope : this
			}]
				  
		});
		
		GO.modules.ModulePermissionsWindow.superclass.initComponent.call(this);
	},
	show: function(moduleId, name, acl_id) {
		this.module_id=moduleId;
		
		this.setTitle(t("Permissions") + ' ' + name);		
		GO.modules.ModulePermissionsWindow.superclass.show.call(this);		
		this.permissionsTab.setAcl(acl_id);		
	}
})
