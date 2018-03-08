/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AliasDialog.js 21624 2017-11-06 08:29:46Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.postfixadmin.AliasDialog = Ext.extend(GO.dialog.TabbedFormDialog,{
	
	initComponent : function(){
		Ext.apply(this, {
			titleField:'address',
			title: GO.postfixadmin.lang.alias,
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
			title:GO.lang['strProperties'],			
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
				plugins:[new Ext.ux.FieldHelp(GO.postfixadmin.lang.aliasAddresHelp)],
				items:[{
					xtype: 'textfield',
					name: 'address',
					flex:3,
					allowBlank:false,
					fieldLabel: GO.postfixadmin.lang.address
					
				},this.domainLabel]
			}),				
			{
				xtype: 'textarea',
			  name: 'goto',
				anchor: '-20',
			  allowBlank:true,
				height:120,
			  fieldLabel: GO.postfixadmin.lang.goto_address,
				plugins:[new Ext.ux.FieldHelp(GO.postfixadmin.lang.aliasHelp)]
			},{
				xtype: 'xcheckbox',
			  name: 'active',
				anchor: '-20',
			  allowBlank:false,
			  boxLabel: GO.postfixadmin.lang.active,
			  hideLabel: true
			}]
		});
		
		this.addPanel(this.propertiesPanel);	
	},
	
	afterLoad : function(remoteModelId, config, action){
		this.domainLabel.setText('@'+action.result.data.domain_name);
	}
});