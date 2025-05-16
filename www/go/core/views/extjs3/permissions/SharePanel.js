go.permissions.SharePanel = Ext.extend(go.grid.EditorGridPanel, {
	/*
	 * the form field name
	 */
	name: "acl",
	
	cls: "go-share-panel", 
	
	clicksToEdit: 1,
	
	showLevels: true,

	title: t("Permissions"),
	
	trackMouseOver: true,

	/**
	 * Default level when groups are checked
	 */
	addLevel: go.permissionLevels.read,

	value: null,

	enableDelete: false,

	disableSelection: true,

	/**
	 * Generally not needed.
	 * If set, the ACL id of this property will be used for sorting the selected groups on top.
	 * If not set it will use the standard AclOwnerEntity acl id for it.
	 */
	aclIdProp: null,

	initComponent: function () {
		
		var checkColumn = new GO.grid.CheckColumn({
			width: dp(64),
			dataIndex: 'selected',
			hideable: false,
			sortable: false,
			menuDisabled: true,
			listeners: {
				change: this.onCheckChange,
				scope: this
			}
		});
		
		var me = this;
		
		this.store = new go.data.Store({
			sortInfo: {
				field: 'name',
				direction: 'ASC'
			},
			filters: {
				hideUsers: {hideUsers: true},
				hideGroups: {hideGroups: false}
			},
			fields: [
				'id', 
				'name', 
				{name: 'user', type: "relation"}, //fetches entity from store
				{name: 'users', type: "relation", limit: 3},
				{
					name: 'level', 
					type: {
						convert: function (v, data) {							
							return me.value ? me.value[data.id] : null;
						}
					}
				},
				{
					name: 'selected', 
					type: {
						convert: function (v, data) {
							return me.getSelectedGroupIds().indexOf(parseInt(data.id)) > -1;
						}
					},
					sortType:function(checked) {
						return checked ? 1 : 0;
					}
				}
			],

			baseParams: {
			},
			entityStore: "Group"
		});
		
		var levelCombo = this.createLevelCombo();

		Ext.apply(this, {		
			plugins: [checkColumn],
			tbar: [
				{
					xtype:'buttongroup',
					defaults:{
						toggleHandler: (btn, state) => {
							if(state) {
								const filter = btn.itemId === 'users' ? {hideGroups: true} : (btn.itemId === 'groups' ? {hideUsers: true} : null);
								this.store.setFilter('hideUsers', filter).load();
							}
						},
						toggleGroup: 'groupType'
					},
					items: [
						{text:t('All'), itemId: 'both'},
						{text:t('Users'), itemId: 'users'},
						{text:t('Groups'), itemId: 'groups', pressed:true},
					]
				},
				'->',
				{
					xtype: 'tbsearch',
					filters: [
						'text'					
					]
				}
			],
			columns: [
				{
					id: 'name',
					header: t('Name'),
					sortable: false,
					dataIndex: 'name',
					menuDisabled: true,
					hideable: false,
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						
						var user = record.get("user"),
										style = user && user.avatarId ?  'background-image: url(' + go.Jmap.thumbUrl(record.get("user").avatarId, {w: 40, h: 40, zc: 1}) + ')"' : "background: linear-gradient(rgba(0, 0, 0, 0.38), rgba(0, 0, 0, 0.24));";
										html = user ? "" : '<i class="icon">group</i>';

							let memberStr = record.get('users').column('name').join(", ");
							var more = record.json._meta.users.total - store.fields.item('users').limit;
							if(more > 0) {
								memberStr += t(" and {count} more").replace('{count}', more);
							}					
						
						return '<div class="user"><div class="avatar" style="' + style + '">' + html + '</div>' +
							'<div class="wrap">'+
								'<div class="displayName">' + value + '</div>' +
								'<small class="username">' + Ext.util.Format.htmlEncode(memberStr) + '</small>' +
							'</div>'+
							'</div>';
					}
				},{
					id: 'level',
					header : t("Level"),
					dataIndex : 'level',
					menuDisabled:true,
					editor : levelCombo,
					width: dp(260),
					hidden: !this.showLevels,
					hideable: false,
					sortable: false,
					renderer:function(v, meta){
						if(!me.showLevels) {
							return "";
						}
						var r = levelCombo.store.getById(v);
						meta.style="position:relative";
						return r ? r.get('text') + "<i class='trigger'>arrow_drop_down</i></div>" : v;
					},
				},
				checkColumn,
				{
					sortable: false,
					width: dp(64),
					dataIndex: "id",
					renderer: function() {
						return '<a class="show-on-hover" title="' + Ext.util.Format.htmlEncode(t("View members")) + '"><i class="icon">people</i></a>';
					}
				}
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',		
				scrollOffset: 0		
			},
			autoExpandColumn: 'name',
			listeners: {
				scope: this,
				afteredit : this.afterEdit
			}
//			// config options for stateful behavior
//			stateful: true,
//			stateId: 'users-grid'
		});

		go.permissions.SharePanel.superclass.initComponent.call(this);

		this.on("cellclick", function(grid, rowIndex, columnIndex, e) {
			var record = grid.getStore().getAt(rowIndex);  // Get the Record
			var fieldName = grid.getColumnModel().getDataIndex(columnIndex); // Get field name
			if(fieldName == "id") {
				var win = new go.groups.GroupMemberWindow();
				win.load(record.data.id).show();
			}
			
		}, this);


		this.on("added", () => {

			setTimeout(() => {

				const form = this.findParentByType("entityform");
				if (form) {
					const onLoad = async () => {
						//orders selected values on top
						let entity;
						if(!this.aclIdProp) {
							const nameParts = this.name.split('.');

							if (nameParts.length == 3 && nameParts[2] == 'defaultAcl') {
								//for default permisssions
								entity = {default: true, entity: nameParts[1]};
							} else {
								entity = {id: form.currentId, entity: form.entityStore.entity.name};
								this.setDisabled(form.entity.permissionLevel < go.permissionLevels.manage);
							}
						} else
						{
							entity = go.util.Object.fetchPath(form.values, this.aclIdProp);
						}

						this.store.setFilter("inAcl", {inAcl: entity});

					};
					if(!form.entity) {
						form.on("load", onLoad);
					} else {
						onLoad();
					}
					if(!form.currentId) {
						this.value = form.entityStore.entity.defaultAcl;
					}
				}


			})
		});

	},
	
	startEditing : function(row,  col) {
		go.permissions.SharePanel.superclass.startEditing.call(this, row, col);
		
		//expand combo when editing
		if(this.activeEditor) {
			this.activeEditor.field.onTriggerClick();
		}
	},
	
	onCheckChange : function(record, newValue) {
		if(this.value == null) {
			this.value = {};
		}
		if(newValue) {
			record.set('level', this.addLevel);
			this.value[record.data.id] = record.data.level;
		} else
		{
			record.set('level', null);
			this.value[record.data.id] = null;
		}

		this._isDirty = true;
	},

	afterEdit : function(e) {
		this.value[e.record.id] = e.record.data.level;				
		this._isDirty = true;
	},
	
	createLevelCombo : function() {
		var levelData = [];

		this.levelLabels = this.levelLabels || {};
		
		if(!this.levelLabels[go.permissionLevels.read])
			this.levelLabels[go.permissionLevels.read] =t("Read only");
		if(!this.levelLabels[go.permissionLevels.create])
			this.levelLabels[go.permissionLevels.create] =t("Read and Create only");
		if(!this.levelLabels[go.permissionLevels.write])
			this.levelLabels[go.permissionLevels.write] =t("Write");
		if(!this.levelLabels[go.permissionLevels.writeAndDelete])
			this.levelLabels[go.permissionLevels.writeAndDelete] =t("Write and delete");
		if(!this.levelLabels[go.permissionLevels.manage])
			this.levelLabels[go.permissionLevels.manage] =t("Manage");
		
		if(!this.levels){
			this.levels=[
				go.permissionLevels.read,
				go.permissionLevels.create,
				go.permissionLevels.write,
				go.permissionLevels.writeAndDelete,
				go.permissionLevels.manage
			];
		}
		
		for(var i=0;i<this.levels.length;i++){			
			if(!this.levelLabels[this.levels[i]]){
				alert('Warning: you must define a label for permission level: '+this.levels[i]);
			}else
			{
				levelData.push([this.levels[i],this.levelLabels[this.levels[i]]]);
			}
		}

		this.levelStore = new Ext.data.SimpleStore({
			id:0,
			fields : ['value', 'text'],
			data : levelData
		});

		this.showLevel = (this.hideLevel) ? false : true;			

		var permissionLevelConfig ={
					store : this.levelStore,
					valueField : 'value',
					displayField : 'text',
					mode : 'local',
					triggerAction : 'all',
					editable : false,
					selectOnFocus : true,
					forceSelection : true
				};
				
		

		return new go.form.ComboBox(permissionLevelConfig);
	},
	
	afterRender : function() {

		go.permissions.SharePanel.superclass.afterRender.call(this);

		//Check form currentId because when form is loading then it will load the store on setValue later.
		//Set timeout is used to make sure the check will follow after a load call.

		setTimeout(() => {
				this.store.load();
		});
	},
	
	isFormField: true,

	getName: function() {
		return this.name;
	},

	_isDirty: false,

	isDirty: function () {
		return this._isDirty || this.store.getModifiedRecords().length > 0;
	},

	reset : function() {
		this.setValue(null);
		this.dirty = false;
	},

	setValue: function (groups) {
		this._isDirty = false;
		this.value = groups;
		if(this.rendered) {
			this.store.load();
		}
	},
	
	getSelectedGroupIds : function() {
		//return Object.keys(this.value).map(function(id) { return parseInt(id);});
		var groupIds = [];
		for(var id in this.value) {
			if(this.value[id]) {
				groupIds.push(parseInt(id));
			}
		}
		return groupIds;
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
	
	validate : function() {
		return true;
	},

	isValid: function(preventMark) {
		return true;
	}
});


