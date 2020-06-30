Ext.ns("go.customfields.type");

go.customfields.type.Select = Ext.extend(go.customfields.type.Text, {
	
	name : "Select",
	
	label: t("Select"),
	
	iconCls: "ic-list",	
	
	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog : function() {
		return new go.customfields.type.SelectDialog();
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
		var opt = this.findRecursive(value, customfield.dataType.options);		
		return opt ? opt.text : null;
	},

	findRecursive: function (value, options) {
		var o;
		for(var i = 0, l = options.length; i < l; i++) {
			o = options[i];
			if(o.id == value) {
				return o;
			}

			if(o.children) {
				var nested = this.findRecursive(value, o.children);
				if(nested) {
					return nested;
				}
			}
		}

		return false;
	},
	
	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var c = go.customfields.type.Select.superclass.createFormFieldConfig.call(this, customfield, config);

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
		
		var c = go.customfields.type.Select.superclass.getFieldDefinition.call(this, field);
		
		c.convert = function(v, record) {
			return this.customFieldType.renderDetailView(v, record.data, this.customField);
		};		
		
		return c;
	},
	
	getFilter : function(field) {
			
		return {
			name: field.databaseName,
			type: "go.customfields.type.TreeSelectField",
			multiple: true,
			wildcards: true,
			title: field.name,
			customfield: field
		};
	}
	
});

// go.customfields.CustomFields.registerType(new go.customfields.type.Select());
