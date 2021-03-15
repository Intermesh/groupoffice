go.import.ColumnSelectDialog = Ext.extend(go.Window, {
	modal: true,
	layout: "fit",
	title: t("Select columns"),
	height: dp(600),
	width: dp(400),
	entity: "",

	layout: "border",
	initComponent: function () {
		this.spreadSheetExportGrid = new go.import.SpreadSheetExportGrid({
			entityStore: this.entity,
			region: "center"
		});

		this.northPanel = new Ext.Panel({
			layout: "form",
			region: "north",
			autoHeight: true,
			buttonAlign: "center",
			buttons: [{
				text: t("Delete"),
				class: "danger",
				scope: this,
				handler: function() {
					Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to delete this item?"), function (btn) {

						if (btn == "no") {
							return;
						}

						const id = this.spreadSheetExportCombo.getValue();

						const rec = this.spreadSheetExportCombo.store.getById(id);
						this.spreadSheetExportCombo.store.remove(rec);

						go.Db.store("SpreadSheetExport").destroy(id).then(() => {

							const first = this.spreadSheetExportCombo.store.getAt(0);
							if (first) {
								this.spreadSheetExportCombo.setValue(first.id);
								this.spreadSheetExportGrid.loadSelection(first.data.columns);
							} else
							{
								this.spreadSheetExportCombo.reset();
							}
						});
					}, this);
				}
			},{
				text: t("Save"),
				handler: function () {
					this.save();
				},
				scope: this
			}],
			items: [{
				xtype: "fieldset",
				items: [

					{
						anchor: "100%",
						fieldLabel: t("Preset"),
						xtype: "compositefield",
						items:[
							this.spreadSheetExportCombo = new go.form.ComboBox({
								// allowNew: true,
								hiddenName: 'spreadSheetExportId',
								flex: 1,
								emptyText: t("Select preset..."),
								valueField: 'id',
								displayField: 'name',
								triggerAction: 'all',
								editable: true,
								selectOnFocus: true,
								forceSelection: true,
								store: {
									xtype: "gostore",
									entityStore: "SpreadSheetExport",
									fields: ['id', 'name', 'columns'],
									sortInfo: {
										field: "name",
										direction: "ASC"
									},
									filters: {
										default: {
											userId: go.User.id,
											entity: this.entity
										}
									}
								},
								listeners: {
									render: function (combo) {
										combo.store.load().then(() => {
											const first = combo.store.getAt(0);
											if (first) {
												combo.setValue(first.id);
												this.spreadSheetExportGrid.loadSelection(first.data.columns);
											}
										})
									},
									// beforecreatenew: function(combo, entity) {
									// 	entity.columns = this.spreadSheetExportGrid.getSelection();
									// 	entity.userId = go.User.id;
									// 	entity.entity = this.entity;
									// },
									select: function (combo, record, index) {
										this.spreadSheetExportGrid.loadSelection(record.data.columns);
									},
									scope: this
								}
							}),
							{
								xtype: "box",
								width: dp(16)
							},
							{
								xtype: "button",
								iconCls: 'ic-add',
								tooltip: t("Create new preset"),
								handler: function() {
									this.save(true);
								},
								scope: this
							}
						]
					}
					]
			}]
		});


		this.items = [this.northPanel, this.spreadSheetExportGrid];

		this.buttons = [

			'->',
			{
				cls: "primary",
				text: t("Export"),
				handler: function () {
					this.handler.call(this.scope || window, this.spreadSheetExportGrid.getSelection())
					this.close();
				},
				scope: this
			}
		];

		go.import.ColumnSelectDialog.superclass.initComponent.call(this);
	},


	getSelection() {
		return this.spreadSheetExportGrid.getSelection();
	},

	save : function(isNew) {
		Ext.MessageBox.prompt(t("Save"), t("Please enter a name"), function (btn, name) {

			if (btn == "cancel" || !name) {
				return;
			}

			const me = this;

			me.getEl().mask();

			go.Db.store("SpreadSheetExport").save({
				name: name,
				columns: me.spreadSheetExportGrid.getSelection(),
				userId: go.User.id,
				entity: me.entity
			}, isNew ? null : this.spreadSheetExportCombo.getValue())
				.then(function (entity) {
					me.spreadSheetExportCombo.setValue(entity.id);
				})

				.finally(function () {
					me.getEl().unmask();
				})
		}, this, false, isNew ? "" :this.spreadSheetExportCombo.getRawValue());
	}


});