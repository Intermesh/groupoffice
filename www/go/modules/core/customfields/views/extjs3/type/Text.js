Ext.ns("go.modules.core.customfields.type");

go.modules.core.customfields.type.Text = Ext.extend(Ext.util.Observable, {
	
	name : "Text",
	
	label: t("Text"),
	
	iconCls: "ic-description",	
	
	getDialog : function() {
		return new go.modules.core.customfields.FieldDialog();
	},
	
	renderDetailView: function (value, data, customfield) {
		return value;
	},

	renderFormField: function (customfield, config) {
		config = config || {};

		if (!GO.util.empty(customfield.options.validationRegex)) {

			if (!GO.util.empty(customfield.options.validationModifiers))
				config.regex = new RegExp(customfield.options.validationRegex, customfield.options.validationModifiers);
			else
				config.regex = new RegExp(customfield.options.validationRegex);
		}

		if (!GO.util.empty(customfield.hint)) {
			config.hint = customfield.hint;
		}

		var fieldLabel = customfield.name;

		if (!GO.util.empty(customfield.prefix))
			fieldLabel = fieldLabel + ' (' + customfield.prefix + ')';

		if (!GO.util.empty(customfield.required)) {
			fieldLabel += '*';
		}

		if (customfield.options.maxLength) {
			config.maxLength = customfield.options.maxLength;
		}

		var field = Ext.apply({			
			xtype: 'textfield',
			name: 'customFields.' + customfield.databaseName,
			fieldLabel: fieldLabel,
			anchor: '-20',
			allowBlank: !customfield.required
		}, config);
		
		return this.applySuffix(customfield, field);
	},
	
	applySuffix: function (customfield, field) {
		if (!GO.util.empty(customfield.suffix)) {
			return {
				anchor: '-20',
				xtype: 'compositefield',
				fieldLabel: field.fieldLabel,
				items: [field, {
						xtype: 'label',
						text: customfield.suffix,
//							hideLapplySuffixabel: true,
						columnWidth: '.1'
					}]
			};
		} else {
			return field;
		}
	}
});

go.CustomFields.registerType(new go.modules.core.customfields.type.Text());
