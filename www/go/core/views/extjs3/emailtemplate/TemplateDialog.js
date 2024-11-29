/* global go */

go.emailtemplate.TemplateDialog = Ext.extend(go.form.Dialog, {
	title: t('E-mail template'),
	entityStore: "EmailTemplate",
	width: dp(1000),
	height: dp(800),
	// formPanelLayout: "fit",
	autoScroll: false,
	resizable: true,
	maximizable: true,
	collapsible: true,
	modal: false,

	initFormItems: function () {

		return [
			new go.emailtemplate.TemplateFieldset({
				anchor: "100% 100%"
			})
		];
	}


});


