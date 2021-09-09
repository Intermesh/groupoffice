/* global Ext */


/**
 * 
 * new go.form.FormGroup({
 *	name: "dataType.options",
 *	addButtonText: t("Add option"),
 *	addButtonIconCls: 'ic-add',
 *  listeners: {
								scope: this,
								newitem: function(fg, item) {
									//example of how to set value on new form group item. Where formFiel is of type "gocontainer".
									item.formField.findField("text").setValue(this.formPanel.entity.name);
								}
							},
 *	itemCfg: {
 *		listeners: {
 *		  setvalue: function(formContainer, v) {
 *		  	//example how to alter fields when loading data
 *		  	formContainer.findField('text').doSomething(v);
 *		  }
 *		},
 *		items: [{
 *				xtype: "hidden",
 *				name: "id"
 *			}, {
 *				hideLabel: true,
 *				xtype: "textfield",
 *				name: "text",
 *				anchor: "100%",
 *				setFocus: true //this will focus this field when a new item has been added
 *			},{
 *			 xtype: "button",
 *			 handler: function(btn) {
 *			 //find row index and form
 *			   var index = btn.findParentByType("formgroupitemcontainer").rowIndex,
									 form = btn.findParentByType("entityform");
 *			 }
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

	startWithItem: true,

	/**
	 * Enable sorting by drag and drop
	 */
	sortable: false,
	// /**
	//  * If set then this property will be set with the sort order ASC
	//  */
	// sortColumn: null,
	
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

	afterRender: function() {
		go.form.FormGroup.superclass.afterRender.call(this);
		if(this.sortable) {
			this.initSortable();
		}
		if(this.startWithItem && this.items.getCount() == 0) {
			this.addPanel(true);
		}
	},

	initSortable : function() {
		var me = this;
		this.dropZone = new Ext.dd.DropZone(this.getEl(), {
			ddGroup: "form-group-sortable-" + this.getId(),
			getTargetFromEvent: function(e) {
				return e.getTarget('.go-form-group-row');
			},
			onNodeEnter: function(target,dd,e,data) {
				Ext.fly(target).addClass('x-dd-over');
			},
			onNodeOut: function(target,dd,e,data) {
				Ext.fly(target).removeClass('x-dd-over');
			},
			onNodeOver: function (target, dd, e, data) {
				if(e.altKey) {
					return "x-dd-drop-ok-add";
				}
				return Ext.dd.DropZone.prototype.dropAllowed;
			},
			onNodeDrop: function (target, dd, e, data) {
				// var dropRow = Ext.getCmp(target.id);
				// var newItems = me.getValue();
				// if(Ext.isObject(newItems)) {
				// 	newItems = Object.values(newItems);
				// }
				// var dragItem = newItems[data.rowIndex];
				// if(!e.altKey) {
				// 	newItems.splice(data.rowIndex, 1);
				// 	if (dropRow.rowIndex > data.dragIndex) {
				// 		dropRow.rowIndex--;
				// 	}
				// }
				// newItems.splice(dropRow.rowIndex, 0, dragItem);
				// me.setValue(newItems);


				var dropRow = Ext.getCmp(target.id);
				var dragItem = me.items.itemAt(data.rowIndex);

				var v = dragItem.formField.getValue();

				if(!e.altKey) {
					me.remove(dragItem, true);
					if (dropRow.rowIndex > data.dragIndex) {
						dropRow.rowIndex--;
					}
				} else {
					//todo id configurable?
					delete v.id;
				}

				var p = me.addPanel(false, dropRow.rowIndex);
				p.formField.setValue(v);

				// Trigger an update upon saving
				me.dirty = true;

				me.items.each(function(i, rowIndex) {
					i.rowIndex = rowIndex;
				});

				me.doLayout();

				return true;
			}
		});

		this.on("destroy", function() {
			this.dropZone.destroy();
		}, this);
	},
	
	initBbar: function() {

		if(this.bbar) {
			return;
		}

		this.bbar = [
			Ext.apply(this.btnCfg,{
				//iconCls: this.addButtonIconCls,
				text: this.addButtonText || this.btnCfg.text || t("Add"),
				handler: function() {
					this.addRow();
				
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

	addRow : function(auto) {
		var wrap = this.addPanel(auto);
		this.doLayout();

		wrap.formField.focus();

		this.fireEvent("newitem", this, wrap);
	},
	
	createNewItem : function(auto) {
		var item = Ext.ComponentMgr.create(this.itemCfg);
		
		if(!item.getValue || !item.setValue) {
			throw "Form Group item must be a form field";
		}

		item.auto = auto;


		
		return item;
	},

	checkForNewRow : function(focusCatcher, e) {

		const c = focusCatcher.findParentByType("formgroupitemcontainer");

		if(c.rowIndex == this.items.length - 1 && c.formField.isDirty()) {

			this.addRow(true);
		} else
		{
			c.items.get('del-btn').focus();
		}

	},

	each : function(fn, scope){
		var items = [].concat(this.items.items); // each safe for removal
		for(var i = 0, len = items.length; i < len; i++){
			if(fn.call(scope || items[i].formField, items[i].formField, i, len) === false){
				break;
			}
		}
	},
	
	addPanel : function(auto, index) {
		var formField = this.createNewItem(auto), me = this, items = [formField], delBtn = new Ext.Button({
			//disabled: formField.disabled,
			itemId: 'del-btn',
			xtype: "button",
			cls: "small",
			iconCls: 'ic-delete',
			handler: function() {
				if(this.ownerCt.formField.key) {
					me.markDeleted.push(this.ownerCt.formField.key);
				}
				this.ownerCt.destroy();
				me.dirty = true;
			}
		}),
			rowId  = Ext.id();

		if(this.startWithItem) {
			const focusCatch = new Ext.form.TextField({
				submit: false,
				width: 0,
				height: 0,
				style: "padding: 0; border:0"
			})

			focusCatch.on('focus', this.checkForNewRow, this);
			items.push(focusCatch);
		}

		if(this.editable) {
			items.push(delBtn);

			if(this.sortable) {
				var dragHandle = this.createDragHandle(rowId);
				items.push(dragHandle);
			}
		}

		var wrap = new go.form.FormGroupItemContainer({
			id: rowId,
			rowIndex: this.items ? this.items.getCount() : 0,

			formField: formField,
			style: this.pad ?  "padding-top: " + dp(16) + "px" : "",
			items: items
		});

		if(index == undefined) {
			this.add(wrap);
		} else
		{
			this.insert(index, wrap);
		}

		return wrap;
	},

	createDragHandle : function(rowId) {
		return new Ext.Button({
			iconCls: "ic-drag-handle",
			cls: "small",
			tooltip: t("Drag to sort"),
			rowId: rowId,
			tabIndex: -1,
			listeners: {
				scope: this,
				destroy: function(cmp) {

					setTimeout(function()
					{
						if(cmp.dragZone) {
							cmp.dragZone.destroy();
						}
					});
				},
				afterrender: function(cmp) {

					cmp.dragZone = new Ext.dd.DragZone(cmp.getEl(), {
						ddGroup: this.dropZone.ddGroup,
						getDragData: function(e) {
							var row = Ext.getCmp(cmp.rowId);
							var sourceEl = row.getEl().dom;
							if (sourceEl) {
								d = sourceEl.cloneNode(true);
								d.id = Ext.id();
								return {
									sourceEl: sourceEl,
									repairXY: Ext.fly(sourceEl).getXY(),
									ddel: d,
									rowIndex: row.rowIndex
								}
							}
						},
						getRepairXY: function() {
							return this.dragData.repairXY;
						}
					});
				}
			}
		});
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
		if(records === null) return;
		this.markDeleted = [];
		var me = this, wrap;
		function set(r, key) {
			wrap = me.addPanel();
			wrap.formField.key = key;
			wrap.formField.setValue(r);
		}
		if(this.mapKey) {
			for(var r in records) {
				set(records[r]);
			}
		} else {
			records.forEach(set);
		}

		if(this.startWithItem) {
			this.addPanel(true);
		}

		this.doLayout();
	},
	

	getValue: function () {
		var v = this.mapKey ? {} : [];
		if(!this.items) {// || (this.items.getCount() == 1 && this.items.get(0).formField.auto && !this.items.get(0).formField.isDirty())) {
			return v;
		}

		this.items.each(function(wrap, index) {

			var item = wrap.formField;
			// if(this.sortColumn) {
			// 	item[this.sortColumn] = index;
			// }

			if(index == this.items.length -1 && item.auto && !item.isDirty()) {
				return;
			}

			if(this.mapKey) {
				// TODO make minimal PatchObject
				//if(wrap.formField.isDirty()) {
					v[wrap.formField.key || Ext.id()] = item.getValue();
				//}
			} else {
				v.push(item.getValue());
			}
		}, this);

		if(this.mapKey) {
			this.markDeleted.forEach(function(key) { v[key] = null; });
		}
		
		return v;
	},

	isValid : function(preventMark){
		if(this.disabled){
			return true;
		}

		var f = this.getAllFormFields();

		for(var i = 0, l = f.length; i < l; i++) {
			if(i == (l - 1) && f[i].auto && !f[i].isDirty()) {
				//skip auto new fields if not dirty
				continue;
			}

			if(!f[i].isValid(preventMark)) {
				return false;
			}
		}
		return true;
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

			if(i == (l - 1) && f[i].auto && !f[i].isDirty()) {
				//skip auto new fields if not dirty
				continue;
			}

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

go.form.FormGroupItemContainer = Ext.extend(Ext.Container, {

	findBy: false,
	isFormField: false,
	cls: 'go-form-group-row',
	layout: "column"
});
Ext.reg('formgroupitemcontainer', go.form.FormGroupItemContainer);
