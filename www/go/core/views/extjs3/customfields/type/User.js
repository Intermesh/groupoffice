Ext.ns("go.customfields.type");

go.customfields.type.User = Ext.extend(go.customfields.type.Text, {
	
	name : "User",
	
	label: t("User"),
	
	iconCls: "ic-person",	
	
	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog : function() {
		return new go.customfields.type.UserDialog();
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
		
		if(!value) {
			return "";
		}
		
		go.Db.store("User").get([value], function(users) {
			var displayValue;
			if(!users[0]) {
				displayValue = t("Not found or no access");
			} else
			{
				displayValue = users[0].displayName;
			}
			cmp.setValue(displayValue);
			cmp.setVisible(true);
		});
		
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
		c.xtype = "usercombo";
		c.hiddenName = c.name;
		delete c.name;
		
		return c;
	},

	getFieldType: function () {
		return "relation";
	},

	getRelations : function(customfield) {
		var r = {};
		r[customfield.databaseName] = {store: "User", fk: customfield.databaseName};
		return r;
	},

	getColumn : function(field) {		
		var c = go.customfields.type.User.superclass.getColumn.call(this, field);	
		c.renderer = function(v) {
			return v ? v.displayName : "";
		};
		return c;
	},
	
	getFilter : function(field) {
			
		return {
			name: field.databaseName,
			type: "go.users.UserCombo",
			multiple: true,
			title: field.name,
			customfield: field
		};
	}
	
	
});

go.customfields.CustomFields.registerType(new go.customfields.type.User());
