Ext.ns("go.modules.core.core.type");

go.modules.core.core.type.MultiSelect = Ext.extend(go.modules.core.core.type.Text, {
	
	name : "MultiSelect",
	
	label: t("Multi Select"),
	
	iconCls: "ic-list",	
	
	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog : function() {
		return new go.modules.core.core.type.MultiSelectDialog();
	},
	
	/**
	 * Render's the custom field value for the detail views
	 * 
	 * @param {mixed} value
	 * @param {object} data Complete entity
	 * @param {object} customfield Field entity from custom fields
	 * @returns {unresolved}
	 */
	renderDetailView: function (values, data, customfield) {
		if(!values) {
			return "";
		}
		
		var options = []
		values.forEach(function(value){
			var opt = customfield.dataType.options.find(function(o) {
				return o.id == value;
			});
			
			if(opt) {
				options.push(opt.text);
			}
		})
		
		return options.join(", ");
	},
	
	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var c = go.modules.core.core.type.MultiSelect.superclass.createFormFieldConfig.call(this, customfield, config);

		c.xtype = "chips";
		c.valueField = 'id';
		c.displayField = 'text';		
		c.comboStore = new Ext.data.JsonStore({
			data: customfield.dataType,
			id: 'id',
			root: "options",
			fields:['id','text'],
			remoteSort:true
		});
		return c;
	},

	getFieldType: function () {
		return "auto";
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
		
		var c = go.modules.core.core.type.MultiSelect.superclass.getFieldDefinition.call(this, field);
		
		c.convert = function(v, record) {
			return this.customFieldType.renderDetailView(v, record.data, this.customField);
		};		
		
		return c;
	}
	
	
});

go.customfields.CustomFields.registerType(new go.modules.core.core.type.MultiSelect());
