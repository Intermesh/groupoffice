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
				values.map(a => '<a target="_blank" href="' + go.Jmap.downloadUrl(a.blobId, true) + '">' + a.name + '</a>').join(", ");
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
			cls:'x-portlet card',
			height: 150
		});

		return Object.assign(this.supr().createFormFieldConfig.call(this, customfield, config), {
			xtype:'panel',
			frame:true,
			cls:'x-portlet',
			height: 350,
			collapsible:true,
			layout:'fit',
			tools:[{
				id:'add',
				handler: (e,dom,pnl) => {
					go.util.openFileDialog({
						multiple: customfield.options.multiFileSelect, // We do not yet support multiple file upload
						accept: customfield.options.accept,
						directory: false, // We do not yet support directories
						autoUpload: true,
						listeners: {
							upload: (data) => {
								const s = pnl.items.itemAt(0).store,
									r = new s.recordType({blobId: data.blobId, name: data.name, size: data.size, type: data.type,modifiedAt:data.modifiedAt });
								s.add(r);
							},
							uploadComplete: () => { pnl.items.itemAt(0).getEl().unmask();},
							select: () => { pnl.items.itemAt(0).getEl().mask(t('Uploading...')) },
						}
					});
				}
			}],
			//title:customfield.name,
			// bbar: ['->',
			// 	{text:t('Upload'), iconCls: 'ic-upload', handler: (btn) => {
			// 			go.util.openFileDialog({
			// 				multiple: customfield.options.multiFileSelect, // We do not yet support multiple file upload
			// 				accept: customfield.options.accept,
			// 				directory: false, // We do not yet support directories
			// 				autoUpload: true,
			// 				listeners: {
			// 					upload: (data) => {
			// 						const s = btn.findParentByType('panel').items.itemAt(0).store,
			// 						 	r = new s.recordType({blobId: data.blobId, name: data.name, size: data.size, type: data.type,modifiedAt:data.modifiedAt });
			// 						s.add(r);
			// 					},
			// 					uploadComplete: () => { btn.findParentByType('panel').items.itemAt(0).getEl().unmask();},
			// 					select: () => { btn.findParentByType('panel').items.itemAt(0).getEl().mask(t('Uploading...')) },
			// 				}
			// 			});
			// 		}}
			// ],
			items: {
				xtype:'dataview',
				store: {
					xtype:'arraystore',
					fields: ['blobId', 'name','description', 'size','type','modifiedAt'],
					data: []
				},
				tpl: '<div style="overflow-x:hidden" tabindex="0" class="go-attachments"><tpl for="."><div class="filetype-link filetype-{[values.name.split(\'.\').pop()]}" title="{modifiedAt}">\
					{name} ({[go.util.humanFileSize(values.size)]})\
				</div></tpl></div>',
				autoHeight: true,
				selectable: false,
				emptyText: '<div class="go-dropzone">'+t('Empty')+'</div>',
				itemSelector: 'a'
			}
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