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
	 
	//Ext.apply(this, config);	
	
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
//					this.emailEditor.htmlEditor.syncValue(); // From HTML to SOURCE
					this.htmlEditor.pushValue(); // From SOURCE to HTML
				}
			}
		},this);
		
// EXT ALREADY DOES THIS IN BasicForm.js
//		formPanel.form.on('beforeaction', function(form, action){
//			if(action.type=='submit'){
//				
//				//make sure we are in wysiwyg mode.
//				//won't toggle if not done twice...
////				if(this.sourceEditMode){
////					this.htmlEditor.toggleSourceEdit(false);
////					this.htmlEditor.toggleSourceEdit(false);
////				}else
////					{
////						//extra syncvalue because we disable it on every keypress.
////						this.htmlEditor.syncValue();	
////					}
////				
//				//extra syncvalue because we disable it on every keypress.
//				if(!this.htmlEditor.sourceEditMode){
//					this.htmlEditor.syncValue();
//				}
//			}
//		}, this);		
			
	}, this);
	
	this.addEvents({
		submitshortcut : true
	});
};

Ext.extend(GO.base.email.EmailEditorPanel, Ext.Panel, {
	
	// [ [url:"",tmp_file:"relative/path"]]
	inlineAttachments : [],
	
	originalValue : "",
	
	maxAttachmentsSize : 0,
	
	afterLoad : function(action){
		
		if(action.result.data.inlineAttachments)
			this.setInlineAttachments(action.result.data.inlineAttachments);
		
		if(action.result.data.attachments)
			this.setAttachments(action.result.data.attachments);		
		
		this.setOriginalValue();
	},
	
	focus : function(){
		if(this.getContentType()=='html'){
			return this.htmlEditor.focus();
		}else	{
			//focus textarea at beginning
			var elem = this.textEditor.getEl().dom;
			if(elem.createTextRange) {
				var range = elem.createTextRange();
				range.move('character', 0);
				range.select();
			}
			else {
				if(elem.selectionStart) {
					elem.focus();
					elem.setSelectionRange(0, 0);
				}
				else
					elem.focus();
			}
		}
	},

	
	getActiveEditor : function(){
		if(this.getContentType()=='html')
			return this.htmlEditor;
		else
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
		
		var anchorHeight = config.enableSubjectField ? "-" + dp(32) : "100%";


		this.htmlEditor = new GO.form.HtmlEditor({
			name:'htmlbody',
			hideLabel: true,
			headingsMenu: false,
			anchor: '100% '+anchorHeight,
			plugins:this.initHtmlEditorPlugins(),
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

		if (!GO.util.empty(config.enableSubjectField))
			config.items.push({
				xtype: 'textfield',
				name: 'subject',				
				anchor: '100%',
				allowBlank: false,
				fieldLabel: t("Subject")
			});

		config.items.push(this.htmlEditor);
		config.items.push(this.textEditor);
//		console.log(this.maxAttachmentsSize);

		

		this.attachmentsView = new GO.base.email.EmailEditorAttachmentsView({
			autoHeight:true,
			maxSize:config.maxAttachmentsSize,
			listeners:{
				render:function(){
					//reset this element on render of last element.
					this.setContentTypeHtml(true);
				},
				maxsizeexceeded : function(av, maxSize, totalSize){
					
					GO.errorDialog.show(av.getMaxSizeExceededErrorMsg());
				},
				attachmentschanged:function(av){
					this.setEditorHeight();
					var records = av.store.getRange();
					
					this.attachments=[];
					for(var i=0, l=records.length;i<l;i++) {
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

		this.attachmentsView.addFiles([this.addBlob(blob)]);
	},

	addBlob: function(b) {
		return {
			human_size: Ext.util.Format.fileSize(b.size),
			extension: b.name.split('.').pop().toLowerCase(),
			size: b.size,
			type: b.type,
			name: b.name,
			fileName: b.name,
			from_file_storage: false,
			tmp_file: b.tmp_file,
			blobId: b.id
		}
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
		
//		if(html)
//			this.insertDefaultFont();
//		else
//			this.textEditor.selectText(0,0);
//		
		if(!html)
			this.textEditor.selectText(0,0);

	//this.editor = html ? this.htmlEditor : this.textEditor;
	},

//	insertDefaultFont : function(){
//		var font = this.htmlEditor.fontSelect.dom.value;
//		var v = this.htmlEditor.getValue();
//		if(v.toLowerCase().substring(0,5)!='<font'){
//			if(v=='')
//				v='<br />';
//			
//			v='<font face="'+font+'">'+v+'</font>'
//		}
//
//		this.htmlEditor.setValue(v);		
//	},


	
	setEditorHeight : function() {

		var height=0;
		
		var attachmentsEl = this.attachmentsView.getEl();
		attachmentsEl.setHeight("auto");
		var attachmentsElHeight = attachmentsEl.getHeight();
		
		if(attachmentsElHeight > dp(89))
		{
			attachmentsElHeight = dp(89);
			this.attachmentsView.getEl().setHeight(attachmentsElHeight);
		}			
		height += attachmentsElHeight+attachmentsEl.getMargins('tb')  + dp(24);
		
		if(this.enableSubjectField)
			height+=dp(32);
		
	
		
		var newAnchor = "100% -"+height;
		
		//reset anchor and delete cached anchorSpec
		this.htmlEditor.anchor=newAnchor;
		delete this.htmlEditor.anchorSpec;
		
		this.textEditor.anchor=newAnchor;
		delete this.textEditor.anchorSpec;
		
		this.htmlEditor.syncSize();
		this.ownerCt.doLayout();
	},

	// doLayout: function() {
		
	// 	GO.base.email.EmailEditorPanel.superclass.doLayout.call(this, arguments);
	// 	this.htmlEditor.syncSize();
	// },
	
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
			for(var i=0;i<attachments.length;i++)
				this.attachments.push({
					tmp_file: attachments[i].tmp_file,
					from_file_storage:false,
					fileName: attachments[i].name
				});

			this.attachmentsView.store.loadData({
				results:attachments
			});

			this.hiddenAttachmentsField.setValue(Ext.encode(this.attachments));
		}else
		{
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
						var items = [];
						for (var i = 0; i < blobs.length; i++) {
							items.push({
								human_size: Ext.util.Format.fileSize(blobs[i].size),
								extension: blobs[i].name.split('.').pop().toLowerCase(),
								size: blobs[i].size,
								type: blobs[i].type,
								name: blobs[i].name,
								fileName: blobs[i].name,
								from_file_storage: false,
								blobId: blobs[i].blobId
							});
						}
						this.attachmentsView.addFiles(items);
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
					items.push({
						human_size: Ext.util.Format.fileSize(selections[i].data.size),
						extension: selections[i].data.extension,
						size: selections[i].data.size,
						type: selections[i].data.type,
						name: selections[i].data.name,
						fileName: selections[i].data.name,
						from_file_storage: true,
						tmp_file: selections[i].data.path,
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
	}
});



