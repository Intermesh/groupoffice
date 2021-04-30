go.modules.community.tasks.ProjectTaskGrid = function (config) {
	if (!config) {
		config = {};
	}
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
							// TODO: Refactor to JMAP

							this.timeEntryDialog = new go.modules.community.tasks.TimeEntryDialog({
								id: 'pm-timeentry-dialog-grid'
							});
							/*
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

							 */
						}


						this.timeEntryDialog.show(0, {
							loadParams: {
								task_id: record.data.id,
								project_id: this.projectId
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
		this.setDisabled(!this._tasksPanelEnabled || this.selectResource.store.getCount() === 0);
	}, this)

	var fields = {
		fields: ['id', 'group.name', 'projectId', 'responsibleUserId', 'percentageComplete', 'estimatedDuration', 'timeBooked', 'due', 'start', 'description' , 'groupId'],
		columns: [{
			id: 'start',
			header: t('Start'),
			sortable: false,
			dataIndex: 'start',
			menuDisabled: true,
			hideable: false,
			editor: new go.form.DateField({
				format:go.User.date_format,
				emptyText:'Auto'
			}),
			summaryType: 'count',
			summaryRenderer:function(value){
				return value+' '+t("Jobs", "projects2");
			},

			renderer: function (name, cell, record) {
				return go.util.Format.date(name);
			}

		}, {
			id: 'due',
			header: t('Due'),
			sortable: false,
			dataIndex: 'due',
			menuDisabled: true,
			hideable: false,
			editor: new go.form.DateField({
				format:go.User.date_format,
				emptyText:'Auto'
			}),
			summaryType: 'count',
			summaryRenderer:function(value){
				return value+' '+t("Jobs", "projects2");
			},

			renderer: function (name, cell, record) {
				if(Ext.isEmpty(name)) {
					return '';
				}
				return go.util.Format.date(name);
			}

		},{
			id: 'title',
			header: t('Title'),
			sortable: false,
			dataIndex: 'title',
			menuDisabled: true,
			hideable: false,
			editor: new Ext.grid.GridEditor(
				this.titleField = new Ext.form.TextField({
					allowBlank: false,
					fieldLabel: t("Title")
				}), {
					autoSize: false
				}),
			renderer: go.util.nl2br
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
			renderer: go.util.nl2br
		}, {
			id: 'percentComplete',
			header: t("Percent complete", 'tasks', 'community'),
			dataIndex: 'percentComplete',
			menuDisabled: true,
			width: dp(260),
			hideable: false,
			editor: new Ext.grid.GridEditor(new go.form.NumberField({minValue: 0, maxValue: 100, decimals: 0})),
			renderer: function (value, meta, rec, row, col, store) {
				return '<div class="pm-progressbar">' +
					'<div class="pm-progress-indicator" style="width:' + Math.ceil(GO.util.unlocalizeNumber(value)) + '%"></div>' +
					'</div>';
			}
		},/* {
			header: t("Duration", "projects2"),
			dataIndex: 'estimatedDuration',
			summaryType: 'sum',
			hidden: true,
			width: dp(64),
			editor: new Ext.grid.GridEditor(new go.form.NumberField())
		},*/ {
			header: t("Hours booked", "projects2"),
			dataIndex: 'timeBooked',
			width: dp(72),
			renderer: function (value, metaData, record, rowIndex, colIndex, ds) {
				debugger;
				// TODO?
				// if(record.data.hours_over_budget) {
				// 	metaData.css = 'projects-late';
				// }
				return value;
			},
			summaryType: 'sum'
		}, {
			id: 'employee',
			width: dp(72),
			header: t("Employee", "projects2"),
			dataIndex: 'responsibleUserId',
			renderer: this.renderResource.createDelegate(this),
			editor: new Ext.grid.GridEditor(this.selectResource)
		}/*, {
			id: 'groupId',
			header: "Group",
			hidden: false,
			hideable: false,
			dataIndex: 'groupId',
			renderer: function(value, metaData, record, rowIndex, colIndex, ds) {
				if(Ext.isEmpty(value)) {
					return '';
				}
				return record.data.group[record.data.tasklistId].name;//record.data.group[record.tasklistId].name;
			},

			groupable: true
		}*/]
	};
	if (go.Modules.isAvailable("legacy", "timeregistration2")) {
		fields.columns.push(addTimeRegAction);
	}
	config.hideCollapseTool = true;

	fields.columns.push()

	config.store = new go.data.GroupingStore({
		fields: [
			'id',
			'title',
			'groupId',
			'description',
			'percentComplete',
			'color',
			'start',
			'due',
			'progress',
			'responsibleUserId',
			'tasklistId',
			'group',
			'timeBooked'
		],
		sortInfo: {
			field: 'start',
			direction: 'ASC'
		},
		remoteSort: true,
		remoteGroup: true,
		filters: {
			projectId: this.projectId
		},
		groupField: 'groupId',
		entityStore: "Task"
	});

	config.cm = new Ext.grid.ColumnModel({
		defaults: {
			sortable: false,
			groupable: false
		},
		columns: fields.columns
	});
	config.view = new go.grid.GroupingView({
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
		text: t("Delete"),
		handler: function () {
			this.deleteSelected();
		},
		scope: this
	},/* Disabled until further notice. Not sure whether needed anymore'-', this.ungroupButton = new Ext.Button({
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
	}),*/ '->', {
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

	// Temporarily disabled because unclear whether task grouping is still valid
	// this.getSelectionModel().on('selectionchange', function (sm) {
		// this.ungroupButton.setDisabled(true);
		// this.groupButton.setDisabled(true);
		//
		// var selections = sm.getSelections();
		//
		// for (var ii = 0,il=selections.length; ii < il; ii++) {
		// 	if (selections[ii].data.groupId > 0) {
		// 		this.ungroupButton.setDisabled(false);
		// 	} else {
		// 		this.groupButton.setDisabled(false);
		// 	}
		// }
	// }, this);
};


Ext.extend(go.modules.community.tasks.ProjectTaskGrid, go.grid.EditorGridPanel, {
	projectId: 0,
	disabled: true,
	_tasksPanelEnabled: false,

	setProjectId: function (projectId, tasksPanelEnabled) {
		tasksPanelEnabled = tasksPanelEnabled || false;
		this.projectId = projectId;

		this._tasksPanelEnabled = tasksPanelEnabled;

		if (tasksPanelEnabled) {
			this.selectResource.setProjectId(this.projectId);
		}

		this.setDisabled(!this._tasksPanelEnabled || !this.projectId || this.selectResource.store.getCount() == 0);

		if (projectId) {
			this.store.setFilter('projectId', {projectId: this.projectId}).load();
				// .then(function(result) {
				// 	console.log(result);
				// });
		} else {
			this.store.removeAll();
		}
	},

	deleteSelected : function(){
		var selectedRows = this.selModel.getSelections();
		if(selectedRows.length > 0) {
			Ext.MessageBox.confirm(t('Confirm'), t('Are you sure you wish to remove the selected task(s)?'), function(btn){
				if(btn !== 'yes') {
					return;
				}
				var params = {};
				params.destroy = []
				for(var ii=0, il=selectedRows.length;ii<il;ii++) {
					params.destroy.push(selectedRows[ii].id);
				}
				go.Db.store("Task").set(params);

			});
		}

	},

	addNewRow: function (groupName) {
		this.stopEditing();
		var description = groupName || "",
			index = this.store.getCount(),
			sm = this.getSelectionModel(),
			rows = sm.getSelections(),
			tasklistId = 0, // TODO: get tasklistId if no tasks available
			groupId = null,
			user_id,
			parent_description = t("Ungrouped", "projects2");

		if (rows.length > 0) {
			index = this.store.indexOf(rows[rows.length - 1]) + 1;
		}

		if (description) {
			index = 0; // If there is a description, then this is a group task.
			// todo: get current GroupId
		}
		var previousRecord = this.store.getAt(index - 1);
		if (previousRecord) {
			tasklistId = previousRecord.data.tasklistId;
			user_id = previousRecord.data.responsibleUserId;
		} else {

			var resource = this.selectResource.store.find('user_id', user_id);
			if (resource === -1) {
				var firstRecord = this.selectResource.store.getAt(0);
				user_id = firstRecord.id;
			}
		}

		var e = {};
		e['new_' + tasklistId + '_' + this.store.getCount()] = {
			description: description,
			start: '',
			due: '',
			responsibleUserId: user_id,
			title: t('New task', 'tasks'),
			estimatedDuration: GO.util.numberFormat(1),
			percentComplete: 0,
			tasklistId: tasklistId,
			groupId: groupId
		};

		go.Db.store('Task').set({create: e});

		var colIndex = this.getColumnModel().getIndexById('title');

		sm.selectRow(index);
		this.startEditing(index, colIndex);

		return index;

	},

	/**
	 * Since saving directly into a grouping store does not work, we manually get the changes and create our own JMAP call
	 * TODO? Refactor delete function into this function as well?
	 */
	save: function() {
		if(!this.isDirty()) {
			Ext.MessageBox.alert(t('Nothing to save'), t('No changes have been made'));
			return;
		}
		var queue = {},
			rs = this.store.getModifiedRecords(),
			hasChanges = false;
		queue.create = {};
		queue.update = {};
		for(var r,i = 0;r = rs[i]; i++){
			if(!r.isValid()) {
				continue;
			}
			hasChanges = true;
			var change = {}, attr;
			for(attr in r.modified) {
				change[attr] = r.data[attr];
			}
			queue[r.phantom?'create':'update'][r.id] = change;
		}
		if(!hasChanges) {
			return;
		}
		go.Db.store("Task").set(queue);
	},

	renderResource: function (value, p, record, rowIndex, colIndex, ds) {
		var cm = this.getColumnModel();
		var ce = cm.getCellEditor(colIndex, rowIndex);

		var val = '';
		var userRecord = ce.field.store.getById(value);
		if (userRecord !== undefined) {
			val = userRecord.get("user_name");
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

		//expand combo when editing TODO: Test whether still needed
		if (this.activeEditor && this.activeEditor.field.onTriggerClick) {
			this.activeEditor.field.onTriggerClick();
		}
	},
	// afterEdit: function (e) {
		// 	debugger;
		// 	this.value[e.record.id] = e.record.data.level;
		// this._isDirty = true;
	// },
	onEditComplete : function(ed, value, startValue){
		go.modules.community.tasks.ProjectTaskGrid.superclass.onEditComplete.call(this, ed, value, startValue);
		// if(ed.col==5 && ed.row==this.store.getCount()-1)
		// 	this.addNewRow();
		if(value !== startValue) {
			this._isDirty = true;
		}
	},

	afterRender: function () {
		// TODO: Do we need this handler anyway?
		go.modules.community.tasks.ProjectTaskGrid.superclass.afterRender.call(this);


		var form = this.findParentByType("entityform");

		if (!form) {
			return;
		}

		if (!this.store.loaded) {
			this.store.load();
		}

		form.on("load", function (f, v) {
			this.setDisabled(v.permissionLevel < go.permissionLevels.manage);
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

	groupSelection: function(groupName) {
		// TODO;
	},

	showToGroupDialog: function () {
		// TODO: Refactor into JMAP
		if (!this.toGroupDialog) {
			this.toGroupDialog = new GO.projects2.ToGroupDialog(); // TODO: tasks?
			this.toGroupDialog = new go.modules.community.tasks.TasklistGroupDialog();
			this.toGroupDialog.on('groupName', function (groupName) {
				this.groupSelection(groupName);
			}, this);
		}
		this.toGroupDialog.show();

	},

	ungroupSelection: function () {
		var selectedRows = this.selModel.getSelections();

		for (var ii = 0, il=selectedRows.length; ii < il; ii++) {
			selectedRows[ii].set('groupId', null);
		}

		this.save();
		this.ungroupButton.setDisabled(true);
		this.groupButton.setDisabled(false);
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