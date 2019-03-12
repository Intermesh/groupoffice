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
	
	addButtonText: t("Add"),
	addButtonIconCls: "ic-add",
	
	layout: "form",
	
	defaults: {
		anchor: "100%"
	},
	
	initComponent : function() {		
		
		
		//to prevent items to be cascaded by Extjs basic form
		this.itemCfg.findBy = false;
		
		//to prevent adding to the ExtJS basic form
		this.itemCfg.isFormField = false;
		
		this.itemCfg.flex = 1;
		
		if(!this.itemCfg.xtype) {
			this.itemCfg.xtype = "formcontainer";
		}		
		
		this.initBbar();
		
		go.form.FormGroup.superclass.initComponent.call(this);
	},
	
	initBbar: function() {
		this.bbar = [
			{
				//iconCls: this.addButtonIconCls,
				text: this.addButtonText,
				handler: function() {
					var wrap = this.addPanel();
					this.doLayout();
					
					wrap.formField.focus();
				
				},
				scope: this
			}
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
			throw new "Form Group item must be a form field";
		}
		
		return item;
	},
	
	addPanel : function() {
		var formField = this.createNewItem(), me = this, wrap = new Ext.Container({
			xtype: "container",
			layout: "hbox",
			formField: formField,			
			findBy: false,
			isFormField: false,
			style: this.pad ?  "padding-top: " + dp(16) + "px" : "",
			items: [				
				{
					xtype: "container",
					width: dp(48),					
					items: [{				
						xtype: "button",
						iconCls: 'ic-delete',
						handler: function() {
							this.ownerCt.ownerCt.destroy();
							me.dirty = true;
						}
					}]
				},
				formField
			]
		});
		return this.add(wrap);		
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
		this.removeAll();
		
		var me = this, wrap;
		records.forEach(function(r) {						
			wrap = me.addPanel();
			wrap.formField.setValue(r);
		});		
		
		this.doLayout();
	},
	

	getValue: function () {
		var v = [];
		if(!this.items) {
			return v;
		}
		this.items.each(function(wrap) {
			v.push(wrap.formField.getValue());
		}, this);
		
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
				if(i.isFormField) {
					fields.push(i);
				}					
			}, this);
		}
		
		return fields;
	}
});

Ext.reg('formgroup', go.form.FormGroup);
