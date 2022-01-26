/* global go */

go.emailtemplate.TemplateDialog = Ext.extend(go.form.Dialog, {
	title: t('E-mail template'),
	entityStore: "EmailTemplate",
	width: dp(1000),
	height: dp(800),
	formPanelLayout: "fit",
	resizable: true,
	maximizable: true,
	collapsible: true,
	modal: false,

	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel());

		return [
			new go.emailtemplate.TemplateFieldset()
		];
	}


});


