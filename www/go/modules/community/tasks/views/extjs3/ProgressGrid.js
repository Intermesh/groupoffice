go.modules.community.tasks.ProgressGrid = Ext.extend(go.NavGrid, {
	autoHeight: true,
	saveSelection: true,
	stateId: "task-progress-grid",
	initComponent: function () {
		Ext.apply(this, {
			store: new Ext.data.ArrayStore({
				fields: ['value', 'name'],
				id: 0,
				data: [
					['completed', t("Completed")],
					['failed', t("Failed")],
					['in-progress', t("In Progress")],
					['needs-action', t("Needs action")],
					['cancelled', t("Cancelled")]
				]
			}),

			stateful: true,
		});

		go.modules.community.tasks.ProgressGrid.superclass.initComponent.call(this);

	}

});
