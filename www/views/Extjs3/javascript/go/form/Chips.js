go.form.Chips = Ext.extend(Ext.Container, {
	name: null,
	displayField: "name",
	valueField: "id",
	entityStore: null,
	autoHeight: true,
	
	initComponent: function () {


		var tpl = new Ext.XTemplate(
						'<tpl for=".">',
						'<div class="go-chip">{' + this.displayField + '} <button class="icon">delete</button></div>',
						'</tpl>',
						'<div class="x-clear"></div>'
						);
		
	
		this.dataView = new Ext.DataView({
			store: new go.data.Store({
				fields: ['id', 'name'],
				entityStore: this.entityStore
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
		})
		
		this.dataView.store.on("add", function(store) {
			this.comboBox.store.baseParams.filter.exclude = this.dataView.store.getRange().column("id");			
		}, this);
		this.dataView.store.on("remove", function(store) {
			this.comboBox.store.baseParams.filter.exclude = this.dataView.store.getRange().column("id");			
		}, this);
		
		this.items = [{
				layout: "form",				
				items: [this.createComboBox()]
			},
			this.dataView
		];

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
	setValue: function (models) {
		this.dataView.store.loadData({records: models}, true);
	},
	getValue: function () {
		return this.dataView.store.getRange();
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
		this.comboBox = new go.form.ComboBox({
			hideLabel: true,
			anchor: '100%',
			emptyText: t("Please select..."),
			pageSize: 50,
			valueField: 'id',
			displayField: this.displayField,
			triggerAction: 'all',
			editable: true,
			selectOnFocus: true,
			forceSelection: true,
			store: new go.data.Store({
				fields: ['id', 'name'],
				entityStore: this.entityStore,
				baseParams: {
					filter: {
						exclude: []
					}
				}
			})
		});
		
		
		this.comboBox.on('select', function(combo, record, index) {
			this.dataView.store.add([record]);
			combo.store.remove(record);			
			combo.reset();
			
		}, this);
		
		return this.comboBox
	},
	
	onClick: function(dv, index, node,e) {
		if(e.target.tagName == "BUTTON") {
			this.dataView.store.removeAt(index);
		}
	},
	validate: function () {
		return true;
	}


});


Ext.reg('chips', go.form.Chips);