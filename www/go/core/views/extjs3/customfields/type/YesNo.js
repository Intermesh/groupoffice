Ext.ns("go.customfields.type");

go.customfields.type.YesNo = Ext.extend(go.customfields.type.Text, {

	name: "YesNo",

	label: t("Yes or no"),

	iconCls: "ic-check-box",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog: function () {
		return new go.customfields.FieldDialog();
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
		if (value === null) {
			return t("Unknown");
		}

		return value === 1 ? t("Yes") : t("No");
	},

	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig: function (customfield, config) {
		var f = go.customfields.type.YesNo.superclass.createFormFieldConfig.call(this, customfield, config);

		var store = new Ext.data.SimpleStore({
			id: 'id',
			fields: ['id', 'text'],
			data: [
				[1, t("Yes")],
				[-1, t("No")]],
			remoteSort: false
		});

		

		return Ext.apply(f, {
			xtype: 'comboboxreset',
			store: store,
			valueField: 'id',
			displayField: 'text',
			hiddenName: f.name, 
			mode: 'local',
			editable: false,
			triggerAction: 'all',
			selectOnFocus: true,
			forceSelection: false
		}, config);
	},

	getFieldType: function () {
		return "int";
	},
	
	getFilter : function(field) {
		return {
			name: field.databaseName,
			type: "select",
			multiple: true,
			wildcards: false,
			title: field.name,
			options: [{
					value: null,
					title: t("Not set")
			},{
					value: 1,
					title: t("Yes")
			},{
					value: -1,
					title: t("No")
			}]
		};
	},

	getColumnXType : function() {
		return "gridcolumn";
	},

	/**
	 * Get grid column definition
	 *
	 * @param {type} field
	 * @returns {TextAnonym$0.getColumn.TextAnonym$6}
	 */
	getColumn : function(field) {
		var def = this.getFieldDefinition(field);
		return {
			dataIndex: def.name,
			header: def.customField.name,
			hidden: def.customField.hiddenInGrid,
			id: "custom-field-" + encodeURIComponent(def.customField.databaseName),
			sortable: true,
			hideable: true,
			draggable: true,
			xtype: this.getColumnXType(),
			renderer: function(value) {
				if(value === null) {
					return t("Not set");
				}
				return value === 1 ? t("Yes") : t("No");
			}
		};
	},



});

// go.customfields.CustomFields.registerType(new go.customfields.type.YesNo());

