go.modules.community.tasks.ProjectTaskGrid = function (config) {
	if (!config) {
		config = {};
	}
	var checkColumn = new GO.grid.CheckColumn({ // TODO: Do we need this?
		width: dp(64),
		dataIndex: 'selected',
		hideable: false,
		sortable: false,
		menuDisabled: true,
		listeners: {
			change: this.onCheckChange,
			scope: this
		},
		isDisabled: function (record) {
			return record.data.id === 1;
		}
	});
	var summary = new Ext.grid.GridSummary();

	var addTimeRegAction = '';

	if (go.Modules.isAvailable("legacy", "timeregistration2")) {
		addTimeRegAction = new Ext.ux.grid.RowActions({
			header: '&nbsp;',
			autoWidth: true,
			align: 'center',
			actions: [{
				iconCls: 'ic-alarm-add',
				qtip: t("Time entry", "projects2")
			}]
		});
		addTimeRegAction.on({
			scope: this,
			action: function (grid, record, action, row, col) {

				grid.getSelectionModel().selectRow(row);

				switch (action) {
					case 'ic-alarm-add':

						if (!this.timeEntryDialog) {
							// TODO: Refactor this as well?
							this.timeEntryDialog = new GO.projects2.TimeEntryDialog({
								id: 'pm-timeentry-dialog-grid'
							});
							this.timeEntryDialog.on('submit', function () {
								GO.request({
									url: 'projects2/task/save',
									method: 'POST',
									params: {
										project_id: this.project_id,
										data: Ext.encode(this.getGridData())
									},
									success: function (response, options, result) {
										this.store.load();
									},
									scope: this
								});
							}, this);
						}


						this.timeEntryDialog.show(0, {
							loadParams: {
								task_id: record.data.id,
								project_id: record.data.project_id
							}
						});
						break;

				}
			}
		}, this);

		config.plugins = [summary, addTimeRegAction];
	}

	config.cls = "go-project-task-grid";
	config.clicksToEdit = 2;
	config.title = t("Tasks", 'tasks', 'community');

	config.scrollLoader = false;

	this.selectResource = new GO.projects2.SelectResource();

	this.selectResource.store.on('load', function () {
		this.setDisabled(!this._tasksPanelEnabled || this.selectResource.store.getCount() == 0);
	}, this)

	var fields = {
		fields: ['id', 'parent_description', 'project_id', 'user_id', 'percentage_complete', 'duration', 'hours_booked', 'due_date', 'auto_date', 'description', 'sort_order', 'hours_over_budget', 'late', 'parent_id', 'has_children'],
		columns: [{
			id: 'start',
			header: t('Start'),
			sortable: false,
			dataIndex: 'start',
			menuDisabled: true,
			hideable: false,
			renderer: function (name, cell, record) {
				/* Old code
						metaData.css='';
		if(record.data.late)
			metaData.css='projects-late';

		if(GO.util.empty(value))
			return record.data.auto_date;


		var str=typeof(value.dateFormat)=='undefined' ? value : value.dateFormat(GO.settings.date_format);

		str += '<span class="pm-task-manual-date">&nbsp;[M]</span>';

		return str;
				 */
				return typeof (value.dateFormat) == 'undefined' ? value : value.dateFormat(go.User.dateFormat);
			}

		}, {
			id: 'due',
			header: t('Due'),
			sortable: false,
			dataIndex: 'due',
			menuDisabled: true,
			hideable: false,
			renderer: function (name, cell, record) {
				/* Old code
						metaData.css='';
		if(record.data.late)
			metaData.css='projects-late';

		if(GO.util.empty(value))
			return record.data.auto_date;


		var str=typeof(value.dateFormat)=='undefined' ? value : value.dateFormat(GO.settings.date_format);

		str += '<span class="pm-task-manual-date">&nbsp;[M]</span>';

		return str;
				 */
				return typeof (value.dateFormat) == 'undefined' ? value : value.dateFormat(go.User.dateFormat);
			}

		}, {
			id: 'description',
			header: t('Description'),
			sortable: false,
			dataIndex: 'description',
			menuDisabled: true,
			hideable: false,
			editor: new Ext.grid.GridEditor(
				this.descriptionField = new Ext.form.TextArea({
					height: 150,
					width: dp(224),
					allowBlank: true,
					fieldLabel: t("Description")
				}), {
					autoSize: false
				}),
			renderer: GO.util.nl2br
		}, {
			id: 'percentComplete',
			header: t("Percent complete", 'tasks', 'community'),
			dataIndex: 'percentComplete',
			menuDisabled: true,
			width: dp(260),
			hideable: false,
			editor: new Ext.grid.GridEditor(new GO.form.NumberField({minValue: 0, maxValue: 100, decimals: 0})),
			renderer: function (value, meta, rec, row, col, store) {
				return '<div class="pm-progressbar">' +
					'<div class="pm-progress-indicator" style="width:' + Math.ceil(GO.util.unlocalizeNumber(value)) + '%"></div>' +
					'</div>';
			}
		}, {
			header: t("Duration", "projects2"),
			dataIndex: 'duration',
			summaryType: 'sum',
			hidden: true,
			width: dp(64),
			editor: new Ext.grid.GridEditor(new go.form.NumberField())
		}, {
			header: t("Hours booked", "projects2"),
			dataIndex: 'hours_booked',
			width: dp(72),
			renderer: function (value, metaData, record, rowIndex, colIndex, ds) {
				// TODO
				// if(record.data.hours_over_budget) {
				// 	metaData.css = 'projects-late';
				// }
				return value;
			},
			summaryType: 'sum'
		}, {
			width: dp(72),
			header: t("Employee", "projects2"),
			dataIndex: 'user_id',
			renderer: this.renderResource.createDelegate(this),
			editor: new Ext.grid.GridEditor(this.selectResource)
		}, {
			header: "Group",
			hidden: true,
			hideable: false,
			dataIndex: 'parent_description',
			groupable: true
		}]
	};
	if (go.Modules.isAvailable("legacy", "timeregistration2")) {
		fields.columns.push(addTimeRegAction);
	}
	config.hideCollapseTool = true;

	fields.columns.push()

	config.store = new go.data.Store({
		sortInfo: {
			field: 'name',
			direction: 'ASC'
		},
		remoteSort: false,
		baseParams: {
			limit: 0,
			tasklistId: this.tasklistId
		},
		fields: [
			'id',
			'title',
			'description',
			'percentComplete',
			'color',
			'start',
			'due',
			'progress'
		],

		entityStore: "Task"
	});

	var columnModel = new Ext.grid.ColumnModel({
		defaults: {
			sortable: false,
			groupable: false
		},
		columns: fields.columns
	});

	config.cm = columnModel;
	config.view = new Ext.grid.GroupingView({
		emptyText: t("Tasks", "tasks", "community"),
		hideGroupedColumn: true,
		showGroupName: false
	});
	config.sm = new Ext.grid.RowSelectionModel();
	config.loadMask = true;

	config.tbar = [{
		iconCls: 'ic-add',
		text: t("Add"),
		handler: function () {
			this.addNewRow();
		},
		scope: this
	}, {
		iconCls: 'ic-delete',
		tooltip: t("Delete"),
		handler: function () {
			this.deleteSelected();
		},
		scope: this
	}, '-', this.ungroupButton = new Ext.Button({
		text: t("Ungroup", "projects2"),
		disabled: true,
		handler: function () {
			this.ungroupSelection();
		},
		scope: this
	}), this.groupButton = new Ext.Button({
		text: t("Group", "projects2"),
		disabled: true,
		handler: function () {
			this.showToGroupDialog();
		},
		scope: this
	}), '->', {
		iconCls: 'ic-save',
		text: t("Save"),
		handler: function () {
			this.save();
		},
		scope: this
	}];

	go.modules.community.tasks.ProjectTaskGrid.superclass.constructor.call(this, config);

	this.addEvents({
		'saved': true
	});

	this.getSelectionModel().on('selectionchange', function (sm) {
		this.ungroupButton.setDisabled(true);
		this.groupButton.setDisabled(true);

		var selections = sm.getSelections();

		for (var i = 0; i < selections.length; i++) {
			if (selections[i].data.parent_id > 0) {
				this.ungroupButton.setDisabled(false);
			} else {
				this.groupButton.setDisabled(false);
			}
		}
	}, this);
};


Ext.extend(go.modules.community.tasks.ProjectTaskGrid, go.grid.EditorGridPanel, {
	project_id: 0,
	disabled: true,
	_recordsDeleted: false,
	_tasksPanelEnabled: false,

	setProjectId: function (projectId, tasksPanelEnabled) {
		this.projectId = this.store.baseParams.projectId = projectId;

		this._tasksPanelEnabled = tasksPanelEnabled;

		this.selectResource.setProjectId(this.projectId);

		this.setDisabled(!this._tasksPanelEnabled || !this.projectId || this.selectResource.store.getCount() == 0);

		// if (this._tasksPanelEnabled)
		// 	this.expand();
		// else
		// 	this.collapse();
		//
		if (projectId) {
			this.store.load();
		} else {
			this.store.removeAll();
		}
		this._recordsDeleted = false;
	},

	renderResource: function (value, p, record, rowIndex, colIndex, ds) {
		var cm = this.getColumnModel();
		var ce = cm.getCellEditor(colIndex, rowIndex);

		var val = '';

		var record = ce.field.store.getById(value);
		if (record !== undefined) {
			val = record.get("user_name");
		}
		return val;
	},

	reset: function () {
		this.setValue([]);
		this.dirty = false;
	},

	setValue: function (groups) {
		this._isDirty = false;
		this.value = groups || {};
		this.store.load().catch(function () {
		}); //ignore failed load becuase onBeforeStoreLoad can return false
	},

	onBeforeStoreLoad: function (store, options) {
		//don't add selected on search
		if (this.store.filters.tbsearch || options.selectedLoaded || options.paging) {
			this.store.setFilter('exclude', null);
			return true;
		}
		return false;
	},

	getValue: function () {
		return this.value;
	},

	markInvalid: function (msg) {
		this.getEl().addClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.mark(this, msg);
	},
	clearInvalid: function () {
		this.getEl().removeClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.clear(this);
	},

	validate: function () {
		return true;
	},

	isValid: function (preventMark) {
		return true;
	},

	startEditing: function (row, col) {
		go.modules.community.tasks.ProjectTaskGrid.superclass.startEditing.call(this, row, col);

		//expand combo when editing
		if (this.activeEditor) {
			this.activeEditor.field.onTriggerClick();
		}
	},

	onCheckChange: function (record, newValue) {
		// if (newValue) {
		// 	record.set('level', this.addLevel);
		// 	this.value[record.data.id] = record.data.level;
		// } else {
		// 	record.set('level', null);
		// 	this.value[record.data.id] = null;
		// }

		this._isDirty = true;
	},

	afterEdit: function (e) {
		// this.value[e.record.id] = e.record.data.level;
		this._isDirty = true;
	},

	afterRender: function () {
		go.modules.community.tasks.ProjectTaskGrid.superclass.afterRender.call(this);

		var form = this.findParentByType("entityform");

		if (!form) {
			return;
		}

		if (!this.store.loaded) {
			this.store.load();
		}

		form.on("load", function (f, v) {
			this.setDisabled(v.permissionLevel < go.permissionLevels.manage); // TODO: determine correct level
		}, this);

		//Check form currentId becuase when form is loading then it will load the store on setValue later.
		//Set timeout is used to make sure the check will follow after a load call.
		var me = this;
		setTimeout(function () {
			if (!go.util.empty(me.value) && !form.currentId) {
				me.store.load();
			}
		}, 0);
	},

	isFormField: true,

	getName: function () {
		return this.name;
	},

	_isDirty: false,

	isDirty: function () {
		return this._isDirty || this.store.getModifiedRecords().length > 0;
	}
});