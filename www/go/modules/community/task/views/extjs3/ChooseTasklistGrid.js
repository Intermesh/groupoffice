go.modules.community.task.ChooseTasklistGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function () {

		this.store = new go.data.Store({
			fields: [
				'id', 
                'name',
                {name: 'creator', type: "relation"}
			],
			entityStore: "Tasklist"
		});

		Ext.apply(this, {		
		
			columns: [
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: dp(40),
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'name',
					header: t('Name'),
					width: dp(75),
					sortable: true,
					dataIndex: 'name'
                },
				{	
					hidden: true,
					header: t('Created by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'creator',
					renderer: function(v) {
						return v ? v.displayName : "-";
					}
				}
			],
			viewConfig: {
				totalDisplay: true,
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>'
            },
            listeners: {
				scope: this,
				rowclick: function (grid, rowIndex, e) {
                    var row = this.getSelectionModel().getSelections()[0];
                    this.selectedId = row.get("id");
				}
			},
			autoExpandColumn: 'name',
			// config options for stateful behavior
			stateful: true,
			stateId: 'choose-tasklist-grid'
        });

        this.store.load();
		go.modules.community.task.ChooseTasklistGrid.superclass.initComponent.call(this);
	}
});

