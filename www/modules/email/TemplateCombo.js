/* global Ext, go, GO */

GO.email.TemplateCombo = Ext.extend(go.form.ComboBoxReset, {
	fieldLabel: t("Template"),
	hiddenName: 'templateId',
	anchor: '100%',
	emptyText: t("None"),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: false,
	initComponent: function () {
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'name'],
				entityStore: "EmailTemplate"				
			})
		});
		
		GO.email.TemplateCombo.superclass.initComponent.call(this);

	}
});

Ext.reg("emailtemplatecombo", GO.email.TemplateCombo);
