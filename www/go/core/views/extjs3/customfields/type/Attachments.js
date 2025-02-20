Ext.ns("go.customfields.type");

go.customfields.type.Attachments = Ext.extend(go.customfields.type.Text, {
	
	name : "Attachments",
	label: t("Attachments"),
	iconCls: "ic-attachment",

	getDialog() {
		return new go.customfields.type.AttachmentsDialog();
	},

	renderDetailView(values, data, customfield) {
		if (!values || !values.length) {
			return "";
		}

		if (!customfield.options.accept || customfield.options.accept.indexOf("image") === -1) {
			return '<i class="icon ic-attachment"></i> ' +
				values.map(a => {
					let s = '<a target="_blank" href="' + go.Jmap.downloadUrl(a.blobId, true) + '">' + (a.description || a.name);

					s += '</a>';

					return s;
				}).join(", ");
		}

		let r = `<div class="x-panel card x-panel-noborder">
			<div class="x-panel-bwrap"><div class="x-panel-body x-panel-noborder x-panel-noheader">
			<div style="height: auto;">`;
		for (const v of values) {
			let c = v.description;
			if (go.util.empty(c)) {
				c = v.name;
			}
			r += `<div class="fs-thumb-wrap" id="${v.name}">
				<div class="fs-thumb" title="${c}" style="background-image:url(${go.Jmap.thumbUrl(v.blobId, {
				w: 480,
				h: 270,
				zc: 1
			})});cursor: pointer;" 
				onclick="window.open('${go.Jmap.downloadUrl(v.blobId, true)}')">
				&nbsp;</div>
				<span class="x-editable">${Ext.util.Format.ellipsis(c, 20)}</span>
				</div>`;
		}
		r += `<div class="x-clear"></div></div></div></div></div>`;

		return r;
	},

	createFormFieldConfig (customfield, config) {

		return Object.assign(this.supr().createFormFieldConfig.call(this, customfield, config), {
			xtype: 'attachmentfield',
			hasDescription: true,
			cls:'x-portlet card',
			height: 150
		});

	},

	getFieldType () {
		return "auto";
	},

	getFieldDefinition(field) {

		const c = this.supr().getFieldDefinition.call(this, field);

		c.convert = function(v, record) {
			return this.customFieldType.renderDetailView(v, record.data, this.customField);
		};		
		
		return c;
	},
	
	getColumn(field) {
		return Object.assign(this.supr().getColumn.call(this, field),{sortable:false});
	},
	
	getFilter(field) {

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