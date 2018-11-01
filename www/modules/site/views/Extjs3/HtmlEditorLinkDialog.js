/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: HtmlEditorLinkDialog.js 8376 2011-10-24 09:55:16Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.HtmlEditorLinkDialog = Ext.extend(GO.Window , {
	
	_panels : [],
	
	generatedTag : '',
	
	linkTitle : '',
	selectedUrl : '',
	contentId : false,
	siteId: false,
	
	initComponent : function(){
		
		this.buttonOk = new Ext.Button({
			text: t("Ok"),
			handler: function(){
				this.generateTag();
				
				this.fireEvent('insert');
				this.hide();
			},
			scope: this
		});

		this.buttonClose = new Ext.Button({
			text: t("Close"),
			handler: function(){
				this.clearTag();
				this.hide();
			},
			scope:this
		});
		
		Ext.apply(this, {
			goDialogId:'linkEditor',
			title:t("Insert link", "site"),
			height:410,
			width:600,
			layout:'fit',
			modal:true,
			buttons: [this.buttonOk,this.buttonClose]
		});

		this.buildForm();
		
		this.formPanelConfig=this.formPanelConfig || {};
		this.formPanelConfig = Ext.apply(this.formPanelConfig, {
			waitMsgTarget:true,			
			border: false,
			baseParams : {},
			layout:'fit'
		});
		
		this.formPanel = new Ext.form.FormPanel(this.formPanelConfig);

		if(this._panels.length > 1 || this.forceTabs) {		    
			this._tabPanel = new Ext.TabPanel({
				activeTab: 0,
				enableTabScroll:true,
				deferredRender: false,
				border: false,
				anchor: '100% 100%',
				items: this._panels
			});
		    
			this.formPanel.add(this._tabPanel);
		} else if (this._panels.length===1) {			

			delete this._panels[0].title;
			this._panels[0].header=false;
			if(this._panels[0].elements)
				this._panels[0].elements=this._panels[0].elements.replace(',header','');

			this.formPanel.add(this._panels[0]);
		}
		
		this.items=this.formPanel;				
	
		GO.site.HtmlEditorLinkDialog.superclass.initComponent.call(this);		
	},
				
	buildForm : function(){
		
		this.contentTreePanel = new GO.site.HtmlEditorContentTreePanel({
			region:'west',
			width:180,
//			height:183,
			border:true
		});
		
		this.contentTreePanel.on('dblclick',function(node, event){
			node.select();
			if(this.contentTreePanel.isContentNode(node)){
				this.fillUrlField('content:'+node.attributes.content_id,t("Content", "site")+': '+node.attributes.text)
			}
			
		},this);
		
		this.contentTreePanelText = new GO.form.HtmlComponent({
			html: '<p class="go-form-text">'+t("Link to a content item within this site. Doubleclick on an item to select it.", "site")+'</p>'
		});
		
		this.fileButton = new Ext.Button({
			text: t("Select file", "site"),
			name:'select_file'
		});
		
		this.fileButton.on('click',function(){
			
			GO.request({
				url:'files/folder/checkModelFolder',
				params:{								
					mustExist:true,
					model:'GO\\Site\\Model\\Site',
					id:this.siteId
				},
				success:function(response, options, result){														
					GO.files.createSelectFileBrowser();
					GO.selectFileBrowser.setFileClickHandler(this.setUrlFromImage, this);
					//GO.selectFileBrowser.setFilesFilter(this.filesFilter);
					GO.selectFileBrowser.setRootID(result.files_folder_id, result.files_folder_id);
					GO.selectFileBrowserWindow.show();
					GO.selectFileBrowserWindow.show.defer(200, GO.selectFileBrowserWindow);
				},
				scope:this
			});			
			
		},this);
		
		this.fileButtonText = new GO.form.HtmlComponent({
			html: '<p class="go-form-text">'+t("Link to a file that you have uploaded within this site.", "site")+'</p>'
		});
		
		this.urlText = new GO.form.HtmlComponent({
			html: '<p class="go-form-text">'+t("You can also type in your own url, please make sure you add http(s):// to it to make the url work.", "site")+'</p>'
		});
		
		this.titleField = new Ext.form.TextField({
			name: 'title',
			anchor: '100%',
			maxLength: 255,
			value: this.linkTitle,
			allowBlank:true,
			fieldLabel: t("Link title", "site"),
			listeners:{
				change:function(oldValue,newValue){
					this.linkTitle = newValue;
				},
				scope:this
			}
		});
		
		this.urlField = new Ext.form.TextField({
			name: 'url',
			anchor: '100%',
			maxLength: 255,
			allowBlank:true,
			value:'http://',
			fieldLabel: t("Url", "site")
		});
		
		this.urlField.on('change',function(){
			this.selectedUrl = '';
		},this);
		
		
		this.openInNewWindowCbx = new Ext.ux.form.XCheckbox({
			hideLabel: false,
			boxLabel: t("Open in new window", "site"),
			name: 'open_in_new_window',
			value: false
		});
	
		this.contentTreeFieldset = new Ext.form.FieldSet({
			title: t("Link to content item", "site"),
			height:297,
			border: true,
			collapsed: false,
			items:[
				this.contentTreePanelText,
				this.contentTreePanel
			]
		});
		
		this.fileFieldset = new Ext.form.FieldSet({
			title: t("Link to file", "site"),
			height:120,
			border: true,
			collapsed: false,
			items:[
				this.fileButtonText,
				this.fileButton
			]
		});
			
		this.labelFieldset = new Ext.form.FieldSet({
			title: t("Url", "site"),
			height:170,
			border: true,
			collapsed: false,
			labelWidth:50,
			items:[
				this.urlText,
				this.urlField,
				this.titleField,
				this.openInNewWindowCbx
			]
		});
		
		this.propertiesPanel = new Ext.Panel({
			labelWidth: 120,
			cls:'go-form-panel',
			layout:'column',
			items:[{
				itemId:'leftCol',
				columnWidth: .5,
				items: [
					this.contentTreeFieldset
				]
			},{
				itemId:'rightCol',
				columnWidth: .5,
				style: 'margin-left: 5px;',
				items: [
					this.fileFieldset,
					this.labelFieldset
				]
			}]
		});

		this.addPanel(this.propertiesPanel);
	},

	setUrlFromImage : function(file){
		this.fillUrlField('file:'+file.data.path,t("File", "site")+': '+file.data.name);
		GO.selectFileBrowserWindow.hide();
	},

	fillUrlField: function(id, label){
		this.selectedUrl = id;
		this.urlField.setValue(label);
	},

	setSiteId : function(id){
		this.siteId = id;
		this.contentTreePanel.setSiteId(this.siteId);
	},
	
	show : function(config){
		this.setSiteId(config.site_id);		
		this.setDefaults();
		GO.site.HtmlEditorLinkDialog.superclass.show.call(this);		
	},
	
	getTag : function(){
		this.generateTag();
		return this.generatedTag;
	},
					
	generateTag : function(){
					
		if(this.selectedUrl.substr(0,8) !== 'content:' && this.selectedUrl.substr(0,5) !== 'file:'){
			var textValue = this.urlField.getValue();
			if(textValue && !this.selectedUrl && textValue != 'http://')
				this.selectedUrl = textValue;
		}
				
		this.clearTag();
		var tag = '';
		var linkType = this.getLinkType(this.selectedUrl);
		var tagLink = '<a  title="'+this.linkTitle+'"';

		tag += '<site:link';
		tag += ' linktype="'+linkType+'"';
		
		switch(linkType){
			case 'content':		
				tag += ' contentid="'+this.selectedUrl.replace('content:','')+'"';
				break;
			case 'file':
				tag += ' path="'+this.selectedUrl.replace('file:','')+'"';
				break;
			case 'manual':
				tag += ' url="'+this.selectedUrl+'"';
				break;
		}

		tag += ' title="'+this.linkTitle+'"';

		tag += ' href="'+this.selectedUrl+'"';
		
		var checked = this.openInNewWindowCbx.checked;
		if(checked){
			tag += ' target="_blank"';
			tagLink += ' target="_blank"';
		}

		tagLink += '>{selectedEditorText}</a>';
		tag += '>';
		tag += tagLink;
		tag += '</site:link>';

		this.generatedTag = tag;
	},
	
	getLinkType : function(input){
		
		if(input.substr(0,8) === 'content:')
			return 'content';
		else if(input.substr(0,5) === 'file:')
			return 'file';
		else
			return 'manual'
	},
	
	clearTag : function(){
		this.generatedTag = '';
	},
	/**
	 * Use this function to add panels to the window.
	 * 
	 * @var relatedGridParamName Set to the field name of the has_many relation. 
	 * eg. Addressbook dialog showing contacts would have this value set to addressbook_id
	 */
	addPanel : function(panel, relatedGridParamName){
		this._panels.push(panel);
	},
	setDefaults : function(){
		this.linkTitle = '';
		this.selectedUrl = '';
		this.urlField.setValue('http://');
		this.titleField.setValue('');
		this.openInNewWindowCbx.setValue(false);
	}
	
});
