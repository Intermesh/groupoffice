go.customfields.EditFieldSetDialog = Ext.extend(go.form.Dialog, {
	title: t('Edit custom fields'),
	height: dp(700),
	width: dp(1000),
	formPanelLayout: "column",

	addCustomFields : function(items) {
		const fs = this.getCustomFieldSets().filter(f => f.fieldSet.id == this.fieldSetId);
		this.title = fs[0].fieldSet.name;
		fs[0].collapsible = false;
		fs[0].title = false;
		return items.concat(fs);
	},
});


