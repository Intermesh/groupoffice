Ext.ns("go.modules.community.addressbook.customfield");

go.modules.community.addressbook.customfield.MultiContact = Ext.extend(go.customfields.type.Text, {

	name : "MultiContact",

	label: t("Multiple Contacts"),

	iconCls: "ic-contacts",

	/**
	 * Return dialog to edit this type of field
	 *
	 * @returns {go.modules.community.addressbook.customfield.MultiContactDialog}
	 */
	getDialog : function() {
		return new go.modules.community.addressbook.customfield.MultiContactDialog();
	},

	/**
	 * Renders the custom field value for the detail views
	 *
	 * @param {mixed} value
	 * @param {object} data Complete entity
	 * @param {object} customfield Field entity from custom fields
	 * @param {object} cmp The component
	 * @returns {string}
	 */
	renderDetailView: function (values, data, customfield, cmp) {
		if( go.util.empty(values) || !cmp) {
			return "";
		}

		go.Db.store("Contact").get(values).then(function(result) {
			let cnt = [];
			result.entities.forEach((contact) => {
				cnt.push('<a href="#' + go.Entities.get("Contact").getRouterPath(contact.id) + '">' + go.modules.community.addressbook.renderName(contact) + '</a>');
			});
			cmp.setValue(cnt.join(", "));
		}).catch(function() {
			cmp.setValue(t("Not found or no access"));
		}). finally(function() {
			if(cmp) {
				cmp.setVisible(true);
			}
		});

		let options = [];
		values.forEach(function(value){
			const opt = customfield.dataType.options.find(function(o) {
				return o.id === value;
			});

			if(opt) {
				options.push(opt.text);
			}
		});

		return options.join(", ");
	},

	/**
	 * Returns config object to create the form field
	 *
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		const c = go.modules.community.addressbook.customfield.MultiContact.superclass.createFormFieldConfig.call(this, customfield, config);

		c.xtype = "chips";
		c.valueField = "id";
		c.displayField = "name";
		c.entityStore = "Contact";

		let filters = {};

		if(!go.util.empty(customfield.options.addressBookId)) {
			filters.addressBookId = {
				addressBookId: customfield.options.addressBookId
			};
		}
		if(Ext.isDefined(customfield.options.isOrganization)) {
			filters.isOrganization = {
				isOrganization: customfield.options.isOrganization
			}
		}

		if(!go.util.empty(filters)) {
			c.comboStoreConfig = {filters: filters};
		}

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

		const c = go.modules.community.addressbook.customfield.MultiContact.superclass.getFieldDefinition.call(this, field);

		c.convert = function(v, record) {
			return this.customFieldType.renderDetailView(v, record.data, this.customField);
		};

		return c;
	},

	getColumn : function(field) {

		const c = go.modules.community.addressbook.customfield.MultiContact.superclass.getColumn.call(this, field);

		c.sortable = false;

		return c;
	},

	getFilter : function(field) {
		let config = {
			name: field.databaseName,
			type: "go.modules.community.addressbook.ContactCombo",
			typeConfig: this.createFormFieldConfig(field),
			multiple: true,
			wildcards: true,
			title: field.name
		};

		if(Ext.isDefined(field.options.isOrganization)) {
			config.typeConfig.isOrganization = field.options.isOrganization;
		}
		if(!go.util.empty(field.options.addressBookId)) {
			config.typeConfig.addressBookId = field.options.addressBookId;
		}
		return config;
	}

});
