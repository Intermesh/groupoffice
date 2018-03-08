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

GO.dokuwiki.MainPanel = function(config){

	if(!config)
	{
		config = {};
	}

	config.tbar= new Ext.Toolbar({
		cls: 'go-head-tb',
		items:[new Ext.Button({
			iconCls: 'btn-refresh',
			cls: 'x-btn-text-icon',
			text: GO.lang['cmdRefresh'],
			handler:function(){
				GO.dokuwiki.checkHost(GO.dokuwiki.settings.externalUrl);
					//this.checkHost(GO.dokuwiki.settings.externalUrl);
					//GO.dokuwiki.iFrameComponent.setUrl(GO.settings.modules.dokuwiki.url);
					GO.dokuwiki.iFrameComponent.setUrl(GO.dokuwiki.settings.externalUrl);
			},
			scope: this
		})
		]
		});

	if(GO.settings.modules.dokuwiki.write_permission)
	{
		config.tbar.addItem('-');
		this.settingsButton = new Ext.Button({
			iconCls: 'btn-settings',
			text: GO.lang.administration,
			cls: 'x-btn-text-icon',
			handler: function(){
				if(!this.settingsDialog)
				{
					this.settingsDialog = new GO.dokuwiki.SettingsDialog();
				}
				this.settingsDialog.show();
			},
			scope: this
		});
		config.tbar.addItem(this.settingsButton);
	}

	GO.dokuwiki.iFrameComponent = new GO.panel.IFrameComponent({
		//url: GO.settings.modules.dokuwiki.url
    url: GO.dokuwiki.settings.externalUrl
	});

	config.layout='fit';
	config.items = [GO.dokuwiki.iFrameComponent];

	config.title = GO.dokuwiki.settings.title;
  
  config.listeners={
    scope:this,
    render:function(){
      GO.dokuwiki.checkHost(GO.dokuwiki.settings.externalUrl);
    }
  }

	GO.dokuwiki.MainPanel.superclass.constructor.call(this, config);

}

Ext.extend(GO.dokuwiki.MainPanel, Ext.Panel,{
  
});


GO.dokuwiki.checkHost = function(wikiurl) {

	if(GO.util.empty(wikiurl))
		return false;

	var godomain = window.location.hostname;

	var wikidomain = wikiurl.match(/http(s)?:\/\/([^/:]+)/i);

	if(!wikidomain || godomain != wikidomain[2]){
		return false;
		Ext.MessageBox.show({
			title:'Wrong Domain',
			msg:"Login and Logout functions will not work properly because Dokuwiki is on a different domain than Group-Office.<br /><br />Group-Office Domain: " + godomain + "<br />Dokuwiki Domain: " + wikidomain,
			buttons: Ext.Msg.OK,
			icon: Ext.MessageBox.ERROR
		});
	} else {
		return true;
	}
};


GO.moduleManager.addModule('dokuwiki', GO.dokuwiki.MainPanel, {
	title : GO.dokuwiki.settings.title,
	iconCls : 'go-tab-icon-dokuwiki'
});