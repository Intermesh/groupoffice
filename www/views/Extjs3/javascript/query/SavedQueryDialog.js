/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SavedQueryDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.query.SavedQueryDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			titleField:'name',
			goDialogId:'SavedQuery',
			title:t("Saved query"),
			formControllerUrl: 'advancedSearch'
		});
		
		GO.query.SavedQueryDialog.superclass.initComponent.call(this);	
	},
	
	beforeSubmit : function() {
		this.formPanel.baseParams.data = Ext.encode(this.savedQueryGrid.queryPanel.getData());
	},
	
	afterSubmit : function(){
		this.savedQueryGrid.store.load();
		this.savedQueryGrid.queryPanel.setTitle('<b>'+this.formPanel.form.findField('name').getValue()+'</b>');
	},
	
	buildForm : function () {

		this.propertiesPanel = new Ext.Panel({
			title:t("Properties"),			
			cls:'go-form-panel',
			layout:'form',
			items:[{
				xtype: 'textfield',
				name: 'name',
				width:120,
				anchor: '100%',
				maxLength: 100,
				allowBlank:false,
				fieldLabel: t("Name")
			}]		
		});

		this.addPanel(this.propertiesPanel);
		
		this.addPermissionsPanel(new GO.grid.PermissionsPanel());
	},
	
	
	beforeLoad : function(remoteModelId,config) {
		
		config = config || {};
		if (!GO.util.empty(config.model_name))
			this.formPanel.baseParams.model_name = config.model_name;
		else
			Ext.MessageBox.alert(t("Error"),t("Model name not passed to the dialog."));
		
	}	
});
