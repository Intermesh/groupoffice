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
	customFieldType: "Site",
	initComponent: function() {
		Ext.apply(this, {
			goDialogId: 'site',
			title: t("Options", "site"),
			formControllerUrl: 'site/site',
			height: 550
		});

		GO.site.SiteDialog.superclass.initComponent.call(this);
	},
	buildForm: function() {

		this.propertiesPanel = new Ext.Panel({
			title: t("Options", "site"),
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
					fieldLabel: t("Name", "site")
				}, {
					xtype: 'textfield',
					name: 'module',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: false,
					fieldLabel: t("Module", "site"),
					disabled: true
				}, {
					xtype: 'textfield',
					name: 'domain',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: false,
					fieldLabel: t("Domain", "site")
				}, {
					xtype: 'textfield',
					name: 'base_path',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: true,
					fieldLabel: t("Base path", "site")
				}, {
					xtype: 'textfield',
					name: 'language',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: true,
					fieldLabel: t("Language", "site")
				}, {
					xtype: 'xcheckbox',
					name: 'ssl',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					fieldLabel: t("SSL", "site")
				}, {
					xtype: 'xcheckbox',
					name: 'mod_rewrite',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					fieldLabel: t("Mod rewrite", "site")
				}, {
					xtype: 'textfield',
					name: 'mod_rewrite_base_path',
					width: 300,
					anchor: '100%',
					maxLength: 100,
					allowBlank: false,
					fieldLabel: t("Mod rewrite base path", "site")
				}]

		});

		this.addPanel(this.propertiesPanel);

		this.addPermissionsPanel(new GO.grid.PermissionsPanel());
	}
});
