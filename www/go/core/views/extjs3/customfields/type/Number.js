Ext.ns("go.customfields.type");

go.customfields.type.Number = Ext.extend(go.customfields.type.Text, {

	name: "Number",

	label: t("Number"),

	iconCls: "ic-format-list-numbered",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.customfields.type.NumberDialog();
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
		return value !== null ? go.util.Format.number(value, customfield.options.numberDecimals) : null;
	},

	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var config = go.customfields.type.Number.superclass.createFormFieldConfig.call(this, customfield, config);

		delete config.anchor;
		config.width = dp(200);
		config.xtype = "numberfield";
		config.decimals = customfield.options.numberDecimals;

		return config;
	},

	getFieldType: function () {
		return "float";
	},

	getColumnXType : function() {
		return "numbercolumn";
	},

	getColumn : function(field) {
		const c = go.customfields.type.Number.superclass.getColumn.call(this, field);
		c.renderer = function(v) {
			return v ? go.util.Format.number(v, field.options.numberDecimals) : "";
		};
		return c;
	},


	getFilter : function(field) {
		return {
			name: field.databaseName,
			wildcards: false,
			type: "number",
			multiple: false,
			title: field.name,
			customfield: field
		};
	}


});

// go.customfields.CustomFields.registerType(new go.customfields.type.Number());
