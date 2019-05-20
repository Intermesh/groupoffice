/* global go, Ext */

go.customfields.type.TreeSelectField = Ext.extend(Ext.Container, {
	layout: "anchor",
	isFormField: true,
	name: "options",
	findBy: false,
	customfield: null,
	//height: dp(36),
	getName : function() {
		return this.name;
	},
	initComponent: function () {	
		go.customfields.type.TreeSelectField.superclass.initComponent.call(this);		
		this.add(this.createCombo(this.customfield.dataType.options));
		
		this.pathMap = {};
		this.buildPathMap(this.customfield.dataType.options);
	},
	
	pathMap : null,
	
	buildPathMap : function(options, path) {
		var me = this, nextPath;
		
		path = path || [];
		
		options.forEach(function(o) {
			nextPath = path.concat(o.id);
			me.pathMap[o.id] = nextPath;			
			me.buildPathMap(o.children, nextPath);
		});
	},
	
	

	createCombo: function (options) {		
		
		var store = new Ext.data.JsonStore({
			data: {root: options},
			id: 'id',
			root: "root",
			fields: [{name: 'id', type: "int"}, 'text'],
			remoteSort: true
		});

		return {
			submit: false,
			anchor: "100%",
			xtype: 'combo',
			store: store,
			valueField: 'id',
			displayField: 'text',
			mode: 'local',
			triggerAction: 'all',
			editable: true,
			selectOnFocus: true,
			forceSelection: true,
			hideLabel: true,
			listeners: {
				scope: this,
				select: this.onSelect
			}
		};
	},
	
	reset:function() {
		this.items.each(function(f){
			f.reset();
	  });
	  return this;
	},

	onSelect : function(field, record) {
		
		var index = this.items.indexOf(field) + 1;
		for(var i = index; i < this.items.getCount(); i++) {
			this.remove(this.items.item(i));
		}
		
		if(!go.util.empty(record.json.children)) {			
			this.add(this.createCombo(record.json.children));
		}
		
		this.doLayout();
	},
	
	isDirty: function () {
		return true;
	},

	
	setValue: function (optionId) {
		if(!this.pathMap[optionId]) {
			return;
		}
		
		var path = this.pathMap[optionId], field;
		
		for(var i = 0, l = path.length; i < l; i++) {
			field = this.items.itemAt(i);
			field.setValue(path[i]);
			var record = field.store.getById(path[i]);
		
			this.onSelect(field, record);
		}		
	},

	getValue: function () {
		if(!this.items || !this.items.length) {
			return null;
		}
		var v = this.items.last().getValue();
		
		if(!v && this.items.getCount() > 1){
			v = this.items.itemAt(this.items.getCount() -2).getValue();
		}
		
		return go.util.empty(v) ? null : v;
	},
	markInvalid: function () {

	},
	clearInvalid: function () {

	},

	validate: function () {
		return true;
	}
});


Ext.reg("treeselectfield", go.customfields.type.TreeSelectField);