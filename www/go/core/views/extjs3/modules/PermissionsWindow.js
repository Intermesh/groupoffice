go.modules.PermissionsWindow = Ext.extend(GO.Window,{
	
	module_id : '',
	initComponent : function(){
		
		var levelLabels={};
		levelLabels[go.permissionLevels.read]=t("Use", "users");
		levelLabels[go.permissionLevels.manage]=t("Manage", "users");
		
		this.permissionsTab = new GO.grid.PermissionsPanel({
					title : t("Use", "users"),
					levels:[
						go.permissionLevels.read,
						go.permissionLevels.manage
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

					if(!this.pkg || this.pkg == 'legacy') {
						GO.request({
							timeout: 2 * 60 * 1000,
							url: 'modules/module/checkDefaultModels',
							params: {
								moduleId: this.module_id
							},
							success: function (response, options, result) {
								this.hide();
							},
							scope: this
						});
					} else {
						this.hide();
					}
				},
				scope : this
			}]
				  
		});
		
		go.modules.PermissionsWindow.superclass.initComponent.call(this);
	},
	show: function(moduleId, pkg, name, acl_id) {
		this.module_id=moduleId;
		this.pkg = pkg;
		
		this.setTitle(t("Permissions") + ' ' + name);		
		go.modules.PermissionsWindow.superclass.show.call(this);		
		this.permissionsTab.setAcl(acl_id);		
	}
})
