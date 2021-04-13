go.modules.community.tasks.TaskGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function () {
		this.checkColumn = new GO.grid.CheckColumn({
			id:'progress',
			dataIndex: 'progress',
			hideInExport:true,
			header: '<i class="icon ic-check"></i>',
			width: dp(56),
			hideable:false,
			menuDisabled: true,
			sortable:false,
			groupable:false,
			renderer: function (v, p, record) {
				p.css += ' x-grid3-check-col-td';
				var disabledCls = '';
				if (this.isDisabled(record))
					disabledCls = ' x-item-disabled';

				return String.format('<div class="x-grid3-check-col{0}' + disabledCls + '" {1}></div>',
					(v=='completed' || v =='cancelled') ? '-on' : '',
					record.json.color ? 'style="color:#'+record.json.color+'"' : '');
			}
		});

		this.checkColumn.on('change', function(record){
			// this.store.reload({
			// 	callback:function(){
					// update task
			var wasComplete = record.json.progress == 'completed' || record.json.progress == 'cancelled';
			go.Db.store("Task").set({update: {
				[record.data.id]: {progress: (!wasComplete ? 'completed' : 'needs-action')}}
			});
			// 	},
			// 	scope:this
			// });
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

		Ext.apply(this, {		
			columns: [
				this.checkColumn,
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: dp(35),
					sortable: true,
					dataIndex: 'id'
				},{
					id: 'icons',
					width: dp(60),
					renderer: function(v,m,rec) {
						var icons = [];
						if(rec.json.recurrenceRule) {
							icons.push('repeat');
						}
						if(rec.json.priority < 5) {
							icons.push('low_priority');
						}
						if(rec.json.priority > 5) {
							icons.push('priority_high');
						}
						if(rec.json.filesFolderId) {
							icons.push('attachment');
						}
						if(!Ext.isEmpty(rec.json.alerts)) {
							icons.push('alarm');
						}
						return icons.map(i => '<i class="icon small">'+i+'</i>').join('');
					}
				},
				{
					id: 'title',
					header: t('Title'),
					width: dp(75),
					sortable: true,
					dataIndex: 'title',
					renderer: function(v,m,r) {
						if(r.json.color) {
							m.style += 'color:#'+r.json.color+';';
						}
						return v;
					}
				},{
					xtype:"datecolumn",
					id: 'start',
					dateOnly: true,
					header: t('Start at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'start'
				},{
					xtype:"datecolumn",
					id: 'due',
					dateOnly: true,
					header: t('Due at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'due',
				},{
					width:dp(112),
					header: t("% complete", "tasks"),
					dataIndex: 'percentComplete',
					renderer:function (value, meta, rec, row, col, store){
						return '<div class="go-progressbar"><div style="width:'+Math.ceil(value)+'%"></div></div>';
					}
				},{
					header: t('Responsible'),
					width: dp(160),
					sortable: true,
					dataIndex: 'responsible',
					renderer: function(v) {
						return v ? go.util.avatar(v.displayName,v.avatarId)+' '+v.displayName : "-";
					}
				},{
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

		go.modules.community.tasks.TaskGrid.superclass.initComponent.call(this);
	}
});

