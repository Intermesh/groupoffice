go.modules.core.core.SharePanel = Ext.extend(go.grid.EditorGridPanel, {
	/*
	 * the form field name
	 */
	name: "groups",
	
	clicksToEdit: 1,
	initComponent: function () {
		
		this.selectedGroups = [];
		
		var checkColumn = new GO.grid.CheckColumn({
			width: dp(48),
			dataIndex: 'selected',
			listeners: {
				change: this.onCheckChange,
				scope: this
			},
			isDisabled : function(record) {
				return record.data.id === 1;
			}
		});
		
		var me = this;
		
		this.store = new go.data.Store({
			sortInfo: {
				field: 'name',
				direction: 'ASC'
			},
			fields: [
				'id', 
				'name', 
				{name: 'user', type: go.data.types.User, key: 'isUserGroupFor'}, //fetches entity from store
				{name: 'members', type: go.data.types.User, key: 'users.userId'},
				{
					name: 'level', 
					type: {
						convert: function (v, data) {
							var index = me.getSelectedGroupIds().indexOf(parseInt(data.id));
							if(index == -1) {
								return null;
							}
							
							return me.selectedGroups[index].level;
						}
					}
				},
				{
					name: 'selected', 
					type: {
						convert: function (v, data) {
							return me.getSelectedGroupIds().indexOf(data.id) > -1;
						}
					},
					sortType:function(checked) {
						return checked ? 1 : 0;
					}
				}
			],

			baseParams: {
			},
			entityStore: go.Stores.get("Group")
		});
		
		var levelCombo = this.createLevelCombo();

		Ext.apply(this, {		
			plugins: [checkColumn],
			tbar: [{
					xtype: 'tbtitle',
					text: t("Group Office users and groups")
			},
			'->', 
				{
					xtype: 'tbsearch'
				}				
			],
			columns: [
				{
					id: 'name',
					header: t('Name'),
					sortable: true,
					dataIndex: 'name',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						
						var user = record.get("user"),
										style = user && user.avatarId ?  'background-image: url(' + go.Jmap.downloadUrl(record.get("user").avatarId) + ')"' : "";
										cls = user ? "avatar" : "avatar group",										
										max = 5,
										members = record.get('members').slice(0, max).column('displayName');
						
							memberStr = members.join(", ");
								
							var more = members.length - max;
							if(more > 0) {
								memberStr += t(" and {count} more").replace('{count}', more);
							}
						
						
						return '<div class="user"><div class="' + cls + '" style="' + style + '"></div>' +
							'<div class="wrap">'+
								'<div class="displayName">' + record.get('name') + '</div>' +
								'<small class="username">' + memberStr + '</small>' +
							'</div>'+
							'</div>';
					}
				},{
					id: 'level',
					header : t("Level"),
					dataIndex : 'level',
					menuDisabled:true,
					editor : levelCombo,
					width: dp(160),
					renderer:function(v, meta){
						var r = levelCombo.store.getById(v);
						meta.style="position:relative";
						return r ? r.get('text') + "<i class='trigger'>arrow_drop_down</i></div>" : v;
					},
					sortable: true
				},
				checkColumn
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',				
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
		
		this.store.on("beforeload", this.onBeforeStoreLoad, this);
		
		go.modules.core.core.SharePanel.superclass.initComponent.call(this);
		
		this.on("beforeedit", function(e) {
			return e.record.data.id !== 1; //cancel edit for admins group
		}, this);

	},
	
	startEditing : function(row,  col) {
		go.modules.core.core.SharePanel.superclass.startEditing.call(this, row, col);
		
		//expand combo when editing
		if(this.activeEditor) {
			this.activeEditor.field.onTriggerClick();
		}
	},
	
	onCheckChange : function(record, newValue) {
		if(newValue) {			
			record.set('level', this.addLevel);
			this.selectedGroups.push({
				groupId: record.data.id,
				level: record.data.level
			});		
		} else
		{
			record.set('level', null);
			this.selectedGroups.splice(this.getSelectedGroupIds().indexOf(record.id), 1);
		}
		
		this._isDirty = true;
	},
	afterEdit : function(e) {
		
		var index = this.getSelectedGroupIds().indexOf(e.record.id);							
		
		if(index == -1) {
			this.selectedGroups.push({
				groupId: e.record.data.id,
				level: e.record.data.level
			});			
			e.record.set('selected', true);
		} else
		{
			this.selectedGroups[index].level = e.record.data.level;
		}
		
		this._isDirty = true;
	},
	
	createLevelCombo : function() {
		var levelData = [];

		this.levelLabels = this.levelLabels || {};
		
		if(!this.levelLabels[GO.permissionLevels.read])
			this.levelLabels[GO.permissionLevels.read] =t("Read only");
		if(!this.levelLabels[GO.permissionLevels.create])
			this.levelLabels[GO.permissionLevels.create] =t("Read and Create only");
		if(!this.levelLabels[GO.permissionLevels.write])
			this.levelLabels[GO.permissionLevels.write] =t("Write");
		if(!this.levelLabels[GO.permissionLevels.writeAndDelete])
			this.levelLabels[GO.permissionLevels.writeAndDelete] =t("Write and delete");
		if(!this.levelLabels[GO.permissionLevels.manage])
			this.levelLabels[GO.permissionLevels.manage] =t("Manage");
		
		if(!this.levels){
			this.levels=[
				GO.permissionLevels.read,
				GO.permissionLevels.create,
				GO.permissionLevels.write,
				GO.permissionLevels.writeAndDelete,
				GO.permissionLevels.manage
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
		

		this.showLevel = (this.hideLevel) ? false : true;			

		var permissionLevelConfig ={
					store : new Ext.data.SimpleStore({
						id:0,
						fields : ['value', 'text'],
						data : levelData
					}),
					valueField : 'value',
					displayField : 'text',
					mode : 'local',
					triggerAction : 'all',
					editable : false,
					selectOnFocus : true,
					forceSelection : true
				};
				
		
		if(!this.addLevel)
			this.addLevel = GO.permissionLevels.read;
		
		return new go.form.ComboBox(permissionLevelConfig);
	},
	
	
	
	isFormField: true,

	getName: function() {
		return this.name;
	},

	_isDirty: false,

	isDirty: function () {
		return this._isDirty || this.store.getModifiedRecords().length > 0;
	},

	setValue: function (groups) {		
		this._isDirty = false;		
		this.selectedGroups = groups;
		this.store.load();
	},
	
	getSelectedGroupIds : function() {
		return this.selectedGroups.column("groupId");
	},
	
	onBeforeStoreLoad : function(store, options) {

		//don't add selected on search
		if(store.baseParams.filter.q || options.selectedLoaded || options.paging) {
			return true;
		}
		
		go.Stores.get("Group").get(this.getSelectedGroupIds(), function(entities) {
			this.store.loadData({records: entities}, true);
			this.store.sortData();
			this.store.load({
				add: true,
				selectedLoaded: true,
				params: {filter: {exclude: this.getSelectedGroupIds()}}
			});
		}, this);
		
		return false;
	},	
	
	getValue: function () {				
		return this.selectedGroups;
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
	}
});


