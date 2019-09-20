Ext.ns("go.customfields.type");

go.customfields.type.DateTime = Ext.extend(go.customfields.type.Text, {

	name: "DateTime",

	label: t("Date and time"),

	iconCls: "ic-schedule",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.customfields.type.DateTimeDialog();
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
		var config = go.customfields.type.Date.superclass.createFormFieldConfig.call(this, customfield, config);

		delete config.anchor;
		//config.width = dp(340);
		config.xtype = "datetimefield";
		
		return config;
	},

	getFieldType: function () {
		return "date";
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
			type: "date",	
			multiple: false,
			title: field.name
		};
	}


});


