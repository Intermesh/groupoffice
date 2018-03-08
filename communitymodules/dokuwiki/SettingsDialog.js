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
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.dokuwiki.SettingsDialog = function(config){
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
	config.title= GO.lang.cmdSettings;
	config.items=this.formPanel;
	config.buttons=[{
		text: GO.lang.cmdOk,
		handler: function(){
			this.submitForm(true);
			this.hide();
		},
		scope:this
	},{
		text: GO.lang.cmdClose,
		handler: function(){
			this.hide();
		},
		scope:this
	}];

	GO.dokuwiki.SettingsDialog.superclass.constructor.call(this, config);
}

Ext.extend(GO.dokuwiki.SettingsDialog, Ext.Window,{

	show : function(establishment_id) {
		if(!this.rendered)
			this.render(Ext.getBody());
		this.formPanel.form.reset();
		this.formPanel.load({
				url : GO.url('dokuwiki/dokuwiki/loadSettings'),
				success:function(form, action)
				{
					GO.dokuwiki.SettingsDialog.superclass.show.call(this);
				},
				failure:function(form, action)
				{
					GO.errorDialog.show(action.result.feedback)
				},
				scope: this
			});
	},

	submitForm : function(hide){
		this.formPanel.form.submit(
		{
			url:GO.url('dokuwiki/dokuwiki/saveSettings'),
			waitMsg:GO.lang['waitMsgSave'],
			success:function(form, action){
				var response = Ext.decode(action.response.responseText);
				if (response.success) {
					GO.dokuwiki.settings.externalUrl = response.data.external_url;
					GO.dokuwiki.settings.title = response.data.title;
					GO.dokuwiki.iFrameComponent.setUrl(GO.dokuwiki.settings.externalUrl);
				} else {
					GO.errorDialog.show(action.result.feedback);
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
				fieldLabel : GO.dokuwiki.lang.externalUrl,
				anchor : '100%',
				allowBlank : false,
				validator: function(value){

					if(!GO.dokuwiki.checkHost(value)){
						return "Wrong Domain (Please make sure it implements http(s)://";
					} else {
						return true;
					}
				}
			}),this.titleField = new Ext.form.TextField({
				name : 'title',
				fieldLabel : GO.dokuwiki.lang.title,
				anchor : '100%',
				allowBlank : false
			})]
		});
		this.formPanel = new Ext.form.FormPanel({
			waitMsgTarget:true,
			baseParams: {
				task: 'load_settings'
			},
			url: GO.settings.modules.dokuwiki.url+'json.php',
			border: false,
			autoHeight:true,
			items: this.panel
		});
	}
});