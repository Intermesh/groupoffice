Ext.ns("go.modules.core.customfields.type");

go.modules.core.customfields.type.DateTime = Ext.extend(go.modules.core.customfields.type.Text, {

	name: "DateTime",

	label: t("Date and time"),

	iconCls: "ic-schedule",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.modules.core.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.modules.core.customfields.type.DateTimeDialog();
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
		console.log(value);
		return GO.util.dateFormat(value);
	},

	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var config = go.modules.core.customfields.type.Date.superclass.createFormFieldConfig.call(this, customfield, config);

		delete config.anchor;
		config.width = dp(340);
		config.xtype = "datetimefield";
		
		return config;
	},

	getFieldType: function () {
		return "float";
	}


});

go.modules.core.customfields.CustomFields.registerType(new go.modules.core.customfields.type.DateTime());

