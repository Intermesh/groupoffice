Ext.ns("go.customfields.type");

go.customfields.type.FunctionField = Ext.extend(go.customfields.type.Number, {

	name: "FunctionField",

	label: t("Function"),

	iconCls: "ic-functions",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.customfields.type.FunctionFieldDialog();
	},
	
	
	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		config = go.customfields.type.FunctionField.superclass.createFormFieldConfig.call(this, customfield, config);
		config.readOnly = true;
		config.submit = false;

		return config;
	}


});

// go.customfields.CustomFields.registerType(new go.customfields.type.FunctionField());

