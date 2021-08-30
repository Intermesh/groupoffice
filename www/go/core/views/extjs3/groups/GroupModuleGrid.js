go.groups.GroupModuleGrid = Ext.extend(go.grid.EditorGridPanel, {
	/*
	 * the form field name
	 */
	//name: "modules",
	submit:false,

	cls: "go-group-module-grid",

	clicksToEdit: 1,

	showLevels: true,

	title: t("Modules"),

	scrollLoader: false,

	initComponent: function () {

		if (!this.value) {
			this.value = {};
		}

		var checkColumn = new GO.grid.CheckColumn({
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
				return record.data.package === "core";
			}
		});

		var me = this;

		this.store = new go.data.Store({
			sortInfo: {
				field: 'name',
				direction: 'ASC'
			},
			remoteSort: false,
			baseParams: {
				limit: 0
			},
			fields: [
				'id',
				'name',
				{
					name: 'label',
					type: {
						convert: function (v, data) {
							const localized = t('name', data.name, data.package, true);
							return (localized === 'name') ? data.name : localized;
						}
					},
					sortType: Ext.data.SortTypes.asText
				},
				'permissions',
				'package',
				// {
				// 	name: 'level',
				// 	type: {
				// 		convert: function (v, data) {
				// 			return me.value[data.id];
				// 		}
				// 	}
				// },
				{
					name: 'selected',
					type: {
						convert: function (v, data) {
							return !!(data.permissions && data.permissions[me.groupId]);
						}
					},
					sortType: function (checked) {
						return checked ? 1 : 0;
					}
				}
			],

			entityStore: "Module"
		});

		//var levelCombo = this.createLevelCombo();
		this.moduleRightsEditor = new go.groups.ModulePermissionCombo({
			store: new Ext.data.SimpleStore({
				id: 0,
				fields: ['id', 'name', 'checked'],
				data: []
			})
		});
		// var rightsCombo = new go.groups.ModulePermissionCombo({
		// 	store: new Ext.data.SimpleStore({
		// 		id:0,
		// 		fields : ['value', 'text'],
		// 		data : (this.rights || []).map(v => [t(v), v] )
		// 	}),
		// });

		Ext.apply(this, {
			plugins: [checkColumn],
			tbar: ['->', {
				xtype: 'tbsearch',
				filters: ['text']
			}],
			columns: [{
				id: 'label',
				header: t('Name'),
				sortable: false,
				dataIndex: 'label',
				menuDisabled: true,
				hideable: false,
				renderer: function (name, cell, record) {
					return '<div class="mo-title" style="background-image:url(' + go.Jmap.downloadUrl('core/moduleIcon/' + (record.data.package || "legacy") + '/' + record.data.name) + 'mtime='+go.User.session.cacheClearedAt+ ')">' + name + '</div>';
				}
			}, {
				id: 'package',
				header: t('Package'),
				sortable: false,
				dataIndex: 'package',
				menuDisabled: true,
				hideable: false
			}, {
				id: 'permissions',
				header: t("Permissions"),
				dataIndex: 'permissions',
				menuDisabled: true,
				editor: this.moduleRightsEditor,
				width: dp(460),
				hidden: !this.showLevels,
				hideable: false,
				renderer: function (v, meta, record) {

					if (!me.showLevels) {
						return "";
					}
					if (!record.data.permissions || !record.data.selected) {
						return "-";
					}
					meta.style = "position:relative";
					let result, permissions = [];
					if (record.data.permissions[me.groupId]) { // when loaded
						const rights = record.data.permissions[me.groupId].rights;
						for (var r in rights) {
							if(r == 'mayRead') {
								continue;
							}
							if (rights[r])
								permissions.push(t(r, record.json.name, record.json.package));
						}
					}
					result = (!permissions.length) ? t("Use") : permissions.join(', ');
					//debugger;
					return result + "<i class='trigger'>arrow_drop_down</i>";
					//}
					// var r = levelCombo.store.getById(v);
					// return r ? r.get('text') + "<i class='trigger'>arrow_drop_down</i></div>" : v;
				},
				sortable: true
			}, checkColumn
			],
			viewConfig: {
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				scrollOffset: 0
			},
			autoExpandColumn: 'label',
			listeners: {
				scope: this,
				'afteredit': this.afterEdit,
				'beforeedit': this.beforeEdit,
				'validateedit': this.validateEdit,
			}
//			// config options for stateful behavior
//			stateful: true,
//			stateId: 'users-grid'
		});

		this.store.on("beforeload", this.onBeforeStoreLoad, this);

		go.groups.GroupModuleGrid.superclass.initComponent.call(this);

	},

	startEditing: function (row, col) {
		go.groups.GroupModuleGrid.superclass.startEditing.call(this, row, col);
		if (this.activeEditor) {
			var record = this.getStore().getAt(row);
			if (!record.data.permissions) {
				return;
			}
			const rights = record.data.permissions[this.groupId].rights;
			let permissions = [];
			for (var r in rights) {
				if (rights[r])
					permissions.push(r);
			}
			//this.moduleRightsEditor.originalValue = record.data.permissions;
			this.moduleRightsEditor.selectedItems = permissions;

			var data = (record.json.rights || []).map(v => [v, t(v, record.json.name, record.json.package), rights[v]]);
			this.activeEditor.field.store.loadData(data);
			//expand combo when editing
			this.activeEditor.field.onTriggerClick();
		}
	},

	onCheckChange: function (record, value) {
		// console.log(record);
		var oldValue = record.data.permissions;
		if(!oldValue) {
			oldValue = {};
		}
		if (value) {
			if(!oldValue[this.groupId]) {
				oldValue[this.groupId] = {rights: {}};
			}
		} else {
			// remove me from old value
			delete oldValue[this.groupId];
		}

		record.set('permissions', oldValue);

		this._isDirty = true;
	},


	beforeEdit: function (e) {
		if (e.field === 'permissions') { // only edit this field if we have permissions. otherwise use checkbox first
			return !!(e.record.data.permissions && e.record.data.permissions[this.groupId]);
		}
		return e.record.data.id !== 1; //cancel edit for admins group
	},

	// after a cell is edited, but before the value is set in the record
	validateEdit: function (e) {
		var setOrUnsetPermissions = this.getParsedPermissions(this.moduleRightsEditor.selectedItems, e.record.json.permissions[this.groupId].rights);
		e.value = {[this.groupId]: {rights: setOrUnsetPermissions, groupId: this.groupId}};
	},

	afterEdit: function (e) {
		// this.value = this.value || {};
		// this.value[e.record.id] = {permissions: e.value};
		// console.log(e.value);
		//this.value[e.record.id] = e.record.data.level;
		this._isDirty = true;
	},

	/**
	 * parse changes from new and old permissions values
	 * @param {string[]} value
	 * @param {string[boolean]} original
	 * @returns {string[boolean]} changes
	 */
	getParsedPermissions: function (value, original) {
		var result = {};

		for (var perm of value) {
			if (!original[perm])
				result[perm] = true; // to add

		}
		for (let right in original) {
			if (value.indexOf(right) === -1) {
				//result[right] = false; // to remove
			} else {
				result[right] = true;
			}
		}
		return result;
	},

	afterRender: function () {

		go.groups.GroupModuleGrid.superclass.afterRender.call(this);

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

	isFormField: true,

	getName: function () {
		return this.name;
	},

	_isDirty: false,

	isDirty: function () {
		return this._isDirty || this.store.getModifiedRecords().length > 0;
	},

	reset: function () {
		this.setValue([]);
		this.dirty = false;
	},

	setValue: function (groups) {
		console.log(groups);
		this._isDirty = false;
		//this.value = groups || {};
		this.store.load().catch(function () {
		}); //ignore failed load becuase onBeforeStoreLoad can return false
	},

	getSelectedModuleIds: function () {
		return Object.keys(this.value).map(parseInt);
	},

	onBeforeStoreLoad: function (store, options) {

		//don't add selected on search
		if (this.store.filters.tbsearch || options.selectedLoaded || options.paging) {
			this.store.setFilter('exclude', null);
			return true;
		}
		go.Db.store("Module").get(this.getSelectedModuleIds(), function (entities) {
			this.store.loadData({records: entities}, true);
			// this.store.sortData();
			this.store.setFilter('exclude', {
				exclude: this.getSelectedModuleIds()
			});
			var me = this;
			this.store.load({
				add: true,
				selectedLoaded: true
			}).then(function () {
				me.store.multiSort([{
					field: 'selected',
					direction: 'DESC'
				}, {
					field: 'label',
					direction: 'ASC'
				}]);
			});
		}, this);

		return false;
	},

	getValue: function () {
		let v = {};
		this.store.getModifiedRecords().forEach((r) => {
			v[r.id] = {permissions: r.data.permissions};
		});

		return v;
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
	}
});


