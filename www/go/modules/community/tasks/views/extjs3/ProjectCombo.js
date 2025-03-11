/* global Ext, go, GO */

/**
 *
 * @type |||
 */
go.modules.community.tasks.ProjectCombo = Ext.extend(go.form.ComboBoxReset, {
	fieldLabel: t("Project", "projects3", "business"),
	hiddenName: 'projectId',
	anchor: '100%',
	// emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'number',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: false,
	forceSelection: true,
	initComponent: function () {


		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: [
					'id',
					"number",
					"name"
					],
				entityStore: "Project3",
				sortInfo: {
					field: "number",
					direction: 'DESC'
				}
			})
		});

		this.tpl = new Ext.XTemplate(
			'<tpl for=".">',
			'<div class="x-combo-list-item"><div>{number}</div><small>{name}</small></div>',
			'</tpl>',
		);

		go.modules.community.tasks.ProjectCombo.superclass.initComponent.call(this);
	}
});

