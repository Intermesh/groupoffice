/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AliasDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 

GO.email.AliasDialog = Ext.extend(GO.dialog.TabbedFormDialog,{
	
	initComponent : function(){
		Ext.apply(this, {
			titleField:'email',
			title: GO.email.lang.alias,
			formControllerUrl: 'email/alias',
			width:700,
			height:500
		});
		
		GO.email.AliasDialog.superclass.initComponent.call(this);
	},
		
	buildForm : function () {
		
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',
			waitMsgTarget:true,			
			layout:'form',
			autoScroll:true,
			items:[{
				xtype : 'textfield',
				name : 'name',
				anchor : '100%',
				allowBlank:false,
				fieldLabel : GO.lang.strName
			}, {
				xtype : 'textfield',
				name : 'email',
				anchor : '100%',
				allowBlank:false,
				fieldLabel : GO.email.lang.email
			}, {
				xtype : 'textarea',
				name : 'signature',
				anchor : '100%',
				height:150,
				fieldLabel : GO.email.lang.signature
			}]
		});
		
		this.addPanel(this.propertiesPanel);
	}
	

});
