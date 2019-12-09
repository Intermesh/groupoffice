Ext.ns("go.customfields.type");

go.customfields.type.TextArea = Ext.extend(go.customfields.type.Text, {

	name: "TextArea",

	label: t("Text area"),

	//iconCls: "ic-text",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.customfields.type.TextAreaDialog();
	},

	/**
	 * Render's the custom field value for the detail views
	 * 
	 * @param {mixed} value
	 * @param {object} data Complete entity
	 * @param {object} customfield Field entity from custom fields
	 * @returns {unresolved}
	 */
	renderDetailView: function (value, data, customfield) {
		return go.util.textToHtml(value);
	},

	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		config = go.customfields.type.TextArea.superclass.createFormFieldConfig.call(this, customfield, config);

		config.xtype = "textarea";
		config.grow = true;
		config.preventScrollbars = true;
		config.maxLength = 65535;

		return config;
	},

	getFieldType: function () {
		return "string";
	}


});

// go.customfields.CustomFields.registerType(new go.customfields.type.TextArea());
