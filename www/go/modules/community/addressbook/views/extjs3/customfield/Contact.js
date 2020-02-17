Ext.ns("go.modules.community.addressbook.customfield");

go.modules.community.addressbook.customfield.Contact = Ext.extend(go.customfields.type.Text, {
	
	name : "Contact",
	
	label: t("Contact"),
	
	iconCls: "ic-person",	
	
	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog : function() {
		return new  go.modules.community.addressbook.customfield.ContactDialog();
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
		
		go.Db.store("Contact").single(value).then(function(contact) {
			cmp.setValue('<a href="#' + go.Entities.get("Contact").getRouterPath(contact.id) + '">' + contact.name + '</a>');
		}).catch(function() {
			cmp.setValue(t("Not found or no access"));
		}). finally(function() {
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
		var c = go.modules.community.addressbook.customfield.Contact.superclass.createFormFieldConfig.call(this, customfield, config);
		
		c.xtype = "contactcombo";
		c.isOrganization = customfield.options.isOrganization; 
		c.hiddenName = c.name;
		delete c.name;
		
		return c;
	},

	getFieldType: function () {
		return "relation";
	},

	getRelations : function(customfield) {
		var r = {};
		r[customfield.databaseName] = {store: "Contact", fk: customfield.databaseName};
		return r;
	},
	
	getColumn : function(field) {		
		var c = go.modules.community.addressbook.customfield.Contact.superclass.getColumn.call(this, field);	
		c.renderer = function(v) {
			return v ? v.name : "";
		};
		return c;
	},
	
	getFilter : function(field) {
			
		return {
			name: field.databaseName,
			type: "go.modules.community.addressbook.ContactCombo",
			typeConfig: this.createFormFieldConfig(field),
			multiple: true,
			wildcards: true,
			title: field.name
		};
	}
	
	
});

// go.customfields.CustomFields.registerType(new go.modules.community.addressbook.customfield.Contact());

