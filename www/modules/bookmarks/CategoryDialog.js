/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CategoryDialog.js 19024 2015-04-23 11:24:40Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.bookmarks.CategoryDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	initComponent : function(){
		
		Ext.apply(this, {
			titleField:'name',
			title:GO.bookmarks.lang.category,
			formControllerUrl: 'bookmarks/category',
			height:600,
			width:500
		});
		
		GO.bookmarks.CategoryDialog.superclass.initComponent.call(this);	
	},
	buildForm : function () {

		this.showInStartMenuCheck = new Ext.ux.form.XCheckbox({
			hideLabel: true,
			boxLabel: GO.bookmarks.lang.showCategoryInStartMenu,
			name: 'show_in_startmenu'
		});

		this.emptyLine = new GO.form.PlainField({
			value: '&nbsp;'
		});
		
		this.propertiesPanel = new Ext.Panel({
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
			},
			this.emptyLine,
			this.showInStartMenuCheck
//			,this.selectUser = new GO.form.SelectUser({
//				fieldLabel: GO.lang['strUser'],
//				disabled : !GO.settings.has_admin_permission,
//				value: GO.settings.user_id,
//				anchor: '100%'
//			})
		]
				
		});

		this.addPanel(this.propertiesPanel);	
 
    this.addPermissionsPanel(new GO.grid.PermissionsPanel());    
	}
});