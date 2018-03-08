/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
  * @author WilmarVB <wilmar@intermesh.nl>
 */

GO.servermanager.AutoEmailDialog = Ext.extend(GO.dialog.TabbedFormDialog,{

	initComponent : function() {
		Ext.apply(this, {
			titleField: 'name',
			title: t("autoEmail", "servermanager"),
			formControllerUrl: 'servermanager/automaticEmail', // change this if new panels are added
			width:700,
			height:480,
			resizable: true
			//fileUpload:true
		});
		GO.servermanager.AutoEmailDialog.superclass.initComponent.call(this);
	},

	buildForm : function() {
		this.propertiesPanel = new Ext.Panel({
			title:t("Properties"),					
			layout:'border',
			autoScroll:true,
			cls: 'go-form-panel',
			items:[new Ext.Panel({
				region: 'north',
				layout: 'form',
				border:false,
				height: 105,
				items: [{
					xtype: 'textfield',
					name: 'name',
					width: '100%',
					anchor: '-20',
					fieldLabel: t("Name"),
					allowBlank: false
				},{
					xtype: 'numberfield',
					name: 'days',
					width: '20',
					decimals: 0,
					value: 7,
					fieldLabel: t("days", "servermanager"),
					plugins:[new Ext.ux.FieldHelp(t("nDays", "servermanager"))]
				},{
					xtype: 'xcheckbox',
					name: 'active',
					anchor: '-20',
					hideLabel:false,
					boxLabel: t("enabled", "servermanager"),
					checked:true
				}]
			}),
				this.htmlEditPanel = new GO.base.email.EmailEditorPanel({
					region: 'center',
					layout:"fit",
					enableSubjectField: true
				})
			]
		});
		this.addPanel(this.propertiesPanel);		
	},

	afterLoad : function(remoteModelId, config, action) {
		if (remoteModelId<1)
			this.htmlEditPanel.reset();
		
		GO.servermanager.ManageDialog.superclass.afterLoad(this,remoteModelId,config,action);
	}

});