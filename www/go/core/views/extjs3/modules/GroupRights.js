go.modules.GroupRights = Ext.extend(go.Window, {

	module: {},
	package: null,
	width: 800,
	height: 600,
	layout:'fit',

	filterUnused: function(store) {
		store.filterBy(function(record, id) {
			if(record.data.name === 'Admins'){
				return false;
			}
			return !this.module.permissions || this.module.permissions[id] === undefined
			//return this.groupStore.find('groupId', id) === -1;
		},this);
	},

	initComponent: function () {

		this.items = [this.formPanel = new Ext.FormPanel({
			tbar: [{
				xtype: 'gocombo',
				editable: false,
				displayField: 'name',
				valueField: 'id',
				width: 500,
				triggerAction: 'all',
				listWidth: 500,
				store: {
					xtype: 'gostore',
					fields: ['id', 'name', 'isUserGroupFor'],
					entityStore: "Group",
					listeners: {
						load: (store) => {this.filterUnused(store);}
					}
				},
				emptyText: t('Add group'),
				//value: t('Add group'),
				listeners: {
					expand: (cb) => {
						this.filterUnused(cb.store);
					},
					select: (cb, record) => {

						this.gridfield.stopEditing();
						this.gridfield.store.insert(0, new Ext.data.Record({
							groupId: record.data.id,
							groupName: record.data.name,
							isUserGroupFor: record.data.isUserGroupFor
						}));
						this.module.permissions = this.module.permissions || {};
						this.module.permissions[record.data.id] = {groupName: record.data.name};
						//this.gridfield.startEditing(0, 1);

						cb.setValue(null);
					}
				},
			}],
			layout: 'fit',
			items: [this.gridfield = new go.form.GridField({
				hideHeaders: false,
				autoHeight: false,
				cls: '',
				name: "permissions",
				columns: [{dataIndex:'groupId'}],
				store: {
					xtype:'gostore',
					autoDestroy: true,
					root: "records",
					fields: ['groupId'],
				},
				mapKey: 'groupId', //todo
			})],
			buttons: ['->', {
				text: t("Save"),
				handler: function () {
					this.submit();
				},
				scope: this
			}]
		})];

		this.supr().initComponent.call(this);

		this.gridfield.getBottomToolbar().hide(); // replace with tbar
	},

	/**
	 *
	 * @param {string} module module name
	 * @param {string[]} rights package name
	 */
	show: function (module, rights) {
		this.module = module;
		this.setTitle(t("Permissions")  + ' ' + module.name);

		this.configureGrid(rights);

		this.supr().show.call(this);
		this.formPanel.form.setValues(module);

	},

	configureGrid: function(rights) {
		this.permissionsTypes = rights;
		let cols = [{
				xtype: 'gridcolumn',
				header: t('Group'),
				dataIndex: 'groupId',
				align:'left',
				renderer: function(v, m, r) {
					return '<i class="icon">' + (r.data.isUserGroupFor ? 'person' : 'people') + '</i>&nbsp;&nbsp;' + r.data.groupName;
				}
			}],
			fields = [
				'groupId',
				{name:'groupName', type:"promise", promise: (data) => {
					return go.Db.store('Group').single(data.groupId).then(e => e.name)
				}},
				{name:'isUserGroupFor', type:"promise", promise: (data) => {
						return go.Db.store('Group').single(data.groupId).then(e => e.isUserGroupFor)
					}}
			];
		for(let prop of rights) {
			fields.push({name: prop, mapping: 'rights.'+prop});
			cols.push({
				header: t(prop, this.module.name, this.module.package),
				dataIndex: prop,
			})
		}

		this.gridfield.reconfigure(new go.data.Store({
			autoDestroy: true,
			root: "records",
			fields: fields
		}), new Ext.grid.ColumnModel({
			defaults: {
				xtype:'checkcolumn',
				sortable: false,
				hideable: false,
				draggable: false,
				menuDisabled: true,
				align:'center'
			},
			columns:cols
		}));

		this.gridfield.store.on('remove' ,function(store,record) {
			delete this.module.permissions[record.data.groupId];
		},this)
	},

	getParsedPermissions: function() {
		var p = this.gridfield.getValue();
		var result = {};
		for(var i = 0 ; i < p.length; i++){
			result[p[i].groupId] = {groupId: p[i].groupId, rights: {}};
			for(var right of this.permissionsTypes) {
				if(p[i][right] === true) {
					result[p[i].groupId].rights[right] = true;
				}
			}
		}
		return result;
	},

	submit: function(){
		var permissions = this.getParsedPermissions();
		go.Db.store('Module').set({update: {[this.module.id]:{permissions: permissions}}},() => {
			this.gridfield.store.commitChanges();
			this.close();
		});
	}
})
