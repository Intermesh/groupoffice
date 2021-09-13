go.modules.community.tasks.TaskGrid = Ext.extend(go.grid.GridPanel, {
	autoExpandColumn: 'title',
	// config options for stateful behavior
	stateful: true,
	stateId: 'tasks-grid',

	initComponent: function () {

		this.store = new go.data.GroupingStore({
			groupField: 'tasklist',
			remoteGroup:true,
			fields: [
				'id',
				'title',
				{name: 'start', type: "date"},
				{name: 'due', type: "date"},
				'description',
				'repeatEndTime',
				{name: 'responsible', type: 'relation'},
				{name: 'createdAt', type: 'date'},
				{name: 'modifiedAt', type: 'date'},
				{name: 'creator', type: "relation"},
				{name: 'modifier', type: "relation"},
				{name: 'tasklist', type: "relation"},
				'percentComplete',
				'progress',{
					name: "complete",
					convert: function(v, data) {
						return data.progress == 'completed';
					}
				},
				'estimatedDuration',
				'timeBooked'
			],
			entityStore: "Task",
			sortInfo: {
				field: "start",
				direction: "ASC"
			}
		});

		this.checkColumn = new GO.grid.CheckColumn({
			id:'complete',
			dataIndex: 'complete',
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
					v ? '-on' : '',
					record.json.color ? 'style="color:#'+record.json.color+'"' : '');
			}
		});

		this.checkColumn.on('change', function(record){

			var wasComplete = record.json.progress == 'completed' || record.json.progress == 'cancelled';
			go.Db.store("Task").set({update: {
				[record.data.id]: {progress: (!wasComplete ? 'completed' : 'needs-action')}}
			});

		}, this);

		const startRenderer = function(v, meta, record) {

			if(record.data.due && record.data.due.format("Ymd") < (new Date).format("Ymd")) {
				meta.css = "danger";
			}

			return go.util.Format.date(v);
		};

		this.columns = [
				this.checkColumn,
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: dp(35),
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'title',
					header: t('Title'),
					width: dp(300),
					sortable: true,
					dataIndex: 'title',
					renderer: function(v,m,rec) {
						if(rec.json.color) {
							m.style += 'color:#'+rec.json.color+';';
						}

						return v;
					}
				},{
					id: 'icons',
					width: dp(60),
					renderer: function(v,m,rec) {
						var v = "";
						if(rec.json.priority != 0) {
							if (rec.json.priority < 5) {
								v += '<i class="icon small orange">priority_high</i>';
							}
							if (rec.json.priority > 5) {
								v += '<i class="icon small blue">low_priority</i>';
							}
						}
						if(rec.json.recurrenceRule) {
							v += '<i class="icon small">repeat</i>';
						}
						if(rec.json.filesFolderId) {
							v += '<i class="icon small">attachment</i>';
						}
						if(!Ext.isEmpty(rec.json.alerts)) {
							v += '<i class="icon small">alarm</i>';
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
					dataIndex: 'start',
					renderer: startRenderer
				},{
					xtype:"datecolumn",
					id: 'due',
					dateOnly: true,
					header: t('Due at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'due',
					renderer: startRenderer,
					hidden: true
				},{
					header: t('Responsible'),
					width: dp(240),
					sortable: true,
					dataIndex: 'responsible',
					renderer: function(v) {
						return v ? go.util.avatar(v.displayName,v.avatarId)+' '+v.displayName : "-";
					}
				},{
					width:dp(150),
					header: t('% complete', "tasks", "community"),
					dataIndex: 'percentComplete',
					renderer:function (value, meta, rec, row, col, store){
						return '<div class="go-progressbar"><div style="width:'+Math.ceil(value)+'%"></div></div>';
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
					id: 'modifiedAt',
					header: t('Modified at'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifiedAt',
					hidden: true
				},
				{	
					header: t('Created by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'creator',
					renderer: function(v) {
						return v ? v.displayName : "-";
					},
					hidden: true
				},
				{
					header: t('Tasklist'),
					width: dp(160),
					sortable: true,
					dataIndex: 'tasklist',
					renderer: function(v) {
						return v ? v.name : "-";
					},
					hidden: true
				},
				{	
					header: t('Modified by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifier',
					renderer: function(v) {
						return v ? v.displayName : "-";
					},
					hidden: true
				}
			];

		if(this.forProject) {
			this.columns.push({
					header: t("Estimated duration", ),
					dataIndex: 'estimatedDuration',
					width: dp(64),
					renderer: function (value, metaData, record, rowIndex, colIndex, ds) {
						if(parseInt(value) > 0) {
							return go.util.Format.duration(value, false , false);
						}
						return '';
					}
				},
				{
					header: t("Hours booked", "tasks", 'community'),
					dataIndex: 'timeBooked',
					width: dp(72),
					renderer: function (value, metaData, record, rowIndex, colIndex, ds) {
						if(parseInt(value) > 0) {
							var v = parseInt(value);
							if(parseInt(record.data.estimatedDuration) > 0 && v > parseInt(record.data.estimatedDuration) ) {
								metaData.css = 'projects-late';
							}
							return go.util.Format.duration(v);
						}
						return '';
					}
				});
		}

		if(!this.view) {
			this.view = new go.grid.GroupingView({
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				hideGroupedColumn: true
			});
		}

		go.modules.community.tasks.TaskGrid.superclass.initComponent.call(this);
	}
});

