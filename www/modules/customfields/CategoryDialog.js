/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CategoryDialog.js 15954 2013-10-17 12:04:36Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.customfields.CategoryDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	initComponent : function(){
		
		Ext.apply(this, {
			title:GO.customfields.lang.category,
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
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',waitMsgTarget:true,			
			layout:'form',
			autoScroll:true,
			items:[{
				xtype: 'textfield',
			  name: 'name',
				anchor: '100%',
			  allowBlank:false,
			  fieldLabel: GO.lang.strName
			}]				
		});

		this.addPanel(this.propertiesPanel);	
 
    this.addPermissionsPanel(new GO.grid.PermissionsPanel({levels:[GO.permissionLevels.read,GO.permissionLevels.write,GO.permissionLevels.manage]}));    
	},	
	setType : function(extends_model)
	{
		this.formPanel.baseParams['extends_model']=extends_model;		
	}
});