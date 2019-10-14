/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: GroupDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.groups.GroupDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'group',
			title:t("group", "groups"),
			formControllerUrl: 'groups/group',
			titleField:'name',
			height:600
		});
		
		GO.groups.GroupDialog.superclass.initComponent.call(this);
	},

	setRemoteModelId : function(remoteModelId) {
		this.userGrid.setGroupId(remoteModelId);
		if(this.modulePermissionsGrid){
			this.modulePermissionsGrid.setIdParam(remoteModelId);
			this.modulePermissionsGrid.setDisabled(!remoteModelId);
		}
		
		GO.groups.GroupDialog.superclass.setRemoteModelId.call(this, remoteModelId);
	},
	
	beforeSubmit : function(params) {
		if(this.modulePermissionsGrid)
			this.formPanel.form.baseParams['permissions'] = this.modulePermissionsGrid.getPermissionData();		
	},
	
	buildForm : function () {
    
    this.propertiesPanel = new Ext.Panel({
      region:'north',
      height:35,
      border:false,
			cls:'go-form-panel',
			layout:'form',
			items:[{
				xtype: 'textfield',
				name: 'name',
				width:300,
				anchor: '100%',
				maxLength: 100,
				allowBlank:false,
				fieldLabel: t("Name")
			}
			]				
		});

//    if(GO.settings.has_admin_permission) {
//      this.adminOnlyCheckBox = new Ext.ux.form.XCheckbox({
//          name: 'admin_only',
//          checked: false,
//          boxLabel: t("adminOnlyLabel", "groups"),
//          hideLabel:true
//      });
//      this.propertiesPanel.height=60;
//      this.propertiesPanel.add(this.adminOnlyCheckBox);
//    }
    
    this.userGrid = new GO.groups.UsersGrid({
      region:'center'
    });
    
    this.borderPanel = new Ext.Panel({
      layout:'border',
      title:t("Properties"),	
      items:[this.propertiesPanel, this.userGrid]
    });

		var levelLabels = {};
		levelLabels[GO.permissionLevels.read]=t("use", "groups");

    this.permissionsPanel = new GO.grid.PermissionsPanel({
			fieldName: 'aclId',
      title:t("managePermissions", "groups"),
			levels:[GO.permissionLevels.read,GO.permissionLevels.manage],
			levelLabels:levelLabels
    });

		

		this.addPanel(this.borderPanel);
    this.addPanel(this.permissionsPanel);
		
		if(GO.settings.modules.modules.permission_level){
			this.modulePermissionsGrid = new GO.grid.ModulePermissionsGrid({
				title: t("modulePermissions", "groups"),
				storeUrl: GO.url('modules/module/permissionsStore'),
				paramIdType: 'groupId',
				disabled:true
			});
			this.addPanel(this.modulePermissionsGrid);
		}
	}
});
