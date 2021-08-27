go.modules.community.tasks.ProgressCombo = Ext.extend(go.form.SelectField,{
	hiddenName : 'progress',
	fieldLabel : t("Progress"),
	options : [
			['completed', t("Completed")],
			['failed', t("Failed")],
			['in-progress', t("In Progress")],
			['needs-action', t("Needs action")],
			['cancelled', t("Cancelled")]
		]

});