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

GO.mediawiki.MainPanel = function(config){

	if(!config)
	{
		config = {};
	}

	config.tbar= new Ext.Toolbar({
		cls: 'go-head-tb',
		items:[new Ext.Button({
			iconCls: 'btn-refresh',
			cls: 'x-btn-text-icon',
			text: t("Refresh"),
			handler:function(){
				GO.mediawiki.iFrameComponent.setUrl(GO.mediawiki.settings.externalUrl);
			},
			scope: this
		})
		]
		});

	if(GO.settings.modules.mediawiki.write_permission)
	{
		config.tbar.addItem('-');
		this.settingsButton = new Ext.Button({
			iconCls: 'btn-settings',
			text: t("Administration"),
			cls: 'x-btn-text-icon',
			handler: function(){
				if(!this.settingsDialog)
				{
					this.settingsDialog = new GO.mediawiki.SettingsDialog();
				}
				this.settingsDialog.show();
			},
			scope: this
		});
		config.tbar.addItem(this.settingsButton);
	}

	GO.mediawiki.iFrameComponent = new GO.panel.IFrameComponent({
		url: GO.mediawiki.settings.externalUrl
	});

	config.layout='fit';
	config.items = [GO.mediawiki.iFrameComponent];

	config.title = GO.mediawiki.settings.title;

	GO.mediawiki.MainPanel.superclass.constructor.call(this, config);

}

Ext.extend(GO.mediawiki.MainPanel, Ext.Panel,{

	beforeRender : function() {
		GO.request({
			url: 'mediawiki/settings/load', //GO.settings.modules.mediawiki.url + 'json.php',
			scope: this,
			success: function(response,options) {
				var responseParams = Ext.decode(response.responseText);
				if (responseParams.success) {
					GO.mediawiki.settings.externalUrl = responseParams.data.external_url;
					GO.mediawiki.iFrameComponent.setUrl(GO.mediawiki.settings.external_url);
					GO.mediawiki.settings.title = responseParams.data.title;
					this.title = responseParams.title;
				} else {
					Ext.Msg.alert(t("Error"), responseParams.feedback);
				}
			}
		})
	}

});

GO.moduleManager.addModule('mediawiki', GO.mediawiki.MainPanel, {
	title : 'Wiki',
	iconCls : 'go-tab-icon-tasks'
});
