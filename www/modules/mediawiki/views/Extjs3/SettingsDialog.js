/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id:
 * @copyright Copyright Intermesh
 * @author Wilmar van Beusekom <wilmar@intermesh.nl>
 */

GO.mediawiki.SettingsDialog = function(config){
	if(!config)
	{
		config={};
	}

	this.buildForm();

	config.layout='fit';
	config.modal=false;
	config.resizable=false;
	config.width=550;
	config.autoHeight=true;
	config.closeAction='hide';
	config.title= t("Settings");
	config.items=this.formPanel;
	config.buttons=[{
		text: t("Ok"),
		handler: function(){
			this.submitForm(true);
			this.hide();
		},
		scope:this
	},{
		text: t("Close"),
		handler: function(){
			this.hide();
		},
		scope:this
	}];

	GO.mediawiki.SettingsDialog.superclass.constructor.call(this, config);
}

Ext.extend(GO.mediawiki.SettingsDialog, Ext.Window,{

	show : function(establishment_id) {
		if(!this.rendered)
			this.render(Ext.getBody());
		this.formPanel.form.reset();
		this.formPanel.load({
				url : GO.url('mediawiki/settings/load'), // GO.settings.modules.mediawiki.url+'json.php',
				success:function(form, action)
				{
					GO.mediawiki.SettingsDialog.superclass.show.call(this);
				},
				failure:function(form, action)
				{
					Ext.Msg.alert(t("Error"), action.result.feedback)
				},
				scope: this
			});
	},

	submitForm : function(hide){
		this.formPanel.form.submit(
		{
			url: GO.url('mediawiki/settings/save'),
			waitMsg:t("Saving..."),
			success:function(form, action){
				var response = Ext.decode(action.response.responseText);
				if (response.success) {
					GO.mediawiki.settings.externalUrl = response.data.external_url;
					GO.mediawiki.settings.title = response.data.title;
					GO.mediawiki.iFrameComponent.setUrl(GO.mediawiki.settings.externalUrl);
				} else {
					Ext.Msg.alert(t("Error"), action.result.feedback);
				}
			},
			scope: this
		});
	},

	buildForm : function() {
		this.panel = new Ext.Panel({
			border:false,
			layout : 'form',
			bodyStyle:'padding:5px',
			waitMsgTarget:true,
			labelAlign:'top',
			autoHeight:true,
			//height : 150,
			items:[this.externalUrlField = new Ext.form.TextField({
				name : 'external_url',
				fieldLabel : t("Mediawiki url", "mediawiki"),
				anchor : '100%',
				allowBlank : false
			}),this.titleField = new Ext.form.TextField({
				name : 'title',
				fieldLabel : t("Module title", "mediawiki"),
				anchor : '100%',
				allowBlank : false
			})]
		});
		this.formPanel = new Ext.form.FormPanel({
			waitMsgTarget:true,
			url: GO.url('mediawiki/settings/load'),
			border: false,
			autoHeight:true,
			items: this.panel
		});
	}
});
