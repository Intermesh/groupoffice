/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: GroupDialog.js 15954 2013-10-17 12:04:36Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.groups.GroupDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'group',
			title:GO.groups.lang.group,
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
				fieldLabel: GO.lang.strName
			}
			]				
		});

//    if(GO.settings.has_admin_permission) {
//      this.adminOnlyCheckBox = new Ext.ux.form.XCheckbox({
//          name: 'admin_only',
//          checked: false,
//          boxLabel: GO.groups.lang.adminOnlyLabel,
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
      title:GO.lang['strProperties'],	
      items:[this.propertiesPanel, this.userGrid]
    });

		var levelLabels = {};
		levelLabels[GO.permissionLevels.read]=GO.groups.lang.use;

    this.permissionsPanel = new GO.grid.PermissionsPanel({
      title:GO.groups.lang.managePermissions,
			levels:[GO.permissionLevels.read,GO.permissionLevels.manage],
			levelLabels:levelLabels
    });

		

		this.addPanel(this.borderPanel);
    this.addPanel(this.permissionsPanel);
		
		if(GO.settings.modules.modules.permission_level){
			this.modulePermissionsGrid = new GO.grid.ModulePermissionsGrid({
				title: GO.groups.lang['modulePermissions'],
				storeUrl: GO.url('modules/module/permissionsStore'),
				paramIdType: 'groupId',
				disabled:true
			});
			this.addPanel(this.modulePermissionsGrid);
		}
	}
});