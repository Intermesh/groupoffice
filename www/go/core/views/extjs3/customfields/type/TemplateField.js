Ext.ns("go.customfields.type");

go.customfields.type.TemplateField = Ext.extend(go.customfields.type.TextArea, {

	name: "TemplateField",

	label: t("Template"),

	iconCls: "ic-note",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.customfields.type.TemplateFieldDialog();
	},
	
	
	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		return;
	}


});

// go.customfields.CustomFields.registerType(new go.customfields.type.FunctionField());

