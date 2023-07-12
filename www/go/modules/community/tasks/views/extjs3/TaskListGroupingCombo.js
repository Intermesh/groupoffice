/* global Ext, go, GO */

/**
 * 
 * @type |||
 */
go.modules.community.tasks.TaskListGroupingCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Group"),
	hiddenName: 'groupingId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: false,
	forceSelection: true,
	allowNew: true,
	initComponent: function () {

		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: [
					'id',
					'name'
					],
				entityStore: "TaskListGrouping",
				sortInfo: {
					field: "name",
					direction: 'ASC' 
				}
			})
		});

		go.modules.community.tasks.TaskListGroupingCombo.superclass.initComponent.call(this);

	}
});

