Ext.ns("go.modules.core.core.type");

go.modules.core.core.type.FunctionField = Ext.extend(go.modules.core.core.type.Number, {

	name: "FunctionField",

	label: t("Function"),

	iconCls: "ic-functions",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.modules.core.core.type.FunctionFieldDialog();
	},
	
	
	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var config = go.modules.core.core.type.FunctionField.superclass.createFormFieldConfig.call(this, customfield, config);
		config.readOnly = true;

		return config;
	}


});

go.customfields.CustomFields.registerType(new go.modules.core.core.type.FunctionField());

