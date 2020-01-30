go.groups.GroupModuleGrid = Ext.extend(go.grid.EditorGridPanel, {
	/*
	 * the form field name
	 */
	name: "modules",
	
	cls: "go-group-module-grid", 
	
	clicksToEdit: 1,
	
	showLevels: true,

  title: t("Modules"),
  
  scrollLoader: false,
	
	initComponent: function () {
		
		if(!this.value) {
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
            convert: function(v, data) {
              var localized = t('name', data.name, data.package, true);
              if(localized == 'name') {
                return data.name;
              } else{
                return localized;
              }
            }
          },
          sortType: Ext.data.SortTypes.asText
        },
        'package',         
				{
					name: 'level', 
					type: {
						convert: function (v, data) {							
							return me.value[data.id];
						}
					}
				},
				{
					name: 'selected', 
					type: {
						convert: function (v, data) {
							return !!me.value[data.id];
						}
					},
					sortType:function(checked) {
						return checked ? 1 : 0;
					}
				}
			],

			entityStore: "Module"
		});
		
		var levelCombo = this.createLevelCombo();

		Ext.apply(this, {		
			plugins: [checkColumn],
			tbar: [
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
					id: 'label',
					header: t('Name'),
					sortable: false,
					dataIndex: 'label',
					menuDisabled: true,
          hideable: false,
          renderer: function(name, cell, record) {
            return '<div class="mo-title" style="background-image:url(' + go.Jmap.downloadUrl('core/moduleIcon/'+(record.data.package || "legacy")+'/'+record.data.name)+ ')">' + name +'</div>';
          }
          
				},{
					id: 'package',
					header: t('Package'),
					sortable: false,
					dataIndex: 'package',
					menuDisabled: true,
					hideable: false
				},{
					id: 'level',
					header : t("Level"),
					dataIndex : 'level',
					menuDisabled:true,
					editor : levelCombo,
					width: dp(260),
					hidden: !this.showLevels,
					hideable: false,
					renderer:function(v, meta){
						if(!me.showLevels) {
							return "";
						}
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
				scrollOffset: 0		
			},
			autoExpandColumn: 'label',
			listeners: {
				scope: this,
				afteredit : this.afterEdit
			}
//			// config options for stateful behavior
//			stateful: true,
//			stateId: 'users-grid'
		});
		
		this.store.on("beforeload", this.onBeforeStoreLoad, this);
		
		go.groups.GroupModuleGrid.superclass.initComponent.call(this);
		
		this.on("beforeedit", function(e) {
			return e.record.data.id !== 1; //cancel edit for admins group
		}, this);

	},
	
	startEditing : function(row,  col) {
		go.groups.GroupModuleGrid.superclass.startEditing.call(this, row, col);
		
		//expand combo when editing
		if(this.activeEditor) {
			this.activeEditor.field.onTriggerClick();
		}
	},
	
	onCheckChange : function(record, newValue) {
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
			this.levelLabels[go.permissionLevels.read] =t("Use");
		if(!this.levelLabels[go.permissionLevels.manage])
			this.levelLabels[go.permissionLevels.manage] =t("Manage");
		
		if(!this.levels){
			this.levels=[
				go.permissionLevels.read,			
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
			this.addLevel = go.permissionLevels.read;
		
		return new go.form.ComboBox(permissionLevelConfig);
	},
	
	afterRender : function() {

		go.groups.GroupModuleGrid.superclass.afterRender.call(this);

		var form = this.findParentByType("entityform");

		if(!form) {
			return;
		}

		if(!this.store.loaded) {
			this.store.load();
		}		

		form.on("load", function(f, v) {
			this.setDisabled(v.permissionLevel < go.permissionLevels.manage);
		}, this);

		//Check form currentId becuase when form is loading then it will load the store on setValue later.
		//Set timeout is used to make sure the check will follow after a load call.
		var me = this;
		setTimeout(function() {
			if(!go.util.empty(me.value) && !form.currentId) {				
				me.store.load();
			}
		}, 0);		
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
		this.setValue([]);
		this.dirty = false;
	},

	setValue: function (groups) {		
		this._isDirty = false;		
		this.value = groups || {};
		this.store.load().catch(function(){}); //ignore failed load becuase onBeforeStoreLoad can return false
	},
	
	getSelectedModuleIds : function() {
		return Object.keys(this.value).map(function(id) { return parseInt(id);});
	},
	
	onBeforeStoreLoad : function(store, options) {

		//don't add selected on search
		if(this.store.filters.tbsearch || options.selectedLoaded || options.paging) {
			this.store.setFilter('exclude', null);
			return true;
		}
		
		go.Db.store("Module").get(this.getSelectedModuleIds(), function(entities) {
			this.store.loadData({records: entities}, true);
			// this.store.sortData();
			this.store.setFilter('exclude', {
				exclude: this.getSelectedModuleIds()
			});
			var me = this;
			this.store.load({
				add: true,
				selectedLoaded: true
			}).then(function() {
        me.store.multiSort([{
          field: 'selected',
          direction: 'DESC'
        },{
          field: 'label',
          direction: 'ASC'
        }]);
      });
		}, this);
		
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
	
	validate : function() {
		return true;
	}
});


