/* global Ext, go, GO */

go.customfields.FieldSetCombo = Ext.extend(go.form.ComboBoxReset, {
	fieldLabel: t("Parent field set"),
	hiddenName: 'fieldSetId',
	anchor: '100%',
	emptyText: t("None"),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	store: {
		xtype: "gostore",
		fields: ['id', 'name'],
		entityStore: "FieldSet"
	}
});

Ext.reg("fieldsetcombo", go.customfields.FieldSetCombo);
