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
 
GO.postfixadmin.AliasDialog = Ext.extend(GO.dialog.TabbedFormDialog,{
	
	initComponent : function(){
		Ext.apply(this, {
			titleField:'address',
			title: t("Alias", "postfixadmin"),
			formControllerUrl: 'postfixadmin/alias',
			width:700,
			height:500
		});
		this.addEvents({'save' : true});	
		GO.postfixadmin.AliasDialog.superclass.initComponent.call(this);
	},
		
	buildForm : function () {
		
		this.domainLabel = new Ext.form.Label({
			flex:2
		});
		
		this.propertiesPanel = new Ext.Panel({
			title:t("Properties"),			
			cls:'go-form-panel',waitMsgTarget:true,			
			layout:'form',
			autoScroll:true,
			items:[{
				xtype: 'textfield',
				name: 'domain_id',
				hidden: true
			},
			
			new Ext.form.CompositeField({
				anchor: '-20',
				plugins:[new Ext.ux.FieldHelp(t("Use '*' for a catch all alias (not recommended).", "postfixadmin"))],
				items:[{
					xtype: 'textfield',
					name: 'address',
					flex:3,
					allowBlank:false,
					fieldLabel: t("Address", "postfixadmin")
					
				},this.domainLabel]
			}),				
			{
				xtype: 'textarea',
			  name: 'goto',
				anchor: '-20',
			  allowBlank:true,
				grow: true,
				height:120,
			  fieldLabel: t("Goto", "postfixadmin"),
				plugins:[new Ext.ux.FieldHelp(t("For multiple recipients use a comma separated list eg. alias1@domain.com,alias2@domain.com", "postfixadmin"))]
			},{
				xtype: 'xcheckbox',
			  name: 'active',
				anchor: '-20',
			  boxLabel: t("Active", "postfixadmin"),
			  hideLabel: true
			}]
		});
		
		this.addPanel(this.propertiesPanel);	
	},
	
	afterLoad : function(remoteModelId, config, action){
		this.domainLabel.setText('@'+action.result.data.domain_name);
	}
});
