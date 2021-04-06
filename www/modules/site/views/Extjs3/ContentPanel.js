GO.site.ContentPanel = Ext.extend(Ext.form.FormPanel, {
	// Plugins for the editor
	editorLinkInsertPlugin: false,
	editorImageInsertPlugin: false,
	editorTablePlugin: false,
	parentPanel: false,
	contentDialog: false,
	submitAction: 'update',
	setSiteId: function(siteId) {
		this.form.baseParams.site_id = siteId;
		
		
		this.paster.model_id=siteId;

		if (this.fileBrowseButton) {
			this.fileBrowseButton.setId(siteId);
//					this.editorImageInsertPlugin.setSiteId(action.result.data.site_id);
//					this.editorLinkInsertPlugin.setSiteId(action.result.data.site_id);
		}
	},
	load: function(contentId) {
		this.setContentId(contentId);
		this.ownerCt.getLayout().setActiveItem(this);
		this.form.load({
			method: 'GET',
			url: GO.url('site/content/update'),
			success: function(form, action) {


				this.setSiteId(action.result.data.site_id);
			},
			scope: this
		});
	},
	create: function(siteId, parentId) {
		this.setSiteId(siteId);
		this.setContentId(0, parentId);
		this.form.baseParams.parent_id = parentId;

		this.form.load({
			method: 'GET',
			url: GO.url('site/content/create'),
			success: function(form, action) {
				this.titleField.focus();
			},
			scope: this
		});


		this.ownerCt.getLayout().setActiveItem(this);
	},
	setContentId: function(contentId, parentId) {
		this.form.baseParams.id = contentId;
		this.advancedButton.setDisabled(!contentId);
		this.viewButton.setDisabled(!contentId);

		delete this.form.baseParams.parent_id;

		if (!contentId) {
			this.form.reset();
			this.submitAction = 'create';
		} else
		{
			this.submitAction = 'update';
		}
	},
	constructor: function(config) {
		config = config || {};

		config.id = 'site-content';
//		config.title = t("Content", "site");
		config.layout = 'form';
		config.border = false;
		config.url = GO.url('site/content/update');
		config.baseParams = {
			id: false
		}

//		config.bodyStyle='padding:5px';
		config.labelWidth = 60;


		this.saveButton = new Ext.Button({
			iconCls: 'btn-save',
			itemId: 'save',
			text: t("Save", "site"),
			cls: 'x-btn-text-icon'
		});

		this.saveButton.on("click", function() {
			// submit the content
			this.form.submit({
				url: GO.url('site/content/' + this.submitAction),
				waitMsg: t("Saving..."),
				success: function(form, action) {
					this.setContentId(action.result.id);
					this.parentPanel.rebuildTree(true); // Rebuild the tree after submit
				},
				failure: function(form, action) {
					if (action.failureType == 'client')
						Ext.MessageBox.alert(t("Error"), t("You have errors in your form. The invalid fields are marked."));
					else
						Ext.MessageBox.alert(t("Error"), action.result.feedback);
				},
				scope: this
			});
		}, this);

		this.advancedButton = new Ext.Button({
			iconCls: 'btn-settings',
			itemId: 'advanced',
			text: t("Advanced options", "site"),
			cls: 'x-btn-text-icon'
		});

		this.advancedButton.on("click", function() {
			this.showContentDialog(this.form.baseParams.id);

		}, this);
		
		
		
		this.viewButton = new Ext.Button({
			iconCls: 'btn-view',
			itemId: 'view',
			text: t("View"),
			cls: 'x-btn-text-icon',
			handler : function(){
				window.open(GO.url('site/content/redirect', {content_id: this.form.baseParams.id}));	
			},
			scope:this
		});
		
		
		

		config.tbar = new Ext.Toolbar({
//			hideBorders:true,
			style: 'margin-bottom:10px;',
			items: [
				this.saveButton,
				this.advancedButton,
				this.viewButton
			]
		});

		if(go.Modules.isAvailable("legacy", "files")) {
			config.tbar.add(this.fileBrowseButton = new GO.files.FileBrowserButton({
				model_name: "GO\\Site\\Model\\Site"
			}));
		}

		this.titleField = new Ext.form.TextField({
			name: 'title',
			anchor:'100%',
			maxLength: 255,
			allowBlank: false,
			fieldLabel: t("Title", "site")
		});

		this.parentSlug = new Ext.form.TextField({
			name: 'parentslug',
			flex: 2,
			maxLength: 255,
			allowBlank: true,
			disabled: true
		});

		this.slugField = new Ext.form.TextField({
			name: 'baseslug',
			flex: 1,
			maxLength: 255,
			allowBlank: true,
			fieldLabel: t("Slug", "site")
		});

		this.completeSlug = new Ext.form.CompositeField({
			fieldLabel: t("Slug", "site"),
			items: [this.parentSlug, this.slugField],
			anchor:'100%'
		});

		this.titleField.on('change', function(field) {
			this.slugField.setValue(this.formatSlug(field.getValue()));
		}, this);

		this.editor = new Ext.form.TextArea({
			hideLabel: true,
			style: 'font-family: "Lucida Console", Monaco, monospace;padding:10px;line-height:16px;',
			name: 'content',
			anchor: '100% -80',
			allowBlank: true,
			fieldLabel: t("Content", "site"),
			listeners: {
				render: function() {
					
					this.paster = new GO.base.upload.Paster({
						pasteEl: this.editor.getEl(),
						model_name:'GO\\Site\\Model\\Site',
						model_id:0, //will be updated in this.setSiteId
						scope:this,
						callback:function(paster, result, xhr){
						
							var tag;
							tag = "{site:thumb path=\"" + result.path + "\" lw=\"300\" ph=\"300\"}";

							this.editor.insertAtCursor(tag);
						}
					});

					var editor = this.editor;

					var contentDD = new Ext.dd.DropTarget(this.editor.getEl(), {
						// must be same as for tree
						ddGroup: 'site-tree',
						notifyDrop: function(dd, e, node) {
							
							
							if(node.node && node.node.attributes.slug){
								//dragged from content tree
								var markdown = '['+node.node.text+'](slug://' + node.node.attributes.slug + ')';

								editor.insertAtCursor(markdown);
							
							}else{
								//dragged from file browser
								
								if(node.grid){
									
									var record = node.selections[0].data;
									
									if(record.extension=='folder'){
										return false;
									}
									
									var pos = record.path.indexOf('files/');
									
									var markdown = '['+record.name+'](file://'+record.path.substring(pos+6,record.path.length)+')';
									
									editor.insertAtCursor(markdown);
								}
								
								
							}
							return true;
							
						}
					});
					
					contentDD.addToGroup('FilesDD');

				},
				scope: this
			}
		});



		config.items = [
			this.titleField,
			this.completeSlug,
			this.editor
		];
		GO.site.ContentPanel.superclass.constructor.call(this, config);
	},
	
	showContentDialog: function(id) {
		if (!this.contentDialog) {
			this.contentDialog = new GO.site.ContentDialog();
			this.contentDialog.on('hide', function() {
				this.form.load();
			}, this);
		}
		this.contentDialog.show(id);
	},
	formatSlug: function(slug) {

		slug = slug.toLowerCase();
		slug = slug.replace(/[^a-z0-9]+/g, '-');
		slug = slug.replace(/^-|-$/g, '');

		return slug;
	}
//	initHtmlEditorPlugins : function(htmlEditorConfig) {		
//		// insertLink plugin
//		this.editorLinkInsertPlugin = new GO.site.HtmlEditorLinkInsert({toolbarPosition : 17,toolbarSeparatorAfter:true});
//		
//		// optional image attachment
//		this.editorImageInsertPlugin = new GO.site.HtmlEditorImageInsert({toolbarPosition : 19,toolbarSeparatorAfter:true});
//		this.editorTablePlugin = new Ext.ux.form.HtmlEditor.Table();
//			
//		return [this.editorLinkInsertPlugin,this.editorImageInsertPlugin,this.editorTablePlugin, new Ext.ux.form.HtmlEditor.HeadingMenu()];
//	}
});

