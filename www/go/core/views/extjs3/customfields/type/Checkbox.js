Ext.ns("go.modules.core.core.type");

go.modules.core.core.type.Checkbox = Ext.extend(go.modules.core.core.type.Text, {

	name: "Checkbox",

	label: t("Checkbox"),

	iconCls: "ic-check-box",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.modules.core.core.FieldDialog}
	 */
	getDialog: function () {
		return new go.modules.core.core.type.CheckboxDialog();
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
		return value ? t("Yes") : t("No");
	},

	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var config = go.modules.core.core.type.Number.superclass.createFormFieldConfig.call(this, customfield, config);

		delete config.anchor;
		config.xtype = "checkbox";
		config.boxLabel = config.fieldLabel;
		config.checked = !!customfield.default;
		delete config.fieldLabel;

		return config;
	},

	getFieldType: function () {
		return "boolean";
	}


});

go.modules.core.core.CustomFields.registerType(new go.modules.core.core.type.Checkbox());

