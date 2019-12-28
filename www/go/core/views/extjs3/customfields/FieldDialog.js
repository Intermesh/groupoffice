go.customfields.FieldDialog = Ext.extend(go.form.Dialog, {	
	title: t('Field'),
	entityStore: "Field",
	height: dp(400),
	initComponent: function() {
		go.customfields.FieldDialog.superclass.initComponent.call(this);
		
		this.formPanel.on("load", function(form, entity){
			
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
						allowBlank: false
					},{
						xtype: 'textfield',
						name: 'databaseName',
						fieldLabel: t("Database name"),
						anchor: '100%',
						allowBlank: false
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
								this.formPanel.getForm().findField('requiredCondition').setDisabled(value);
							},
							scope: this
						}
					},
					{
						xtype: "textfield",
						name: "requiredCondition",
						fieldLabel: t("Required condition"),
						anchor: "100%"
					},
					{
						xtype: "checkbox",
						name: "conditionallyHidden",
						boxLabel: t("Conditionally hidden field"),
						hideLabel: true,
						listeners: {
							check: function(cb, value) {
								let requiredField = this.formPanel.getForm().findField('required');
								if (value) {
									requiredField.setValue(false);
								}
								requiredField.setDisabled(value);
							},
							scope: this
						}
					}
				]
			}
		];
	}
});


