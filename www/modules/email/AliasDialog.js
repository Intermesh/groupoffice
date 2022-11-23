/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AliasDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 

GO.email.AliasDialog = Ext.extend(GO.dialog.TabbedFormDialog,{
	
	initComponent : function(){
		Ext.apply(this, {
			titleField:'email',
			title: t("Alias", "email"),
			formControllerUrl: 'email/alias',
			width:700,
			height:500
		});
		
		GO.email.AliasDialog.superclass.initComponent.call(this);
	},
		
	buildForm : function () {
		
		this.propertiesPanel = new Ext.Panel({
			title:t("Properties"),			
			cls:'go-form-panel',
			waitMsgTarget:true,			
			layout:'form',
			autoScroll:true,
			items:[{
				xtype : 'textfield',
				name : 'name',
				anchor : '100%',
				allowBlank: false,
				fieldLabel : t("Name")
			}, {
				xtype : 'textfield',
				name : 'email',
				anchor : '100%',
				vtype: 'emailAddress',
				allowBlank:false,
				fieldLabel : t("Email", "email")
			}, {
				xtype : 'textfield',
				name : 'reply_to',
				anchor : '100%',
				vtype: 'emailAddress',
				allowBlank: true,
				fieldLabel : t("Reply to", "email")
			}, {
				xtype : 'textarea',
				name : 'signature',
				anchor : '100%',
				height:150,
				fieldLabel : t("Signature", "email")
			}]
		});
		
		this.addPanel(this.propertiesPanel);
	}
	

});
