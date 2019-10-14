/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CategoryDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.customfields.CategoryDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	initComponent : function(){
		
		Ext.apply(this, {
			title:t("Category", "customfields"),
			formControllerUrl: 'customfields/category',
			height:600
		});
		
		GO.customfields.CategoryDialog.superclass.initComponent.call(this);	
	},
	buildForm : function () {

		this.propertiesPanel = new Ext.Panel({
			url: GO.settings.modules.customfields.url+'action.php',
			border: false,
			baseParams: {task: 'category'},			
			title:t("Properties"),			
			waitMsgTarget:true,			
			layout:'form',
			autoScroll:true,
			items:[{
				xtype:'fieldset',
				items:[{
					xtype: 'textfield',
					name: 'name',
					anchor: '100%',
					allowBlank:false,
					fieldLabel: t("Name")
				}]
			}]
		});

		this.addPanel(this.propertiesPanel);	
 
    this.addPermissionsPanel(new GO.grid.PermissionsPanel({fieldName: 'aclId', levels:[GO.permissionLevels.read,GO.permissionLevels.write,GO.permissionLevels.manage]}));    
	},	
	setType : function(extendsModel)
	{
		this.formPanel.baseParams['extendsModel']=extendsModel;		
	}
});
