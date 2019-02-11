Ext.ns("go.modules.core.core.type");

go.modules.core.core.type.Notes = Ext.extend(go.modules.core.core.type.Text, {

	name: "Notes",

	label: t("Notes"),

	iconCls: "ic-description",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.modules.core.core.FieldDialog}
	 */
	getDialog: function () {
		return new go.modules.core.core.type.NotesDialog();
	},


	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {	
		return {
			xtype: "box",
			autoEl: "p",
			html: customfield.options.formNotes
		};
	},
	
	getDetailField: function(customfield, config) {
		
		
		return new Ext.BoxComponent({
			autoEl: "p",
			html: customfield.options.detailNotes,
			hidden: go.util.empty(customfield.options.detailNotes) 
		});
	}

});

go.modules.core.core.CustomFields.registerType(new go.modules.core.core.type.Notes());

