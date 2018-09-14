Ext.ns("go.modules.core.customfields.type");

go.modules.core.customfields.type.Number = Ext.extend(go.modules.core.customfields.type.Text, {

	name: "Number",

	label: t("Number"),

	iconCls: "ic-format-list-numbered",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.modules.core.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.modules.core.customfields.type.NumberDialog();
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
		return value !== null ? GO.util.numberFormat(value, customfield.options.numberDecimals) : null;
	},

	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var config = go.modules.core.customfields.type.Number.superclass.createFormFieldConfig.call(this, customfield, config);

		delete config.anchor;
		config.width = dp(100);
		config.xtype = "numberfield";
		config.decimals = customfield.options.numberDecimals;

		return config;
	},

	getFieldType: function () {
		return "float";
	}


});

go.modules.core.customfields.CustomFields.registerType(new go.modules.core.customfields.type.Number());
