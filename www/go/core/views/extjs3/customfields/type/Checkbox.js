Ext.ns("go.customfields.type");

go.customfields.type.Checkbox = Ext.extend(go.customfields.type.Text, {

	name: "Checkbox",

	label: t("Checkbox"),

	iconCls: "ic-check-box",

	/**
	 * Returns a component to display the custom field value on the go.detail.Panel of a contact for example.
	 * When a contact loads the component is set with a value from
	 * @see this.renderDetailView()
	 *
	 * @param customfield
	 * @param config
	 */
	getDetailField: function(customfield, config) {
		return new go.detail.Property({
			itemId: customfield.databaseName,
			label: t("Checked"),
			icon: this.iconCls
		});
	},

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.customfields.type.CheckboxDialog();
	},
	
	/**
	 * Renders the custom field value for the detail views
	 * 
	 * @param {mixed} value
	 * @param {object} data Complete entity
	 * @param {object} customfield Field entity from custom fields
	 * @returns {unresolved}
	 */
	renderDetailView: function (value, data, customfield) {
		return value ? customfield.name : null;
	},

	/**
	 * Returns config object to create the form field
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var config = go.customfields.type.Number.superclass.createFormFieldConfig.call(this, customfield, config);

		delete config.anchor;
		config.xtype = "xcheckbox";
		// config.boxLabel = config.fieldLabel;
		// config.hideLabel = true;
		config.checked = !!customfield.default;
		// delete config.fieldLabel;

		return config;
	},

	getFieldType: function () {
		return "boolean";
	},
	
	/**
	 * See https://docs.sencha.com/extjs/3.4.0/#!/api/Ext.grid.Column-cfg-xtype
	 * @returns {String}
	 */
	getColumnXType : function() {
		return "booleancolumn";
	},
	
	getFilter : function(field) {
			
		return {
			name: field.databaseName,
			type: "select",
			wildcards: false,
			multiple: true,
			title: field.name,
			options: [
						{
							value: true,
							title: t("Yes")
						},
						{
							value: false,
							title: t("No")
						}
					]
		};
	}


});

// go.customfields.CustomFields.registerType(new go.customfields.type.Checkbox());

