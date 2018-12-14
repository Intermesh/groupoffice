/* global Ext */

/**
 * 
 * new go.form.FormGroup({
 *	name: "dataType.options",
 *	fieldLabel: t("Options"),
 *	itemCfg: {
 *		layout: "form",
 *		items: [{
 *				xtype: "hidden",
 *				name: "id"
 *			}, {
 *				hideLabel: true,
 *				xtype: "textfield",
 *				name: "text",
 *				anchor: "100%"
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
	
	initComponent : function() {		
		
		
		//to prevent items to be cascaded by Extjs basic form
		this.itemCfg.findBy = false;
		
		//to prevent adding to the ExtJS basic form
		this.itemCfg.isFormField = false;
		
		this.itemCfg.flex = 1;
		
		if(!this.itemCfg.xtype) {
			this.itemCfg.xtype = "container";
		}
		
		
		this.initBbar();
		
		go.form.FormGroup.superclass.initComponent.call(this);
	},
	
	initBbar: function() {
		this.bbar = [
				'->',
			{
				iconCls: 'ic-add',
				handler: function() {
					this.addPanel();
					this.doLayout();
				},
				scope: this
			}
		];
	},
	
	setPanelValue : function(panel, v) {
		panel.items.each(function(i) {
			if(!i.isFormField) {				
				return;
			} 
						
			var name = i.getName();
			
			if(v[name]) {
				i.setValue(v[name]);
			} else
			{				
				//if this is a container and not a form field then descent into the component to find fields.
				if(i.items) {
					this.setPanelValue(i, v);
				}
			}
		}, this);
	},
	
	createNewItemPanel : function() {
		return Ext.ComponentMgr.create(this.itemCfg);
	},
	
	addPanel : function(v) {
		var panel = this.createNewItemPanel(), me = this, wrap = {
			xtype: "container",
			layout: "hbox",
			formPanel: panel,
			findBy: false,
			isFormField: false,
			items: [
				panel,
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
				}
			]
		};
		
		if(this.pad) {
			wrap.style = "padding-top: " + dp(16) + "px";
		}
		
		if(v) {
			this.setPanelValue(panel, v);
		}
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
		this.items.each(function(i) {
			if(this.panelIsDirty(i.formPanel)) {
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
		
		var me = this;
		records.forEach(function(r) {						
			me.addPanel(r);
		});		
		
		this.doLayout();
	},
	

	getValue: function () {
		var v = [];
		if(!this.items) {
			return v;
		}
		this.items.each(function(i) {
			v.push(this.getPanelValue(i.formPanel));
		}, this);
		
		return v;
	},
	
	panelIsDirty : function(panel) {
		var dirty = false;
		panel.items.each(function(i) {
			if(!i.isFormField) {
				return true;
			}
			
			if(i.getXType() == 'compositefield') {				
				if(this.panelIsDirty(i)) {
					dirty = true;
					return false;
				}
			} else if(i.isDirty())
			{			
				dirty = true;
				return false;
			}
		}, this);
		
		return dirty;
	},
	
	getPanelValue : function(panel, v) {
		v = v || {};
		panel.items.each(function(i) {
			if(!i.isFormField) {
				return true;
			}
			
			if(i.getXType() == 'compositefield') {				
				this.getPanelValue(i, v);				
			} else
			{			
				var name = i.getName();
				v[name] = i.getValue();
				if(Ext.isDate(v[name])) {
					v[name] = v[name].serialize();
				}
			}
		}, this);
		
		return v;
	},

	markInvalid: function () {

	},
	clearInvalid: function () {

	},

	validate: function () {
		return true;
	}
});

Ext.reg('formgroup', go.form.FormGroup);
