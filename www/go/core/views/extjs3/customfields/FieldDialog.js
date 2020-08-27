go.customfields.FieldDialog = Ext.extend(go.form.Dialog, {
	title: t('Field'),
	entityStore: "Field",
	height: dp(700),
	width: dp(1000),
	formPanelLayout: "column",
	initComponent: function () {
		go.customfields.FieldDialog.superclass.initComponent.call(this);

		this.formPanel.on("load", function (form, entity) {
			var types = go.customfields.CustomFields.getTypes();
			form.getForm().findField('typeLabel').setValue(types[entity.type] ? types[entity.type].label : entity.type);
		}, this);
		this.isReserved = function (value) {
			// Make sure that a database column name is NOT a reserved keyword
			// As per https://mariadb.com/kb/en/columnstore-naming-conventions/
			var arReserved = ['select', 'char', 'table', 'action', 'add', 'alter', 'bigint', 'bit', 'cascade', 'change', 'character',
				'charset', 'check', 'clob', 'column', 'columns', 'comment', 'constraint', 'constraints', 'create', 'current_user', 'datetime',
				'dec', 'decimal', 'deferred', 'default', 'deferrable', 'double', 'drop', 'engine', 'exists', 'foreign', 'full', 'idb_blob',
				'idb_char', 'idb_delete', 'idb_float', 'idb_int', 'if', 'immediate', 'index', 'initially', 'integer', 'key', 'match',
				'max_rows', 'min_rows', 'modify', 'no', 'not', 'null_tok', 'number', 'numeric', 'on', 'partial', 'precision',
				'primary', 'real', 'references', 'rename', 'restrict', 'session_user', 'set', 'smallint', 'system_user', 'table',
				'time', 'tinyint', 'to', 'truncate', 'unique', 'unsigned', 'update', 'user', 'varbinary', 'varchar', 'varying',
				'with', 'zone'];
			return (arReserved.indexOf(String(value).toLowerCase()) > -1);
		};

	},
	initFormItems: function () {
		return [{
			columnWidth: .5,
			xtype: 'fieldset',
			title: t("General"),
			items: [{
				xtype: 'plainfield',
				name: 'typeLabel',
				fieldLabel: t('Type')
			},
				{
					xtype: 'textfield',
					name: 'name',
					fieldLabel: t("Name"),
					anchor: '100%',
					allowBlank: false,
					listeners: {
						change: function (field, value, old) {
							var dbField = this.formPanel.form.findField('databaseName');
							if (dbField.getValue() != "") {
								return;
							}

							//replace all whitespaces with underscores
							var dbName = value.replace(/\s+/g, '_');
							dbName = dbName.replace(/[^A-Za-z0-9_\-]+/g, "");
							dbName = dbName.replace(/^[0-9]+/, '');
							if (String(dbName).length === 0) {
								return false;
							}
							if (this.isReserved(dbName)) {
								dbName = 'go_' + dbName;
							}
							dbField.setValue(dbName);
						},
						scope: this
					}
				}, {
					xtype: 'textfield',
					name: 'databaseName',
					fieldLabel: t("Database name"),
					anchor: '100%',
					allowBlank: false,
					hint: t("This name is used in the database and can only contain alphanumeric characters and underscores. It's only visible to exports and the API."),
					listeners: {
						change: function (field, value, old) {
							var dbName = value.replace(/\s+/g, '_');
							dbName = dbName.replace(/[^A-Za-z0-9_\-]+/g, "");
							dbName = dbName.replace(/^[0-9]+/, '');
							if (String(dbName).length === 0) {
								return false
							}
							if (this.isReserved(dbName)) {
								dbName = 'go_' + dbName;
							}
							field.setValue(dbName);
						},
						scope: this
					}
				}, {
					xtype: "textfield",
					name: "hint",
					fieldLabel: t("Hint text"),
					anchor: "100%"
				}, {
					xtype: "textfield",
					name: "prefix",
					fieldLabel: t("Prefix"),
					anchor: "100%"
				}, {
					xtype: "textfield",
					name: "suffix",
					fieldLabel: t("Suffix"),
					anchor: "100%"
				}, {
					xtype: "checkbox",
					name: "hiddenInGrid",
					fieldLabel: t("Hidden in grid"),
					checked: true,
					hint: t("Field will be hidden by default in grids. Users can enable it through the grid column menu.")
				}]
		},
			{
				columnWidth: .5,
				xtype: 'fieldset',
				title: t("Validation"),
				items: [{
					xtype: "checkbox",
					name: "unique",
					boxLabel: t("Unique values"),
					hideLabel: true
				},{
					xtype: "checkbox",
					name: "required",
					boxLabel: t("Required field"),
					hideLabel: true,
					listeners: {
						check: function (cb, value) {
							var form = this.formPanel.getForm();
							form.findField('relatedFieldCondition').setDisabled(value);
							form.findField('conditionallyRequired').setDisabled(value);
							form.findField('conditionallyHidden').setDisabled(value);
						},
						scope: this
					}
				},
					{
						xtype: "textfield",
						name: "relatedFieldCondition",
						fieldLabel: t("Required condition"),
						anchor: "100%",
						hint: "eg. 'nameOfStandardOrCustomField = test'"
					},
					{
						xtype: "checkbox",
						name: "conditionallyRequired",
						boxLabel: t("Conditionally required field"),
						hideLabel: true,
						listeners: {
							check: function (cb, value) {
								var form = this.formPanel.getForm(),
									requiredField = form.findField('required'),
									conditionallyHidden = form.findField('conditionallyHidden');

								if (!conditionallyHidden.getValue()) {
									if (value) {
										requiredField.setValue(false);
									}
									requiredField.setDisabled(value);
								}
							},
							scope: this
						}
					},
					{
						xtype: "checkbox",
						name: "conditionallyHidden",
						boxLabel: t("Conditionally hidden field"),
						hideLabel: true,
						listeners: {
							check: function (cb, value) {
								var form = this.formPanel.getForm(),
									requiredField = form.findField('required'),
									conditionallyRequired = form.findField('conditionallyRequired');

								if (!conditionallyRequired.getValue()) {
									if (value) {
										requiredField.setValue(false);
									}
									requiredField.setDisabled(value);
								}
							},
							scope: this
						}
					}
				]
			}
		];
	}
});


