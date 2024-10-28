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

	/**
	 * override the getCustomFieldSets function as the original filters out the field sets that are rendered in their own
	 * tabs. In this use case, a field set should always be returned.
	 *
	 * @returns {*[]}
	 */
	getCustomFieldSets : function() {
		const items = [];
		const fieldsets = go.customfields.CustomFields.getFormFieldSets(this.entityStore);
		fieldsets.forEach(function(fs) {
			if(fs.fieldSet.permissionLevel <= 10) {
				return;
			}
			fs.columnWidth = 1;
			items.push(fs);
		}, this);

		return items;
	},
});


