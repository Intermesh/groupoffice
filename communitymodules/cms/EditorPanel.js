GO.cms.EditorPanel = Ext.extend(
	function( cfg ){
			
			
			
		var languages={};
			
		for(var i=0;i<GO.Languages.length;i++)
		{
			languages[GO.Languages[i][0]]=GO.Languages[i][1];
		}
			
		var spellchecker_languages="+"+languages[GO.settings.language]+"="+GO.settings.language;
			
		for(var iso in languages)
		{
			if(iso!=GO.settings.language)
				spellchecker_languages+=','+languages[iso]+'='+iso;
		}

		this.editorConfig = {
			name:'content',
			tinymceSettings:{
				mode : "textareas",
				theme : "advanced",
				plugins : "spellchecker,safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

				// Theme options
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,code,|,forecolor,backcolor",
				theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,spellchecker,media,advhr,|,ltr,rtl,|,fullscreen",
				file_browser_callback : 'GO.cms.fileBrowser',

				//theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : false,

				convert_urls:false,
				relative_urls : false,

				spellchecker_languages : spellchecker_languages,
				spellchecker_rpc_url : BaseHref+'javascript/tiny_mce/plugins/spellchecker/rpc.php',

				extended_valid_elements : "cms:plugin[*]"

			}
		};
			
		this.editor = new Ext.ux.TinyMCE(this.editorConfig);
		
		this.optionsPanel = new GO.cms.TemplateOptionsPanel();
		this.eastFormPanel = new Ext.Panel({
			layout:'form',
			labelAlign:'top',
			border:false,
			bodyStyle:'padding: 5px',
				title: GO.lang.strProperties,
				width: '100%',
				waitMsgTarget:true,
				autoScroll:true,
				defaults: {
					anchor: '-20',
					listeners:{
						change: function(){
							this.dirty=true;
						},
						scope: this
					}
				},
				defaultType: 'textfield',
				items: [{
					fieldLabel:GO.lang.strName,
					name:'name',
					allowBlank:false		
				},{
					xtype:'datefield',
					format:GO.settings.date_format,
					fieldLabel:GO.cms.lang.showUntil,
					name:'show_until',
					allowBlank:true,
					emptyText:GO.cms.lang.alwaysShow
				},{
					hideLabel:true,
					xtype:'checkbox',
					checked: true,
					name:'auto_meta',
					boxLabel:GO.cms.lang.autoMeta,
					listeners:{
						check:function(cb, checked){

							this.setAutoMeta(checked);
						},
						scope:this
					}
				},{
					xtype:'datefield',
					format:GO.settings.date_format,
					fieldLabel:GO.cms.lang.sortDate,
					name:'sort_date',
					allowBlank:true,
					emptyText:GO.cms.lang.unused
				},{
					fieldLabel:GO.cms.lang.title,
					xtype:'textarea',
					name:'title',
					height:60
				},{
					fieldLabel:GO.lang.strDescription,
					xtype:'textarea',
					name:'description',
					height:80
				},{
					fieldLabel:GO.cms.lang.keywords,
					xtype:'textarea',
					name:'keywords',
					height:80
				},
				this.optionsPanel]
			});
		
		this.fileCategoriesTree = new GO.cms.FileCategoriesTree();
		
		this.eastPanel = new Ext.TabPanel({
			region: 'east',
			activeTab: 0,      
      deferredRender: false,
    	border: false,
      items: [
				this.eastFormPanel,
				this.fileCategoriesTree
			],
      width:320
		})
		
		this.eastPanel.on('afterrender',function(eastPanel){
			this.eastPanel.hideTabStripItem(1);
		},this);
		
		var config = {
			disabled:true,
			waitMsgTarget: true,
			url: GO.settings.modules.cms.url+'json.php',
			baseParams:{
				task:'file'
			},
			region:'center',
			border:false,
			labelAlign:'top',
			items:{
				anchor:'100% 100%',
				layout:'border',
				border:false,
				items:[
					{
						region:'center',
						layout:'fit',
						border:false,
						items:this.editor
					},
					this.eastPanel
				]
			}
		};
			
		this.optionsPanel.on('htmlTemplateSelected', function(record){
			//tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent()+record.get('html'));
			tinyMCE.execCommand('mceInsertContent',false,record.get('html'));
		}, this);
			
		Ext.apply( config, cfg );

		
		GO.cms.EditorPanel.superclass.constructor.call( this, config );
			
		this.addEvents({
			'save' :true,
			disabled: true,
			load : true
		});
			
	},

	Ext.FormPanel, {
			
		files_folder_id : 0,
		root_folder_id : 0,

		currentTemplate : '',
			
		dirty : false,
			
		file_id : 0,
				
		setEastPanel : function(enable_categories,file_id) {
			if (enable_categories!=1) {
				this.eastPanel.hideTabStripItem(1);
				this.eastPanel.setActiveTab(0);
			} else {
				this.eastPanel.unhideTabStripItem(1);
				this.fileCategoriesTree.load(file_id);
			}
		},
		
		loadFile : function(file_id, template)
		{
			this.setDisabled(false);
				
			if(this.currentTemplate!=template)
			{					
				var t = tinyMCE.activeEditor.dom;
					
				this.currentTemplate = template;
				t.remove(t.select('link'));				
				if(template!='')
					t.add(t.select('head')[0], 'link', {
						rel : 'stylesheet',
						href : GO.settings.modules.cms.url+'editor.css'
						});
					t.add(t.select('head')[0], 'link', {
						rel : 'stylesheet',
						href : GO.settings.modules.cms.url+'templates/'+template+'/css/editor.css'
						});

				/*this.editorConfig.content_css = GO.settings.modules.cms.url+'editor.css,'+GO.settings.modules.cms.url+'templates/'+template+'/css/editor.css';
				tinyMCE.execCommand('mceRemoveControl',true,tinyMCE.activeEditor);
				tinyMCE.init(this.editorConfig);
				tinyMCE.execCommand('mceAddControl',true,tinyMCE.activeEditor);*/

			}
				
			this.file_id=file_id;
			this.baseParams.file_id=file_id;
				
			if(file_id>0)
				this.baseParams.folder_id=0;
					
			this.dirty=false;

			
				
			this.load({
				success:function(form, action)
				{
					this.root_folder_id=action.result.data.root_folder_id;
					this.files_folder_id=action.result.data.files_folder_id;

					if(action.result.data.folder_id)
					{
						this.baseParams.folder_id=action.result.data.folder_id;
					}
						
					this.setAutoMeta(action.result.data.auto_meta=='1');

					this.optionsPanel.loadConfig(
						action.result.data.config,
						action.result.data.option_values,
						action.result.data.type);

					this.setEastPanel(action.result.data.enable_categories,action.result.data.id);

					this.fireEvent('load', this);
				},
				failure: function(form, action) {
					Ext.MessageBox.alert(GO.lang.strError, action.result.feedback);
					this.fireEvent('load', this);
				},
				scope: this
					
			});
		},
		saveFile : function(){
			tinyMCE.triggerSave();

			GO.mainLayout.getModulePanel('cms').getEl().mask(GO.lang.waitMsgSave);
			
			this.form.submit(
			{
				url:GO.settings.modules.cms.url+'action.php',
				params: {
					'task' : 'save_file'
				},
				//waitMsg:GO.lang['waitMsgSave'],
				success:function(form, action){
					if(action.result.file_id)
					{
						this.baseParams.file_id=action.result.file_id;
																							
					}
						
					if(action.result.title)
						this.form.findField('title').setValue(action.result.title);

					if(action.result.keywords)
						this.form.findField('keywords').setValue(action.result.keywords);
						
					if(action.result.description)
						this.form.findField('description').setValue(action.result.description);

					if(action.result.files_folder_id)
						this.files_folder_id=action.result.files_folder_id;
						
					this.dirty=false;
					this.editor.ed.isNotDirty = 1;
						
					this.fireEvent('save', this.baseParams.file_id, this.form.getValues(), this.baseParams.folder_id);
					GO.mainLayout.getModulePanel('cms').getEl().unmask();
				},
				failure: function(form, action) {
					if(action.failureType == 'client')
					{
						Ext.MessageBox.alert(GO.lang['strError'], GO.lang['strErrorsInForm']);
					} else {
						Ext.MessageBox.alert(GO.lang['strError'], action.result.feedback);
					}
					GO.mainLayout.getModulePanel('cms').getEl().unmask();
				},
				scope: this
			});
		},
		newFile : function(folder_id, template){
			this.form.reset();
			this.baseParams.folder_id=folder_id;
			this.loadFile(0, template);
		},
		setDisabled : function(disabled)
		{
			this.fireEvent('disabled', disabled);
				
			GO.cms.EditorPanel.superclass.setDisabled.call(this, disabled);
		},
			
		isDirty : function(){
				
			return this.dirty || this.editor.isDirty();
		},
		setAutoMeta : function(checked)
		{
			var f = this.form;
									
			f.findField('title').setDisabled(checked);
			f.findField('description').setDisabled(checked);
			f.findField('keywords').setDisabled(checked);
		}
				
	});
		
GO.cms.createFileBrowser = function(root_folder_id, filter, fileClickHandler, files_folder_id){
	
	
	if(!GO.cms.fb)
	{
		GO.cms.fb = new GO.files.FileBrowser({
			border:false,
			treeCollapsed:false,
			filePanelCollapsed:true
		});
		
		GO.cms.fileBrowserWindow = new Ext.Window({
			
			title: GO.lang.strSelectFiles,
			height:500,
			width:750,
			layout:'fit',
			border:false,
			collapsible:true,
			maximizable:true,
			closeAction:'hide',
			items: GO.cms.fb,
			buttons:[
			{
				text: GO.lang.cmdOk,
				handler: function(){
					GO.cms.fb.fileClickHandler();
				},
				scope: this
			},{
				text: GO.lang.cmdClose,
				handler: function(){
					GO.cms.fileBrowserWindow.hide();
				},
				scope:this
			}
			]
							        				
		});
	}
	
	GO.cms.fb.setFilesFilter(filter);		
	GO.cms.fb.setFileClickHandler(fileClickHandler, this);	
	GO.cms.fileBrowserWindow.buttons[0].setVisible(fileClickHandler!=false);	
		
	if(!files_folder_id)
	{
		files_folder_id=root_folder_id;
	}

	GO.cms.fb.setRootID(root_folder_id, files_folder_id);
	GO.cms.fileBrowserWindow.show();
}

GO.cms.fileBrowser = function(field_name, url, type, win){
	//console.log("Field_Name: " + field_name + "\nURL: " + url + "\nType: " + type + "\nWin: " + win);
	
	GO.cms.popupWin = win;
	GO.cms.popupField = field_name;

	if(type=='image')
	{
		GO.cms.createFileBrowser(GO.cms.editorPanel.root_folder_id, 'png,jpg,jpeg,gif,bmp', function(){
			var items = GO.cms.fb.getSelectedGridRecords();						
			GO.cms.popupWin.document.getElementById(GO.cms.popupField ).value=GO.settings.modules.files.full_url+'download.php?id='+items[0].data.id;
			GO.cms.fileBrowserWindow.hide();
		});
			
	}else
	{
		if(!GO.cms.selectFile)
		{
			GO.cms.selectFile = new GO.cms.SelectFile();
			
			GO.cms.selectFile.on('fileselected', function(nodeAttr){
				GO.cms.popupWin.document.getElementById(GO.cms.popupField ).value="/{site_url}?site_id="+nodeAttr.site_id+"&path="+nodeAttr.path;
			}, this);
		}
			
		GO.cms.selectFile.show();
	}
};