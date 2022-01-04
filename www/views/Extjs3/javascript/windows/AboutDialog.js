/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AboutDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 


/**
 * @class GO.dialog.AboutDialog
 * @extends Ext.Window
 * The Group-Office login dialog window.
 * 
 * @cfg {Function} callback A function called when the login was successfull
 * @cfg {Object} scope The scope of the callback
 * 
 * @constructor
 * @param {Object} config The config object
 */
 
GO.dialog.AboutDialog = Ext.extend(GO.dialog.TabbedFormDialog, {
	
	initComponent : function(){

		Ext.apply(this,{
			modal:false,
			formControllerUrl:'core',
			loadAction:'about',
			layout:'fit',
			autoHeight:true,
			width: dp(480),
			resizable: false,
			closeAction:'hide',
			title:t("About GroupOffice"),
			buttons: null
    });

		 
		GO.dialog.AboutDialog.superclass.initComponent.call(this);
	},
	
	afterLoad : function(remoteModelId, config, action){
		if(action.result.data.has_usage){
			this.usageFS.show();
			this.setHeight(370);
		}
	},


	buildForm : function(){		
		this.addPanel(new Ext.Panel({
			border:false,
			// padding: dp(16),
			items: [

				new Ext.form.FieldSet({
						items: [
							{xtype: 'box', cls: "go-about-logo"},
							new GO.form.PlainField({
							name: 'about',
							hideLabel: true
						})]
					}),
				this.usageFS = new Ext.form.FieldSet({
					hidden:true,
					style:'margin-top:10px',
					xtype:'fieldset',
					title:t("This instance is using"),
					items:[
						new GO.form.PlainField({
							fieldLabel:t("Files"),
							name:'file_storage_usage'
						}),
						new GO.form.PlainField({
							fieldLabel:t("Database"),
							name:'database_usage'
						}),
						new GO.form.PlainField({
							fieldLabel:t("E-mail"),
							name:'mailbox_usage'
						}),
						new GO.form.PlainField({
							fieldLabel:t("Total"),
							name:'total_usage'
						})						
					]					
				})
				
			],
			autoScroll:true
		}));
	}
});

