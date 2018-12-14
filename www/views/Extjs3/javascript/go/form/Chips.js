/* global Ext, go */

/**
 * Chips multiselect component
 * 
 * For an example see go/modules/community/addressbook/views/extjs3/CustomFieldSetDialog.js
 * 
 * With entityStore:
 * 
 * {
				xtype: "chips",
				entityStore: go.Stores.get("AddressBook"),
				displayField: "name",
				name: "addressBooks",
				fieldLabel: t("Address books")
			}
 * 
 * With custom store. Data must be local!:
 * 
 * {
				xtype: "chips",
				comboStore: new Ext.data.JsonStore({
					data: [],
					id: 'id',
					root: "options",
					fields: ['id','text'],
					remoteSort:true
				}),
				displayField: "name",
				name: "addressBooks",
				fieldLabel: t("Options")
			}
 * 
 */
go.form.Chips = Ext.extend(Ext.Container, {
	name: null,
	displayField: "name",
	valueField: "id",
	entityStore: null,
	comboStore: null,
	autoHeight: true,
	
	initComponent: function () {


		var tpl = new Ext.XTemplate(
						'<tpl for=".">',
						'<div class="go-chip">{' + this.displayField + '} <button class="icon">delete</button></div>',
						'</tpl>',
						'<div class="x-clear"></div>'
						);
		
	
		this.dataView = new Ext.DataView({
			store: new Ext.data.JsonStore({
				fields: [this.valueField, this.displayField],
				root: "records",
				idProperty: this.valueField				
			}),
			tpl: tpl,
			autoHeight: true,
			multiSelect: true,
			overClass: 'x-view-over',
			itemSelector: 'div.go-chip',
			listeners: {
				click: this.onClick,
				scope: this
			}
		});
		
	
		this.dataView.store.on("add", function(store, records) {
			if(this.entityStore) {
				this.comboBox.store.baseParams.filter.exclude = this.dataView.store.getRange().column(this.valueField);				
			}
			this.comboBox.store.remove(records);//this.comboBox.store.find(this.valueField, records[0].get(this.valueField)));
			this._isDirty = true;
		}, this);
		this.dataView.store.on("remove", function(store, record) {
			if(this.entityStore) {
				this.comboBox.store.baseParams.filter.exclude = this.dataView.store.getRange().column(this.valueField);							
			} 
			this.comboBox.store.add([record]);
			this._isDirty = true;
		}, this);		
		
		this.items = [{
				layout: "form",				
				items: [this.createComboBox()]
			},
			this.dataView
		];
		
		//adds back removed records from static stores.
		this.on("beforedestroy", function() {
			this.dataView.store.each(function(r) {
				this.dataView.store.remove(r);
			}, this);
		}, this);

		go.form.Chips.superclass.initComponent.call(this);
	},
	isFormField: true,
	getName: function () {
		return this.name;
	},
	_isDirty: false,
	isDirty: function () {
		return this._isDirty;
	},
	setValue: function (values) {
		
		if(this.entityStore) {	
				this.entityStore.get(values, function (entities) {				
					this.dataView.store.loadData({records: entities}, true);
			}, this);
		} else
		{
			var me = this;
			values.forEach(function(v){
				var index = me.comboStore.find(me.valueField, v);
				
				if(index > -1) {
					me.dataView.store.add([me.comboStore.getAt(index)]);
				}
			});
		}
		
		this._isDirty = false;
	},
	getValue: function () {		
		var records = this.dataView.store.getRange(), me = this, v = [];
		records.forEach(function(r) {
			v.push(r.get(me.valueField));
		});
		
		return v;
		
	},
	markInvalid: function (msg) {
		this.getEl().addClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.mark(this, msg);
	},
	clearInvalid: function () {
		this.getEl().removeClass('x-form-invalid');
		Ext.form.MessageTargets.qtip.clear(this);
	},
	createComboBox: function () {
		
		if(this.entityStore){
			this.comboStore = new go.data.Store({
				fields: [this.valueField, this.displayField],
				entityStore: this.entityStore,
				baseParams: {
					filter: {
						exclude: []
					}
				}
			});
		} else
		{
			//clone the store.
//			var records = [];
//			this.comboStore.each(function(r){
//				records.push(r.copy());
//			});
//			
//			this.comboStore = new Ext.data.Store({
//				recordType: this.comboStore.recordType
//			});
//			this.comboStore.add(records);
		}
		
		this.comboBox = new go.form.ComboBox({
			hideLabel: true,
			anchor: '100%',
			emptyText: t("Please select..."),
			pageSize: this.entityStore ? 50 : null,
			valueField: 'id',
			displayField: this.displayField,
			triggerAction: 'all',
			editable: true,
			selectOnFocus: true,
			forceSelection: true,
			mode: this.entityStore ? 'remote' : 'local',
			store: this.comboStore
		});		
		
		this.comboBox.on('select', function(combo, record, index) {
			this.dataView.store.add([record]);			
			combo.reset();			
		}, this);
		
		return this.comboBox;
	},
	
	onClick: function(dv, index, node,e) {
		if(e.target.tagName === "BUTTON") {
			this.dataView.store.removeAt(index);
		}
	},
	validate: function () {
		return true;
	}


});


Ext.reg('chips', go.form.Chips);