go.customfields.FieldDialog = Ext.extend(go.form.Dialog, {	
	title: t('Field'),
	entityStore: "Field",
	height: dp(700),
	width: dp(600),
	initComponent: function() {
		go.customfields.FieldDialog.superclass.initComponent.call(this);
		
		this.formPanel.on("load", function(form, entity) {
			var types = go.customfields.CustomFields.getTypes();
			form.getForm().findField('typeLabel').setValue(types[entity.type] ? types[entity.type].label : entity.type);
		}, this);
	},
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [{
						xtype:'plainfield',
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
							change: function(field, value, old) {
								var dbField = this.formPanel.form.findField('databaseName');
								if(dbField.getValue() != "") {
									return;
								}

								//replace all whitespaces with underscores
								var dbName = value.replace(/\s+/g, '_');
								dbName = dbName.replace(/[^A-Za-z0-9_\-]+/g, "");

								dbField.setValue(dbName);
							},
							scope: this
						}
					},{
						xtype: 'textfield',
						name: 'databaseName',
						fieldLabel: t("Database name"),
						anchor: '100%',
						allowBlank: false,
						hint: t("This name is used in the database and can only contain alphanummeric characters and undescores. It's only visible to exports and the API.")
					},{
						xtype: "textfield",
						name: "hint",
						fieldLabel: t("Hint text"),
						anchor: "100%"
					},{
						xtype: "textfield",
						name: "prefix",
						fieldLabel: t("Prefix"),
						anchor: "100%"
					},{
						xtype: "textfield",
						name: "suffix",
						fieldLabel: t("Suffix"),
						anchor: "100%"
					},{
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
							check: function(cb, value) {
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
							check: function(cb, value) {
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


