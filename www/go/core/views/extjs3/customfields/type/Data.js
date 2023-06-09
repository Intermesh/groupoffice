Ext.ns("go.customfields.type");

go.customfields.type.Data = Ext.extend(go.customfields.type.Text, {

	name: "DataField",

	label: t("Data"),

	iconCls: "ic-storage",

	/**
	 * Return dialog to edit this type of field
	 * 
	 * @returns {go.customfields.FieldDialog}
	 */
	getDialog () {
		return new go.customfields.type.DataDialog();
	},

	/**
	 * Render's the custom field value for the detail views
	 * 
	 * @param {mixed} value
	 * @param {object} data Complete entity
	 * @param {object} customfield Field entity from custom fields
	 * @returns {unresolved}
	 */
	renderDetailView (value, data, customfield) {
		return this.renderJsonValue(JSON.parse(value)).join('<br>');
	},

	/**
	 * Returns config oject to create the form field 
	 * 
	 * @param {object} customfield customfield Field entity from custom fields
	 * @param {object} config Extra config options to apply to the form field
	 * @returns {Object}
	 */
	createFormFieldConfig (customfield, config) {
		return ;
	},

	renderJsonValue(data) {
		var html = [];
		if(data === null) {
			html.push('<i>null</i>');
		} else if(Ext.isString(data) && data.match(/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/)) {
			html.push(go.util.Format.dateTime(data));
		} else if(Ext.isArray(data)) {
			let list = '<ul style="padding-left: 10px;">';
			for(var i = 0 ; i < data.length; i++) {
				list += '<li>'+this.renderJsonValue(data[i])+'</li>';
			}
			html.push(list+'</ul>')
		} else if(typeof data === 'object') {
			//	html.push.apply(html, this.renderJsonValue(data));
			for(var key in data) {
				html.push('<b>' + key + '</b> ' + this.renderJsonValue(data[key]));
			}
		} else {
			html.push(data);
		}
		return html;
	},


});

// go.customfields.CustomFields.registerType(new go.customfields.type.TextArea());
