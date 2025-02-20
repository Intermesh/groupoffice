go.customfields.EditFieldSetDialog = Ext.extend(go.form.Dialog, {
	title: t('Edit custom fields'),
	height: dp(700),
	width: dp(1000),
	formPanelLayout: "column",

	/**
	 * This overrides the default addCustomFields function as the selected fieldset should be displayed (ie not filtered
	 * out) by definition.
	 *
	 * @param items
	 * @returns {*}
	 */

	addCustomFields : function(items) {
		const fieldsets = go.customfields.CustomFields.getFieldSets(this.entityStore);
		const fs = fieldsets.find(f => f.id == this.fieldSetId);
		if(!fs || fs.permissionLevel <= 10) {
			return items; // nope
		}
		const ffs = new go.customfields.FormFieldSet({fieldSet: fs, isTab: fs.isTab});
		this.title = fs.name;
		ffs.columnWidth = 1;
		ffs.collapsible = false;
		ffs.title = false;
		return items.concat(ffs);
	}
});


