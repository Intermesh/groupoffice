/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: CategoryDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.email.TemplateGroupDialog = Ext.extend(GO.dialog.TabbedFormDialog, {

	title:t("Group"),
	height:350,
	width: 400,

	initComponent(){

		Ext.apply(this, {
			goDialogId: 'emailtemplategroup',
			title: t("Template group"),
			formControllerUrl: 'email/templateGroup',
			height:600
		});

		this.supr().initComponent.call(this);
	},

	buildForm () {
		this.addPanel(
			this.propertiesPanel = new Ext.Panel({
				items: [{
					xtype: 'fieldset',
					items: [
						{
							xtype: 'textfield',
							name: 'name',
							fieldLabel: t("Name"),
							anchor: '100%',
							required: true
						}
					]
				}]
			})
		);

	}
});

