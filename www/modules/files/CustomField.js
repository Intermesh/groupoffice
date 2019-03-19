/* global Ext, go */

Ext.ns("go.modules.community.files.customfield");

go.modules.community.files.customfield.File = Ext.extend(go.customfields.type.Text, {
	
	name : "File",
	
	label: t("File"),
	
	iconCls: "ic-file",	
	
	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog : function() {
		return new go.customfields.FieldDialog();
	},
	
	/**
	 * Render's the custom field value for the detail views
	 * 
	 * @param {mixed} value
	 * @param {object} data Complete entity
	 * @param {object} customfield Field entity from custom fields
	 * @param {go.detail.Property} cmp The property component that renders the value
	 * @returns {unresolved}
	 */
	renderDetailView: function (value, data, customfield, cmp) {		
		
		return go.util.empty(value) ? null : '<a onclick="GO.files.launchFile({path: \''+  go.util.addSlashes(value) + '\'})">' + value + "</a>";
		
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
		c.xtype = "selectfile";		
		return c;
	},

	getFieldType: function () {
		return "string";
	},
	
	getFilter : function(field) {
		return false;
	}
	
});

go.customfields.CustomFields.registerType(new go.modules.community.files.customfield.File());

