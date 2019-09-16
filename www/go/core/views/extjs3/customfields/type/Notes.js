Ext.ns("go.customfields.type");

go.customfields.type.Notes = Ext.extend(go.customfields.type.Text, {

	name: "Notes",

	label: t("Notes"),

	iconCls: "ic-description",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.customfields.type.NotesDialog();
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
	},
	
	getFilter : function() {
		return false;
	},

	//hide in grid
	getColumn : function(field) {
		return false;
	},
	
	getFieldDefinition : function(field) {
		return false;
	}

});

// go.customfields.CustomFields.registerType(new go.customfields.type.Notes());

