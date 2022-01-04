Ext.ns("go.customfields.type");

go.customfields.type.Date = Ext.extend(go.customfields.type.Text, {

	name: "Date",

	label: t("Date"),

	iconCls: "ic-event",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.customfields.type.DateDialog();
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
		return go.util.Format.date(value);
	},

	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var config = go.customfields.type.Date.superclass.createFormFieldConfig.call(this, customfield, config);

		delete config.anchor;
		
		config.xtype = "datefield";
		
		return config;
	},

	getFieldType: function () {
		return "date";
	},

	getColumn: function(field) {

		const def = go.customfields.type.Date.superclass.getColumn.call(this, field);
		def.dateOnly = true;

		return def;
	},

	/**
	 * See https://docs.sencha.com/extjs/3.4.0/#!/api/Ext.grid.Column-cfg-xtype
	 * @returns {String}
	 */
	getColumnXType : function() {
		return "datecolumn";
	},
	
	getFilter : function(field) {
		return {
			name: field.databaseName,
			wildcards: false,
			type: "date",
			multiple: false,
			title: field.name
		};
	}


});

// go.customfields.CustomFields.registerType(new go.customfields.type.Date());

