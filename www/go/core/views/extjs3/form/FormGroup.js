/* global Ext */

/**
 * 
 * new go.form.FormGroup({
 *	name: "dataType.options",
 *	addButtonText: t("Add option"),
 *	addButtonIconCls: 'ic-add',
 *	itemCfg: {
 *		items: [{
 *				xtype: "hidden",
 *				name: "id"
 *			}, {
 *				hideLabel: true,
 *				xtype: "textfield",
 *				name: "text",
 *				anchor: "100%",
 *				setFocus: true //this will focus this field when a new item has been added
 *			}]
 *	}
 *})
 */
go.form.FormGroup = Ext.extend(Ext.Panel, {
	isFormField: true,
	
	cls: "go-form-group",
	
	// Set to true to add padding between rows
	pad: false,
	
	dirty: false,
	
	hideLabel: true,

	hideBbar: false,
	
	addButtonText: null, // deprecated, use btnCfg
	btnCfg: null, // @type Ext.Button
	editable: true, // show delete and add buttons when true
	layout: "form",
	// @string name of property, when set getValue will build an object map with this property as key
	mapKey: null,
	// When mapKey is set we remember the keys of properties that are going to be deleted here
	markDeleted: [],
	
	defaults: {
		anchor: "100%"
	},
	
	initComponent : function() {		
		
		//No longer needed when cancelling add event.
//		//to prevent items to be cascaded by Extjs basic form
//		this.itemCfg.findBy = false;
//		
//		//to prevent adding to the ExtJS basic form
//		this.itemCfg.isFormField = false;
		this.markDeleted = [];
		this.itemCfg.columnWidth = 1;
		
		if(!this.itemCfg.xtype) {
			this.itemCfg.xtype = "formcontainer";
		}

		this.btnCfg = this.btnCfg || {};

		if(this.editable && !this.hideBbar) {
			this.initBbar();
		}

		this.on("add",function(e) {
			//to prevent adding to Ext.form.BasicForm with add event.
			//Cancels event bubbling
			return false;
		});
		
		go.form.FormGroup.superclass.initComponent.call(this);
	},
	
	initBbar: function() {
		this.bbar = [
			Ext.apply(this.btnCfg,{
				//iconCls: this.addButtonIconCls,
				text: this.addButtonText || this.btnCfg.text || t("Add"),
				handler: function() {
					var wrap = this.addPanel();
					this.doLayout();
					
					wrap.formField.focus();
				
				},
				scope: this
			})
		];
	},
	
//	focusNewField : function(wrap) {
//		var item;
//		for(var i = 0, l = wrap.items.getCount();i < l;i++) {
//			item = wrap.items.get(i);
//			
//			if(item.setFocus) {				
//				item.getEl().focus();
//				return true;
//			}
//			
//			if(item.items && this.focusNewField(item)) {
//				return true;
//			}
//		}
//		return false;
//	},
	
	createNewItem : function() {
		var item = Ext.ComponentMgr.create(this.itemCfg);
		
		if(!item.getValue || !item.setValue) {
			throw "Form Group item must be a form field";
		}
		
		return item;
	},
	
	each : function(fn, scope){
		var items = [].concat(this.items.items); // each safe for removal
		for(var i = 0, len = items.length; i < len; i++){
			if(fn.call(scope || items[i].formField, items[i].formField, i, len) === false){
				break;
			}
		}
	},
	
	addPanel : function() {
		var formField = this.createNewItem(), me = this, items = [formField], delBtn = new Ext.Button({				
			//disabled: formField.disabled,
			xtype: "button",
			iconCls: 'ic-delete',
			handler: function() {
				if(this.ownerCt.ownerCt.formField.key) {
					me.markDeleted.push(this.ownerCt.ownerCt.formField.key);
				}
				this.ownerCt.ownerCt.destroy();
				me.dirty = true;
			}
		});

		if(this.editable) {
			items.unshift({
				xtype: "container",
				width: dp(48),		
				items: [delBtn]
			});	
		}
		var wrap = new Ext.Container({
			layout: "column",
			formField: formField,			
			findBy: false,
			isFormField: false,
			style: this.pad ?  "padding-top: " + dp(16) + "px" : "",
			items: items
		});
		this.add(wrap);
		return wrap;
	},

	getName: function() {
		return this.name;
	},

	
	isDirty: function () {
		if(this.dirty) {
			return true;
		}
		
		var dirty = false;
		this.items.each(function(wrap) {
			if(wrap.formField.isDirty()) {
				dirty = true;
				//stops iteration
				return false;
			}
		}, this);
		
		return dirty;
	},
	
	reset : function() {
		this.setValue([]);
		this.dirty = false;
	},

	setValue: function (records) {	
		this.dirty = true;
		this.removeAll();
		this.markDeleted = [];
		var me = this, wrap;
		function set(r) {
			wrap = me.addPanel();
			wrap.formField.key = r.id;
			wrap.formField.setValue(r);
		}
		if(this.mapKey) {
			for(var r in records) {
				set(records[r]);
			}
		} else {
			records.forEach(set);
		}
		
		this.doLayout();
	},
	

	getValue: function () {
		var v = this.mapKey ? {} : [];
		if(!this.items) {
			return v;
		}
		this.items.each(function(wrap) {
			if(this.mapKey) {
				// TODO make minimal PatchObject
				//if(wrap.formField.isDirty()) {
					v[wrap.formField.key || Ext.id()] = wrap.formField.getValue();
				//}
			} else {
				v.push(wrap.formField.getValue());
			}
		}, this);
		if(this.mapKey) {
			this.markDeleted.forEach(function(key) { v[key] = null; });
		}
		
		return v;
	},

	markInvalid: function (msg) {
		var f = this.getAllFormFields();
		for(var i = 0, l = f.length; i < l; i++) {
			f[i].markInvalid(msg);			
		}
	},
	
	clearInvalid: function () {
		var f = this.getAllFormFields();
		for(var i = 0, l = f.length; i < l; i++) {
			f[i].clearInvalid();			
		}
	},

	validate: function () {
		var f = this.getAllFormFields();
		for(var i = 0, l = f.length; i < l; i++) {
			if(!f[i].validate()) {
				return false;
			}
		}
		return true;
	},
	
	getAllFormFields : function(c) {
		var fields = [];
		
		if(!c) {
			c = this;
		}
		
		if(c.items) {
			c.items.each(function(i) {				
				fields.push(i.formField);									
			}, this);
		}
		
		return fields;
	}
});

Ext.reg('formgroup', go.form.FormGroup);
