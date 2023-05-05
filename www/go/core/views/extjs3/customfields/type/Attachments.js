Ext.ns("go.customfields.type");

go.customfields.type.Attachments = Ext.extend(go.customfields.type.Text, {
	
	name : "Attachments",
	label: t("Attachments"),
	iconCls: "ic-attachment",

	getDialog() {
		return new go.customfields.type.AttachmentsDialog();
	},

	renderDetailView (values, data, customfield) {
		//debugger; // also used for column render
		if(!values || !values.length) {
			return "";
		}
		return '<i class="icon ic-attachment"></i> '+ values.length;
		// let filesdetial = new go.modules.files.FilesDetailPanel();
		// var options = [];
		// values.forEach(function(value){
		// 	var opt = customfield.dataType.options.find(o => o.id == value);
		//
		// 	if(opt) {
		// 		options.push(opt.text);
		// 	}
		// });
		//
		// return options.join(", ");
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
		
		var c = this.supr().getFieldDefinition.call(this, field);
		
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