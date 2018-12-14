Ext.ns("go.modules.core.customfields.type");

go.modules.core.customfields.type.FunctionField = Ext.extend(go.modules.core.customfields.type.Number, {

	name: "FunctionField",

	label: t("Function"),

	iconCls: "ic-functions",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.modules.core.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.modules.core.customfields.type.FunctionFieldDialog();
	},
	
	
	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var config = go.modules.core.customfields.type.FunctionField.superclass.createFormFieldConfig.call(this, customfield, config);
		config.readOnly = true;

		return config;
	}


});

go.modules.core.customfields.CustomFields.registerType(new go.modules.core.customfields.type.FunctionField());

