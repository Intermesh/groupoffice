/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: DisplayPanel.js 19345 2015-08-25 10:11:22Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.DisplayPanel=function(config){
	config = config || {};

	this.templateConfig = {};
	this.collapsibleSections = {};

	GO.DisplayPanel.superclass.constructor.call(this, config);

	this.addEvents({bodyclick:true,afterbodyclick:true,afterload:true, 'reset':true});
}

Ext.extend(GO.DisplayPanel, Ext.Panel,{
	link_id : 0, //for backwards comaptibility
	model_id: 0,
	model_name : "",

	cls : 'go-display-panel',
	
	isDisplayPanel : true,
	
	newMenuButton : false,
	
	template : '',
	
	templateConfig : {},
	
	loadParams : {},
	
	idParam : 'id',
	
	loadUrl : '',
	
	data : {},
	
	saveHandlerAdded : false,

	noFileBrowser : false,

	collapsibleSections : {},

	hiddenSections : [],

	expandListenObject : false,

	editGoDialogId : false,

	loading:false,
	
	createTopToolbar : function(){
		
		this.newMenuButton = new GO.NewMenuButton({
			panel:this
		});
		
		var tbar=[];
		tbar.push(this.editButton = new Ext.Button({
				iconCls: 'btn-edit', 
				text: GO.lang['cmdEdit'], 
				cls: 'x-btn-text-icon', 
				handler:this.editHandler, 
				scope: this,
				disabled : true
			}));

		tbar.push(this.newMenuButton);
		
		if(GO.documenttemplates)
		{
			this.newOODoc = new GO.documenttemplates.NewOODocumentMenuItem();
			this.newOODoc.on('create', function(){
				this.reload();
			}, this);

			this.newMenuButton.menu.add(this.newOODoc);		
		}
		
		if (!this.noLinkBrowser) {
			tbar.push(this.linkBrowseButton = new Ext.Button({
				iconCls: 'btn-link', 
				cls: 'x-btn-text-icon', 
				text: GO.lang.cmdBrowseLinks,
				handler: function(){
					if(!GO.linkBrowser){
						GO.linkBrowser = new GO.LinkBrowser();
					}
					GO.linkBrowser.show({model_id: this.data.id,model_name: this.model_name,folder_id: "0"});
					GO.linkBrowser.on('hide', this.reload, this,{single:true});
				},
				scope: this
			}));
		}
		
		if(GO.files && !this.noFileBrowser)
		{
			tbar.push(this.fileBrowseButton = new GO.files.FileBrowserButton({
				model_name:this.model_name
			}));
		}
		
		tbar.push('-');
		tbar.push({            
	      iconCls: "btn-refresh",
	      text:GO.lang.cmdRefresh,      
				tooltip:GO.lang.cmdRefresh,      
	      handler: this.reload,
	      scope:this
	  });
	  tbar.push({            
	      iconCls: "btn-print",
	      text:GO.lang.cmdPrint,
				tooltip:GO.lang.cmdPrint,      
	 			handler: function(){
					this.body.print({title:this.getTitle()});
				},
				scope:this
	  });
	  
	  return tbar;
	},

	initTemplate : function(){

	},
	
	initComponent : function(){
		this.autoScroll=true;
		this.split=true;
		var tbar = this.createTopToolbar();
	
		if(Ext.isArray(tbar)){
			tbar = new Ext.Toolbar({				
				enableOverflow:true,
				items:tbar
			});
		}
		
		if(tbar)
			this.tbar = tbar;

		this.initTemplate();

		this.templateConfig.panel=this;

		/*
		 * id is the dom id of the element that needs to hidden or showed
		 * dataKey is a reference name for the data that needs to be fetched from
		 * the server. The hidden sections will be sent as an array of dataKeys.
		 * The server can use this array to check if particular data needs be
		 * returned to the client.
		 */
		
		this.templateConfig.collapsibleSectionHeader = function(title, id, dataKey,extraClassName){
			this.panel.collapsibleSections[id]=dataKey;
			
			var extraclassname = '';
			
			if(typeof(extraClassName)!='undefined')
				extraclassname = extraClassName;
			
			return '<div class="collapsible-display-panel-header '+extraclassname+'"><div style="float:left">'+title+'</div><div class="x-tool x-tool-toggle" style="float:right;margin:0px;padding:0px;cursor:pointer" id="toggle-'+id+'">&nbsp;</div></div>';
		}
		
		this.modifyTemplate();

		this.xtemplate = new Ext.XTemplate(this.template, this.templateConfig);
		this.xtemplate.compile();
		
		GO.DisplayPanel.superclass.initComponent.call(this);

		if(!this.expandListenObject){
			this.expandListenObject=this;
		}

		this.expandListenObject.on('expand', function(){
			if(this.collapsedLinkId){
				this.load(this.collapsedLinkId, true);
				delete this.collapsedLinkId;
			}
		}, this);
		
		this.addEvents({commentAdded:true});
	},
	
	modifyTemplate : function(){
		// Can be overridden so the template can be edited outside this object
	},
	
	
	afterRender : function(){		
		
		GO.DisplayPanel.superclass.afterRender.call(this);

		var tbar = this.getTopToolbar();
		if(tbar)
			tbar.setDisabled(true);

		this.body.on('click', this.onBodyClick, this);

		if(this.editGoDialogId){
			GO.dialogListeners.add(this.editGoDialogId,{
				scope:this,
				save:this.onSave
			});
		}
	},
	
	getLinkName : function(){
		return Ext.util.Format.htmlDecode(this.data.name);
	},
	
	onSave : function(panel, saved_id)
	{
		/*if(saved_id > 0 && this.data.id == saved_id)
		{
			this.reload();
		}*/
		//if(saved_id > 0 && (this.model_id == saved_id || this.model_id==0)){
		
		if(saved_id > 0 &&  this.model_id==0){
			this.load(saved_id, true);
		}
	},
	
	gridDeleteCallback : function(config){
		if(this.data)
		{
			var keys = Ext.decode(config.params.delete_keys);				
			for(var i=0;i<keys.length;i++)
			{
				if(this.data.id==keys[i])
				{
					this.reset();
				}
			}
		}
	},
	
	reset : function(){
		if(this.body)
			this.body.update("");		

		this.data={};
		this.model_id=this.link_id=this.collapsedLinkId=0;
		var tbar = this.getTopToolbar();
		if(tbar)
			tbar.setDisabled(true);
		
		this.fireEvent('reset', this);
	},

	getState : function(){
		return Ext.apply(GO.DisplayPanel.superclass.getState.call(this) || {}, {hiddenSections:this.hiddenSections});
	},
	
	updateToolbar : function(){
		var tbar = this.getTopToolbar();
		if(tbar)
			tbar.setDisabled(false);

		if(this.editButton)
			this.editButton.setDisabled(this.data.permission_level<GO.permissionLevels.write);
		
		
		if(this.newMenuButton){
			if(this.data.permission_level>=GO.permissionLevels.write)
			{
				this.newMenuButton.setLinkConfig({
					model_id:this.data.id,
					model_name:this.model_name,
					text: this.getLinkName(),
					action_date: GO.util.empty(this.actionDate) ? "" : this.actionDate, // Actopm date is used in Contacts with Comments module
					callback:this.reload,
					scope:this
				});
			}else
			{
				this.newMenuButton.setDisabled(true);
			}
		}
		
		if(this.fileBrowseButton)
		{
			this.fileBrowseButton.setId(this.data.id);
		}
	},
	
	setData : function(data)
	{
		//this.body.removeAllListeners();
		
		data.model_name=this.model_name.replace(/\\/g,"\\\\");
		data.model_name_underscores = this.model_name.replace(/\\/g,"_")
		data.panelId=this.getId();
		
		// Action date is used in Contacts with Comments module.
		if (!GO.util.empty(data['action_date']))
			this.actionDate = data['action_date'];
		else
			this.actionDate = '';
		
		this.data=data;
		
		this.updateToolbar();
		
		this.xtemplate.overwrite(this.body, data);

		for(var id in this.collapsibleSections){
			if(this.hiddenSections.indexOf(this.collapsibleSections[id])>-1){
				this.toggleSection(id, true);
			}
		}

		//
		
		//this.body.on('click', this.onBodyClick, this);
	},

	toggleSection : function(toggleId, collapse){

		var el = Ext.get(toggleId);
		var toggleBtn = Ext.get('toggle-'+toggleId);

		if(!toggleBtn)
			return false;
		
		var saveState=false;
		if(typeof(collapse)=='undefined'){
			collapse = !toggleBtn.hasClass('go-tool-toggle-collapsed');// toggleBtn.dom.innerHTML=='-';
			saveState=true;
		}

		
		if(collapse){
			//data not loaded yet			

			if(this.hiddenSections.indexOf(this.collapsibleSections[toggleId])==-1)
				this.hiddenSections.push(this.collapsibleSections[toggleId]);
		}else
		{
			var index = this.hiddenSections.indexOf(this.collapsibleSections[toggleId]);
			if(index>-1)
				this.hiddenSections.splice(index,1);
		}

		if(!el && !collapse){
			this.reload();
		}else
		{
			if(el)
				el.setDisplayed(!collapse);

			if(collapse){
				toggleBtn.addClass('go-tool-toggle-collapsed');
			}else
			{
				toggleBtn.removeClass('go-tool-toggle-collapsed');
			}
			//dom.innerHTML = collapse ? '+' : '-';
		}
		if(saveState)
			this.saveState();
	},

	/*collapsibleSectionHeader : function(title, id, dataKey){

		this.collapsibleSections[id]=dataKey;

		return '<div class="collapsible-display-panel-header">'+title+'<div class="x-tool x-tool-toggle" style="float:right;cursor:pointer" id="toggle-'+id+'" title="'+title+'">&nbsp;</div></div>';
	},*/

	
	onBodyClick :  function(e, target){

		this.fireEvent('bodyclick', this, target, e);

		if(target.id.substring(0,6)=='toggle'){
			var toggleId = target.id.substring(7,target.id.length);

			this.toggleSection(toggleId);
		}

		if(target.tagName!='A')
		{
			target = Ext.get(target).findParent('A', 10);
			if(!target)
				return false;
		}	
		
		if(target.tagName=='A')
		{
			
			
			var href=target.attributes['href'].value;
			if(GO.email && href.substr(0,6)=='mailto')
			{
				var indexOf = href.indexOf('?');
				if(indexOf>0)
				{
					var email = href.substr(7, indexOf-8);
				}else
				{
					var email = href.substr(7);
				}				

				e.preventDefault();
				
				GO.email.addressContextMenu.showAt(e.getXY(), email);					
				//this.fireEvent('emailClicked', email);			
			}else 
			{			
				var pos = href.indexOf('#link_');
				if(pos>-1)
				{
					var index = href.substr(pos+6, href.length);		
					var link = this.data.links[index];			
					if(link.model_name=='folder')
					{
						GO.linkBrowser.show({model_id: link.parent_model_id,model_name: link.parent_model_name,folder_id: link.id});
					}else
					{
						if(!GO.linkHandlers[link.model_name]){
							GO.errorDialog.show(GO.lang.handlerNotInstalled,GO.lang.moduleNotInstalled);
						} else {
							GO.linkHandlers[link.model_name].call(this, link.model_id, {data: link});
						}
					}
					e.preventDefault();
					return;
				}

				pos = href.indexOf('#files_');
				if(pos>-1)
				{
					var index = href.substr(pos+7, href.length);
					var file = this.data.files[index];

					if(file.extension=='folder')
					{
						GO.files.openFolder(this.data.files_folder_id, file.id);
					}else
					{
						if(GO.files){
							//GO.files.openFile({id:file.id});
							file.handler.call(this);
						}else
						{
							window.open(GO.url("files/file/download",{id:file.id}));
						}
					}
					e.preventDefault();
					return;
				}

				if(href.indexOf('#browselinks')>-1){

					if(!GO.linkBrowser){
						GO.linkBrowser = new GO.LinkBrowser();
					}
					GO.linkBrowser.show({model_id: this.data.id,model_name: this.model_name,folder_id: "0"});
					GO.linkBrowser.on('hide', this.reload, this,{single:true});
					e.preventDefault();

					return;
				}
				
				if(href.indexOf('#showalllinks')>-1){
					this.loadParams['links_limit']=0;
					this.reload();
					delete this.loadParams['links_limit'];
				
				}

				if(href.indexOf('#browsefiles')>-1){

					GO.files.openFolder(this.data.files_folder_id);
					GO.files.fileBrowserWin.on('hide', this.reload, this, {single:true});
					e.preventDefault();

					return;
				}

				this.fireEvent('afterbodyclick', this, target, e, href);


				/*if(href!='#')
				{
					if(href.substr(0,6)=='callto')
						document.location.href=href;
					else
						window.open(href);
				}*/
				
			}
		}		
	},
	
	_commentsWithActionDate : false,
	
	_toggleActionDate : function() {
		this._commentsWithActionDate = this.model_name == 'GO\\Addressbook\\Model\\Contact';
		this.actionDateField.setDisabled(!this._commentsWithActionDate);
		this.actionDateField.setVisible(this._commentsWithActionDate);
	},
	
	afterLoad : function(loadResponseData) {
	
		if (GO.comments && this.data.comments.length>0) {
			
		  this.newCommentPanel = new Ext.form.FormPanel({			
			  renderTo: 'newCommentForModelDiv_'+this.model_name.replace(/\\/g,"_")+'_'+this.data.id,
			  layout: 'form',
			  border: false,

			  items: [this.commentsField = new Ext.form.TextArea({
				  name: 'comments',
				  anchor: '90%',
				  height: 70,
				  hideLabel:true,
				  allowBlank:false,
				  emptyText: GO.comments.lang['newCommentText']
			  }),
				this.categoriesCB = new GO.comments.CategoriesComboBox(),
				this.actionDateField = new Ext.form.DateField({
					name: 'action_date',
					fieldLabel: GO.comments.lang['actionDate'],
					format : GO.settings['date_format'],
					disabled: true
				}),
					  new Ext.Button({
						  text: GO.lang.cmdAdd,
						  handler: function(){
							  this.newCommentPanel.form.submit({
								  url: GO.url('comments/comment/submit'),
								  params: {
//										comments : this.commentsField.getValue(),
//										category_id : this.categoriesCB.getValue(),
										withActionDate: this._commentsWithActionDate,
									  model_id : this.model_id,
									  model_name : this.model_name
								  },
								  success:function(form, action){
									  if (!GO.util.empty(action.result.feedback))
										  Ext.MessageBox.alert('', action.result.feedback);
									  this.load(this.model_id,true);
										this.fireEvent('commentAdded');
								  },
								  scope: this
							  });
						  },
						  scope: this
					  })
			  ]
		  });
			
			this._toggleActionDate();
			if (!GO.util.empty(loadResponseData['action_date']))
				this.actionDateField.setValue(loadResponseData['action_date']);
			else
				this.actionDateField.setValue();
			
		}
		
		this.fireEvent('afterload',this.model_id);
		
	},
	
	load : function(id, reload)
	{
		if(this.loading && id==this.model_id)
			return false;
		
		if(this.expandListenObject.collapsed || !this.rendered){
			//model_id is needed for editHandlers
			this.collapsedLinkId=this.model_id=id;
		}else//else if(this.model_id!=id || reload)
		{
			this.loading=true;

			this.loadParams[this.idParam]=this.model_id=this.link_id=id;
			this.loadParams['hidden_sections']=Ext.encode(this.hiddenSections);
			
			GO.request({
				maskEl:this.body,
				method:'GET',
				url: this.loadUrl,
				params:this.loadParams,
				success: function(options, response, result)
				{				
					this.setData(result.data);
					if(!reload)
						this.body.scrollTo('top', 0);
					
					this.stopLoading.defer(300, this);
					
					this.afterLoad(result.data);
					
					
					this.fireEvent('load',this, this.model_id);
				},
				scope: this			
			});
		}
	},
	
	stopLoading : function(){
		this.loading=false;
	},

	setTitle : function(title){
		if(typeof(this.title)!='undefined'){
			GO.DisplayPanel.superclass.setTitle.call(this, title);
		}else if(this.ownerCt)
		{
			//we are in a window
			this.ownerCt.setTitle(title);
		}
	},

	getTitle : function(){
		if(typeof(this.title)!='undefined'){
			return this.title;
		}else if(this.ownerCt)
		{
			//we are in a window
			return this.ownerCt.title;
		}
		return false;
	},
	
	reload : function(){
		if(this.data.id)
			this.load(this.data.id, true);
	},
	
	editHandler : function(){
		
	}
});

Ext.reg('displaypanel',GO.DisplayPanel);

// TODO: Idea for a kind of displaymanager that keeps track of the panels by a specific model.
// 
// Maybe this must be generated through PHP???
// 		
//GO.DisplayManager = function(){
//	var templateBlocks = [];
//	var newMenuItems = [];
//
//	return {
//		/**
//		 * Registers a component.
//		 * @param {Ext.Component} c The component
//		 */
//		addTemplateBlock : function(title, template){
//			templateBlocks.add({title:title, template: template});
//		},
//		
//		getTemplateBlocks : function(){
//			return this.templateBlocks;
//		},
//		
//		/**
//		 * Registers a component.
//		 * @param {Ext.Component} c The component
//		 */
//		addNewMenuItem : function(item){
//			newMenuItems.add(item);
//		},
//		
//		getNewMenuItems : function(){
//			return this.newMenuItems;
//		}
//	}
//}
