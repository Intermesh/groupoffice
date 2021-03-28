Ext.ns("go.customfields.type");

go.customfields.type.MultiSelect = Ext.extend(go.customfields.type.Text, {
	
	name : "MultiSelect",
	
	label: t("Multi Select"),
	
	iconCls: "ic-list",	
	
	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog : function() {
		return new go.customfields.type.MultiSelectDialog();
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
		var options = [];
		values.forEach(function(value){
			var opt = customfield.dataType.options.find(function(o) {
				return o.id == value;
			});
			
			if(opt) {
				options.push(opt.text);
			}
		});
		
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
		var c = go.customfields.type.MultiSelect.superclass.createFormFieldConfig.call(this, customfield, config);

		c.xtype = "chips";
		c.valueField = 'id';
		c.displayField = 'text';		
		c.comboStore = new Ext.data.JsonStore({
			data: customfield.dataType,
			autoDestroy: true,
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
		
		var c = go.customfields.type.MultiSelect.superclass.getFieldDefinition.call(this, field);
		
		c.convert = function(v, record) {
			return this.customFieldType.renderDetailView(v, record.data, this.customField);
		};		
		
		return c;
	},
	
	getColumn : function(field) {
		
		var c = go.customfields.type.MultiSelect.superclass.getColumn.call(this, field);
		
		c.sortable = false;
		
		return c;
	},
	
	getFilter : function(field) {
			
		return {
			name: field.databaseName,
			type: "select",
			multiple: true,
			wildcards: true,
			title: field.name,
			options: field.dataType.options.map(function(o) {
				return {
					value: o.id,
					title: o.text
				}
			})
		};
	}
	
	
});

// go.customfields.CustomFields.registerType(new go.customfields.type.MultiSelect());
