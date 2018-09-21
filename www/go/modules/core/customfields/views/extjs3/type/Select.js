Ext.ns("go.modules.core.customfields.type");

go.modules.core.customfields.type.Select = Ext.extend(go.modules.core.customfields.type.Text, {
	
	name : "Select",
	
	label: t("Select"),
	
	iconCls: "ic-list",	
	
	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.modules.core.customfields.FieldDialog}
	 */
	getDialog : function() {
		return new go.modules.core.customfields.type.SelectDialog();
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
		
		var opt = customfield.dataType.options.find(function(o) {
			return o.id == value;
		});
		
		return opt ? opt.text : null;
	},
	
	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var c = go.modules.core.customfields.type.Date.superclass.createFormFieldConfig.call(this, customfield, config);

		c.xtype = "treeselectfield";
		c.customfield = customfield;
		return c;
	},

	getFieldType: function () {
		return "int";
	},
	
	/**
	 * Get the field definition for creating Ext.data.Store's
	 * 
	 * Also the customFieldType (this) and customField (Entity Field) are added
	 * 
	 * @see https://docs.sencha.com/extjs/3.4.0/#!/api/Ext.data.Field
	 * @returns {Object}
	 */
	getFieldDefinition : function(field) {
		
		var c = go.modules.core.customfields.type.Date.superclass.getFieldDefinition.call(this, field);
		
		c.convert = function(v, record) {
			return this.customFieldType.renderDetailView(v, record.data, this.customField);
		};		
		
		return c;
	}
	
	
});

go.modules.core.customfields.CustomFields.registerType(new go.modules.core.customfields.type.Select());
