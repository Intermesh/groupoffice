/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SiteDialog.js 8376 2011-10-24 09:55:16Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */

GO.site.SiteDialog = Ext.extend(GO.dialog.TabbedFormDialog, {
	customFieldType: "GO\\Site\\Model\\Site",
	initComponent: function() {
		Ext.apply(this, {
			goDialogId: 'site',
			title: GO.site.lang.options,
			formControllerUrl: 'site/site',
			height: 550
		});

		GO.site.SiteDialog.superclass.initComponent.call(this);
	},
	buildForm: function() {

		this.propertiesPanel = new Ext.Panel({
			title: GO.site.lang.options,
			cls: 'go-form-panel',
			layout: 'form',
			labelWidth: 170,
			items: [
				{
					xtype: 'textfield',
					name: 'name',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: false,
					fieldLabel: GO.site.lang.siteName
				}, {
					xtype: 'textfield',
					name: 'module',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: false,
					fieldLabel: GO.site.lang.siteModule,
					disabled: true
				}, {
					xtype: 'textfield',
					name: 'domain',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: false,
					fieldLabel: GO.site.lang.siteDomain
				}, {
					xtype: 'textfield',
					name: 'base_path',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: true,
					fieldLabel: GO.site.lang.siteBase_path
				}, {
					xtype: 'textfield',
					name: 'language',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: true,
					fieldLabel: GO.site.lang.siteLanguage
				}, {
					xtype: 'xcheckbox',
					name: 'ssl',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					fieldLabel: GO.site.lang.siteSsl
				}, {
					xtype: 'xcheckbox',
					name: 'mod_rewrite',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					fieldLabel: GO.site.lang.siteMod_rewrite
				}, {
					xtype: 'textfield',
					name: 'mod_rewrite_base_path',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: false,
					fieldLabel: GO.site.lang.siteMod_rewrite_base_path
				}]

		});

		this.addPanel(this.propertiesPanel);

		this.addPermissionsPanel(new GO.grid.PermissionsPanel());
	}
});