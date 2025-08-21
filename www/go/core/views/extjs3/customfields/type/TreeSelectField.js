/* global go, Ext */

go.customfields.type.TreeSelectField = Ext.extend(Ext.Container, {
	layout: "anchor",
	isFormField: true,
	name: "options",
	findBy: false,
	customfield: null,
	allowBlank: true,
	collapseOnSelect: true,
	//height: dp(36),
	getName : function() {
		return this.name;
	},
	initComponent: function () {	
		go.customfields.type.TreeSelectField.superclass.initComponent.call(this);

		this.addEvents('select');

		const options = structuredClone(this.customfield.dataType.options);

		options.unshift({
			id: null, fieldId: this.customfield.id, text: "--", options: [], parentId: null, enabled: true
		});

		first = this.createCombo(options);
		first.allowBlank = this.allowBlank;
		first.conditionallyHidden = this.conditionallyHidden;
		first.conditionallyRequired = this.conditionallyRequired;

		this.add(first);
		
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
			fields: [{name: 'id', type: "int"}, 'text', 'enabled'],
			remoteSort: true
		});

		return {
			//Override so that disabled values are in the store but may not be selected.
			doQuery : function(q, forceAll){
				q = Ext.isEmpty(q) ? '' : q;

				if(forceAll === true || (q.length >= this.minChars)){
					if(this.lastQuery !== q){
						this.lastQuery = q;

							this.selectedIndex = -1;
							if(forceAll){
								this.store.filter('enabled', true);
							}else{

								this.store.filter([{property: 'enabled', value: true}, {property: this.displayField, anyMatch: true, value: q}]);
							}
							this.onLoad();
					}else{
						this.selectedIndex = -1;
						this.onLoad();
					}
				}
			},
			minChars: 1,
			submit: false,
			anchor: "100%",
			xtype: 'gocombo',
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
				select: this.onSelect,
				beforeselect: this.onBeforeSelect,
				change: this.onChange,
				setvalue: () => {
					this.fireEvent('change', this, this.getValue());
				} // to hide conditional fields on select (also when in lazy render tab)
			}
		};
	},

	_isDirty: false,

	onChange: function() {
		this._isDirty = true;
		this.fireEvent('change', this, this.getValue());
	},
	
	reset:function() {
		this._isDirty = false;
		this.items.each(function(f){
			f.reset();
	  });
	  return this;
	},

	onBeforeSelect : function(field, record) {
		if(go.util.empty(record.json.children) && !this.collapseOnSelect) {
			field.collapseOnSelect = false;
		}
	},
	onSelect : function(field, record) {
		var index = this.items.indexOf(field), nextIndex = index + 1;

		if(this.items.getCount() > nextIndex) {
			var remove = this.items.getRange(nextIndex);
			remove.forEach(function(r) {
				r.destroy();
			});
		}
		
		if(!go.util.empty(record.json.children)) {			
			this.add(this.createCombo(record.json.children));
		} else {
			this.fireEvent('select', this, record);
		}
		this.doLayout();
	},
	
	isDirty: function () {
		return this._isDirty;
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
			if(!record) {
				console.error("Record not found for " + path[i], field);
				return;
			}
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

	getRawValue : function() {
		if(!this.items || !this.items.length) {
			return null;
		}
		var v = this.items.last().getRawValue();

		if(!v && this.items.getCount() > 1){
			v = this.items.itemAt(this.items.getCount() -2).getRawValue();
		}

		return go.util.empty(v) ? null : v;
	},

	markInvalid: function (msg) {
		this.items.each(function(i) {			
			i.markInvalid(msg);
		});

		this.fireEvent('invalid', this, msg);
	},
	clearInvalid: function () {
		this.items.each(function(i) {
			i.clearInvalid();
		});

		this.fireEvent('valid', this);
	},

	isValid : function(preventMark){
		if(this.disabled){
			return true;
		}
		var f = this.items.items;
		for(var i = 0, l = f.length; i < l; i++) {
			if(f[i].isFormField && !f[i].isValid(preventMark)) {
				return false;
			}
		}
		return true;
	},

	validate: function () {
		var valid = true, fn = function (i) {
			if (i.isFormField && !i.validate()) {
				valid = false;
				//stops iteration
				return false;
			}
		};
		this.items.each(fn, this);

		valid ? this.fireEvent('valid', this) : this.fireEvent('invalid', this, null);

		return valid;
	},

	isModified: function() {
		return this.isDirty()
	},


	trackReset: function() {
		this._isDirty = false;
	}
});


Ext.reg("treeselectfield", go.customfields.type.TreeSelectField);