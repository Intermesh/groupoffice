/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @author WilmarVB <wilmar@intermesh.nl>
 * @author Merijn Schering <mschering@intermesh.nl>
 */

// parameter attachments must be passed by reference

/**
 * This is necessary in the corresponding controller:
 * 	protected function beforeSubmit(&$response, &$model, &$params) {
		
		$message = new \GO\Base\Mail\Message();
		$message->handleEmailFormInput($params);
		
		$model->content = $message->toString();
		
		return parent::beforeSubmit($response, $model, $params);
	}
	
	
	protected function afterLoad(&$response, &$model, &$params) {
		
		// create message model from client's content field, turned into HTML format
		$message = \GO\Email\Model\SavedMessage::model()->createFromMimeData($model->content);
	
		$response['data'] = array_merge($response['data'], $message->toOutputArray());

		return parent::afterLoad($response, $model, $params);
	}
 */

GO.base.email.EmailEditorPanel = function(config){
	
	config = config || {};

	config.htmlEditorConfig = config.htmlEditorConfig || {};
	
	if(!config.maxAttachmentsSize)
		config.maxAttachmentsSize=GO.settings.config.max_attachment_size;

	this.buildForm(config);
	
	config.layout='form';
	config.border=false;	
	
	GO.base.email.EmailEditorPanel.superclass.constructor.call(this,config);
	
	this.on("render", function(){
		var formPanel = this.findParentByType(Ext.form.FormPanel);
		formPanel.form.on('actioncomplete', function(form, action){
			if(action.type=='load'){
				this.afterLoad(action);
			}
		}, this);		
		
		formPanel.form.on('beforeaction', function(form, action){
			if(action.type=='submit'){
				// When the editor is in sourceEditMode then the value needs to be pushed to the HTML editor before it is saved.
				if(this.htmlEditor.sourceEditMode){
					this.htmlEditor.pushValue(); // From SOURCE to HTML
				}
			}
		},this);

		this.dropTarget = new Ext.dd.DropTarget(this.container,
			{
				ddGroup : 'EmailDD',
				copy:false,
				notifyOver: this.onNotifyOver.createDelegate(this),
				notifyDrop : this.onNotifyDrop.createDelegate(this),
				notifyOut: this.onNotifyOut.createDelegate(this)
			});


	}, this);
	
	this.addEvents({
		submitshortcut : true
	});
};

Ext.extend(GO.base.email.EmailEditorPanel, Ext.Panel, {
	
	inlineAttachments : [],
	
	originalValue : "",
	
	maxAttachmentsSize : 0,
	
	afterLoad : function(action){
		
		if(action.result.data.inlineAttachments) {
			this.setInlineAttachments(action.result.data.inlineAttachments);
		}
		
		if(action.result.data.attachments) {
			this.setAttachments(action.result.data.attachments);
		}
		
		this.setOriginalValue();
	},
	
	focus : function(){
		if(this.getContentType()=='html'){
			return this.htmlEditor.focus();
		} else {
			//focus textarea at beginning
			var elem = this.textEditor.getEl().dom;
			if(elem.createTextRange) {
				var range = elem.createTextRange();
				range.move('character', 0);
				range.select();
			} else {
				if(elem.selectionStart) {
					elem.focus();
					elem.setSelectionRange(0, 0);
				} else {
					elem.focus();
				}
			}
		}
	},

	
	getActiveEditor : function(){
		if(this.getContentType()=='html') {
			return this.htmlEditor;
		}
		return this.textEditor;
	},
	
	buildForm : function(config) {

		config.items = config.items || new Array();				

		this.inlineAttachments = new Array();
		this.hiddenInlineImagesField = new Ext.form.Hidden({
			name: 'inlineAttachments'
		});

		this.attachments = new Array();
		this.hiddenAttachmentsField = new Ext.form.Hidden({
			name: 'attachments'
		});
		
		this.hiddenCtField = new Ext.form.Hidden({
			name: 'content_type',
			value:'html'
		});

		config.items.push(this.hiddenCtField);
		config.items.push(this.hiddenAttachmentsField);
		config.items.push(this.hiddenInlineImagesField);

		const anchorHeight = config.enableSubjectField ? "-" + dp(32) : "100%";

		this.htmlEditor = new GO.form.HtmlEditor({
			mobile: {
				grow: true,
				growMinHeight: 300,
				growMaxHeight: 0,
				anchor: "100%"
			},
			desktop: {
				anchor: '100% '+anchorHeight,
			},
			name:'htmlbody',
			hideLabel: true,
			headingsMenu: false,
			enableDragDrop: true,
			plugins:this.initHtmlEditorPlugins(),
			//this font is applied here because it must match the one in htmleditor.scss. Ext will copy this style to the body tag.
			style: "font: " + dp(16) + "px  Helvetica, Arial, sans-serif",
			getFontStyle :  function() {
				return GO.form.HtmlEditor.prototype.getFontStyle.call(this) + ';color: black';
			},

			getEditorFrameStyle : function() {
				return GO.form.HtmlEditor.prototype.getEditorFrameStyle.call(this) + ' body {background-color: white}';
			}
		});

		this.htmlEditor.on('attach', this.htmlEditorAttach, this);
		
		this.textEditor = new Ext.form.TextArea({
			name: 'plainbody',
			anchor : '100% '+anchorHeight,
			hideLabel : true,
			cls:'em-plaintext-body-field'
		});

		this.dropZone = new Ext.BoxComponent({
			hidden: true,
			tag: 'div',
			cls: 'go-dropzone',
			style: {
				height: "90%"
			},
			html: t("Drop email messages here")
		});


		if (!GO.util.empty(config.enableSubjectField))
			config.items.push({
				xtype: 'textfield',
				name: 'subject',				
				anchor: '100%',
				allowBlank: false,
				fieldLabel: t("Subject")
			});

		config.items.push([this.htmlEditor, this.textEditor, this.dropZone]);

		this.attachmentsView = new GO.base.email.EmailEditorAttachmentsView({
			autoHeight:true,
			maxSize:config.maxAttachmentsSize,
			listeners:{
				render: function(){
					//reset this element on render of last element.
					this.setContentTypeHtml(true);
				},
				maxsizeexceeded : function(av, maxSize, totalSize){
					
					GO.errorDialog.show(av.getMaxSizeExceededErrorMsg());
				},
				attachmentschanged: function(av) {
					this.setEditorHeight();
					const records = av.store.getRange();

					this.attachments=[];
					for(let i=0, l=records.length;i<l;i++) {
						this.attachments.push({
							tmp_file: records[i].data.tmp_file,
							from_file_storage: records[i].data.from_file_storage,
							fileName: records[i].data.name,
							blobId: records[i].data.blobId
						});
					}
					this.hiddenAttachmentsField.setValue(Ext.encode(this.attachments));
				},
				scope:this
			}
		});
		config.items.push(this.attachmentsView);

	},

	htmlEditorAttach : function(htmlEditor,blob, file, img) {
		if(img) {
			//inline will be processed from body
			return;
		}

		this.attachmentsView.addBlob(blob);
	},
	
	reset : function(){
		this.setAttachments();
		this.setInlineAttachments();
		this.setOriginalValue();
	},
	
	getContentType : function(){
		return this.hiddenCtField.getValue();
	},
	
	setContentTypeHtml : function(html){
		this.htmlEditor.getEl().up('.x-form-item').setDisplayed(html);
		this.textEditor.getEl().up('.x-form-item').setDisplayed(!html);
		
		this.hiddenCtField.setValue(html ? 'html' : 'plain');

		if(!html)
			this.textEditor.selectText(0,0);
	},

	setEditorHeight : function() {
		let height = 0;
		const attachmentsEl = this.attachmentsView.getEl();
		attachmentsEl.setHeight("auto");
		let attachmentsElHeight = attachmentsEl.getHeight();
		if(attachmentsElHeight > dp(89)) {
			attachmentsElHeight = dp(89);
			this.attachmentsView.getEl().setHeight(attachmentsElHeight);
		}			
		height += attachmentsElHeight+attachmentsEl.getMargins('tb')  + dp(24);
		
		if(this.enableSubjectField) {
			height += dp(32);
		}

		const newAnchor = "100% -" + height;
		this.resizeEditorFrame(newAnchor);
	},

	initHtmlEditorPlugins : function(htmlEditorConfig) {		
		// optional image attachment
		var imageInsertPlugin = new GO.plugins.HtmlEditorImageInsert();
		imageInsertPlugin.on('insert', function(plugin, path, isTempFile, token) {
			this.inlineAttachments.push({
				tmp_file : path,
				from_file_storage : !isTempFile,
				token:token
			});				
			this.setInlineAttachments(this.inlineAttachments);	
		}, this);
	
		return [imageInsertPlugin, go.form.HtmlEditor.emojiPlugin];
	},
	
	getHtmlEditor : function() {
		return this.htmlEditor;
	},
	
	setInlineAttachments : function(inlineAttachments){
		this.inlineAttachments = inlineAttachments ? inlineAttachments : [];
		this.hiddenInlineImagesField.setValue(Ext.encode(this.inlineAttachments));
	},
	
	setOriginalValue : function(){		
		this.originalValue=this.getActiveEditor().getValue();
	},
	
	isDirty : function(){
		return this.originalValue!=this.getActiveEditor().getValue();
	},
	
	setAttachments : function(attachments){
		this.attachments=[];
		
		if(attachments){
			for(let i=0,l=attachments.length; i<l; i++)
				this.attachments.push({
					tmp_file: attachments[i].tmp_file,
					from_file_storage:false,
					fileName: attachments[i].name
				});

			this.attachmentsView.store.loadData({
				results:attachments
			});

			this.hiddenAttachmentsField.setValue(Ext.encode(this.attachments));
		} else {
			this.attachmentsView.store.loadData({
				results:[]
			});
		}
		this.hiddenAttachmentsField.setValue(Ext.encode(this.attachments));
	},	

	
	getAttachmentsButton : function(){

		var uploadHandle = function() {
			go.util.openFileDialog({
				multiple: true,
				directory: false,
				autoUpload: true,
				maxSize: this.maxAttachmentsSize, // todo
				listeners: {
					uploadComplete: function (blobs) {
						for (var i = 0; i < blobs.length; i++) {
							this.attachmentsView.addBlob(blobs[i]);
						}
					},
					scope: this
				}
			});
		};


		if(!go.Modules.isAvailable("legacy", "files")) {
			return new Ext.Button({
				iconCls:'ic-attach-file',
				tooltip: t("Attach files"),
				handler:uploadHandle,
				scope:this
			});
		}

		var fileBrowserHandle = function(result) {
			GO.files.createSelectFileBrowser();

			GO.selectFileBrowser.setFileClickHandler(function(){

				var items = [],
					selections = GO.selectFileBrowser.getSelectedGridRecords();
				for (var i = 0; i < selections.length; i++) {
					// the name is the full path when searching
					var name = selections[i].data.name.substr(selections[i].data.name.lastIndexOf('/')+1);
					items.push({
						human_size: Ext.util.Format.fileSize(selections[i].data.size),
						extension: selections[i].data.extension,
						size: selections[i].data.size,
						type: selections[i].data.type,
						name: name,
						fileName: name,
						from_file_storage: true,
						tmp_file: selections[i].data.path
					});
				}

				this.attachmentsView.addFiles(items);
				GO.selectFileBrowserWindow.hide();
			}, this);
			GO.selectFileBrowser.setFilesFilter('');

			if(result) {
				GO.selectFileBrowser.setRootID(result.files_folder_id,result.files_folder_id);
			} else {
				GO.selectFileBrowser.setRootID(0,0);
			}
			GO.selectFileBrowserWindow.show();
		}.bind(this);

		return new Ext.Button({
			iconCls:'ic-attach-file',
			tooltip: t("Attach files"),
			menu:[{
				text: t('Upload'),
				iconCls: 'ic-file-upload',
				handler:uploadHandle,
				scope:this
			}, {
				iconCls:'ic-folder',
				text : t("Add from personal folder", "email").replace('{product_name}', GO.settings.config.product_name),
				handler : fileBrowserHandle,
				scope : this
			},{
				iconCls:'ic-folder',
				text : t("Add from item", "email"),
				handler : function() {
					var dlg = new GO.email.LinkAttachmentDialog();
					dlg.setAttachmentHandle(fileBrowserHandle);
					dlg.setAttachmentsView(this.attachmentsView);
					dlg.show(null);
				},
				scope : this
			}]
		});
	},

	onNotifyOver(dd,e,data) {
		// TODO: Determine when to return false

		// Unhide attachments bar if hidden and set a minimum height
		// this.attachmentsView.show();
		/*
		const attachmentsEl = this.attachmentsView.getEl();
		attachmentsEl.setHeight("auto");
		let attachmentsElHeight = attachmentsEl.getHeight();
		const minHeight = 109; // which is the full size of a go_dropzone class

		let avHeight = this.attachmentsView.getHeight();
		if(avHeight < minHeight) {
			avHeight = attachmentsElHeight + attachmentsElHeight+attachmentsEl.getMargins('tb')  + dp(24);
			if(this.enableSubjectField) {
				avHeight += dp(32);
			}
			avHeight = Math.max(avHeight, minHeight);
		}
		const newAnchor = "100% -" + avHeight;

		this.resizeEditorFrame(newAnchor);

		// Highlight attachments bar upon hovering
		if (!this.oldTpl) {
			this.oldTpl = this.attachmentsView.tpl;
			this.attachmentsView.update('<div class="go-dropzone">' + t("Drop email messages here") + '</div>');
		}
		*/
		this.dropZone.style = {height: this.getActiveEditor().getHeight()};
		this.dropZone.show();
		this.getActiveEditor().hide();
		return true;
	},

	onNotifyOut: function(dd,e,data) {
		this.attachmentsView.fireEvent('attachmentschanged', this.attachmentsView);
		this.dropZone.hide();
		this.getActiveEditor().show();
		return true;
	},

	onNotifyDrop: function(dd,e,data) {
		if(!data.grid) {
			this.attachmentsView.fireEvent('attachmentschanged', this.attachmentsView);
			return false;
		}

		const selections = data.grid.getSelectionModel().getSelections();
		for(let i=0,l=selections.length;i<l;i++) {
			const curr = selections[i];

			let attIdx = i;
			if(this.attachments && this.attachments.length) {
				attIdx += this.attachments.length;
			}
			const params = {
				account_id: curr.data.account_id,
				uid: curr.data.uid,
				mailbox: curr.data.mailbox,
				number: attIdx,
				encoding: ''
			}
			GO.request({
				url: 'email/message/saveToBlob',
				params: params,
				scope: this,
				success: function(options, response, data)
				{
					if(data.success) {
						this.attachmentsView.addBlob(data.blob);
					}
					this.dropZone.hide();
					this.getActiveEditor().show();

				},
				failure: function(options, response, data) {
					// this.resetAttachmentsView();
					this.dropZone.hide();
					this.getActiveEditor().show();

				}
			});
		}
		return true;
	},

	resizeEditorFrame: function(anchor)
	{
		anchor = anchor || "100%";

		this.htmlEditor.anchor = anchor;
		delete this.htmlEditor.anchorSpec;

		this.textEditor.anchor = anchor;
		delete this.textEditor.anchorSpec;

		this.htmlEditor.syncSize();
		this.dropZone.style = {height: this.htmlEditor.getHeight()};
		this.ownerCt.doLayout();
	},
	//
	// resetAttachmentsView: function() {
	// 	if(this.oldTpl) {
	// 		this.attachmentsView.update({
	// 			tpl: this.oldTpl
	// 		});
	// 		delete this.oldTpl;
	// 	}
	// 	this.resizeEditorFrame();
	// 	this.attachmentsView.fireEvent('attachmentschanged', this.attachmentsView);
	// }

});
