go.modules.community.task.TaskGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function () {
		this.checkColumn = new GO.grid.CheckColumn({
			id:'percentageComplete',
			dataIndex: 'percentageComplete',			
			hideInExport:true,
			header: '<i class="icon ic-check"></i>',
			width: dp(56),
			hideable:false,
			menuDisabled: true,
			sortable:false,
			groupable:false
		});

		this.checkColumn.on('change', function(record, checked){
			this.store.reload({
				callback:function(){
					var update = {}, id = record.data.id;
					// task completed
					if(checked) {
						update[id] = {percentageComplete: 100};
						// check for another task
						// go.Jmap.request({
						// 	method: "community/task/Task/repeatTask",
						// 	params: {
						// 		id: id
						// 	},
						// 	callback: function(options, success, result) {
						// 		// this.websiteTitle.setValue(result.title);
						// 		// this.websiteDescription.setValue(result.description);
						// 		// thumbExample.getEl().dom.style.backgroundImage = 'url(' + go.Jmap.downloadUrl(result.logo) + ')';
						// 		// this.thumbField.setValue(result.logo);
						// 		// this.el.unmask();								
						// 	},
						// 	scope: this
						// });
					} else {
						update[id] = {percentageComplete: 0};
					}
					// update task
					go.Db.store("Task").set({update: update});
				},
				scope:this
			});
		}, this);

		// without reload
		// this.checkColumn.on('change', function(record, checked){
		// 	var update = {}, id = record.data.id;
		// 	if(checked) {
		// 		update[id] = {percentageComplete: 100};
		// 	} else {
		// 		update[id] = {percentageComplete: 0};
		// 	}

		// 	go.Db.store("Task").set({update: update});
		// }, this);



		this.store = new go.data.Store({
			fields: [
				'id', 
				'title', 
				'description', 
				'repeatEndTime', 
				{name: 'createdAt', type: 'date'}, 
				{name: 'modifiedAt', type: 'date'}, 
				{name: 'creator', type: "relation"},
				{name: 'modifier', type: "relation"},
				'percentageComplete'
			],
			entityStore: "Task"
		});

		Ext.apply(this, {		
			columns: [
				this.checkColumn,
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: dp(40),
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'title',
					header: t('Title'),
					width: dp(75),
					sortable: true,
					dataIndex: 'title'
				},
				{
					xtype:"datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'createdAt',
					hidden: true
				},
				{					
					xtype:"datecolumn",
					hidden: false,
					id: 'modifiedAt',
					header: t('Modified at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifiedAt'
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
				},
				{	
					hidden: true,
					header: t('Modified by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifier',
					renderer: function(v) {
						return v ? v.displayName : "-";
					}
				}
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>'
			},
			autoExpandColumn: 'title',
			// config options for stateful behavior
			stateful: true,
			stateId: 'tasks-grid'
		});

		go.modules.community.task.TaskGrid.superclass.initComponent.call(this);
	}
});

