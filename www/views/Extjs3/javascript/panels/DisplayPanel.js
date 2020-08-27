/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: DisplayPanel.js 22372 2018-02-13 14:47:17Z mschering $
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

	cls : 'go-display-panel go-detail-view',
	
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

	showLinks: true,

	showFiles: true,

	showComments: true,
	
	createTopToolbar : function(){
//		
//		this.newMenuButton = new GO.NewMenuButton({
//			panel:this
//		});
//		
//		
		
		var tbar=['->'];
		
		
		
//		
//		if (!this.noLinkBrowser) {
//			tbar.push(this.linkBrowseButton = new Ext.Button({
//				iconCls: 'btn-link', 
//				cls: 'x-btn-text-icon', 
//				text: t("Links"),
//				handler: function(){
//					if(!GO.linkBrowser){
//						GO.linkBrowser = new GO.LinkBrowser();
//					}
//					GO.linkBrowser.show({model_id: this.data.id,model_name: this.model_name,folder_id: "0"});
//					GO.linkBrowser.on('hide', this.reload, this,{single:true});
//				},
//				scope: this
//			}));
//		}
		
//		if(GO.files && !this.noFileBrowser)
//		{
//			tbar.push(this.fileBrowseButton = new GO.files.FileBrowserButton({
//				model_name:this.model_name
//			}));
//		}
		
//		tbar.push('-');
//		tbar.push({            
//	      iconCls: "btn-refresh",
//	      text:t("Refresh"),      
//				tooltip:t("Refresh"),      
//	      handler: this.reload,
//	      scope:this
//	  });

		tbar.push(this.editButton = new Ext.Button({
				iconCls: 'btn-edit', 
				tooltip: t("Edit"), 
				cls: 'x-btn-text-icon', 
				handler:this.editHandler, 
				scope: this,
				disabled : true
			}), 
			
		
		this.addButton = this.newMenuButton = new go.detail.addButton({			
			detailView: this,
			noFiles: this.noFileBrowser
				})
		);

		this.moreButton = new Ext.Button({
			iconCls: 'ic-more-vert',
			menu:[
				{
					iconCls: "ic-print",
					
					text:t("Print"),      
					handler: function(){
						this.body.print({title:this.getTitle()});
					},
					scope:this
				},{            
					iconCls: "btn-refresh",
					text:t("Refresh"),      
					handler: this.reload,
					scope:this
				}
			]
		});
		
		
		if(go.Modules.isAvailable("legacy", "files") && !this.noFileBrowser) {

			tbar.push({
				xtype: "detailfilebrowserbutton"
			});
		}
		tbar.push({
			xtype: "linkbrowserbutton"
		});
		
		tbar.push(this.moreButton);

	  return tbar;
	},

	initTemplate : function(){

	},
	
	initComponent : function(){
		
		this.autoScroll=true;
		this.split=true;
		var tbar = this.createTopToolbar();
	
//		if(Ext.isArray(tbar)){
//			tbar = new Ext.Toolbar({				
//				enableOverflow:true,
//				items:tbar
//			});
//		}
		
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
		this.templateConfig.defaultFormatFunc = false;
		this.xtemplate = new Ext.XTemplate(this.template, this.templateConfig);
		
		this.xtemplate.compile();
		
		
		this.mainItem = new Ext.Panel({
			listeners: {
				render: function() {
					this.mainItem.body.on('click', this.onBodyClick, this);
				},
				scope: this

			}
		});
		
		this.items = [this.mainItem];
		
		GO.DisplayPanel.superclass.initComponent.call(this);
		
		
		if(this.model_name) {
			var parts = this.model_name.split("\\");
			this.entity = parts[3];
		}
		
		if (this.showLinks) {
			
			this.add(go.links.getDetailPanels());
		}

		if(go.Modules.isAvailable("legacy", "files")) {
			if (this.showFiles && !this.noFileBrowser) {
                this.add(new go.modules.files.FilesDetailPanel());
            }
		}
		
		if (this.showComments && go.Modules.isAvailable("community", "comments")) {
			this.add(new go.modules.comments.CommentsDetailPanel());
		}
		
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
		
		
		this.items.each(function (item, index, length) {
			item.hide();
		}, this);
		

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

		if(this.mainItem.rendered) {
			this.mainItem.update("");
		}

		this.data={};
		this.model_id=this.link_id=this.collapsedLinkId=0;
		var tbar = this.getTopToolbar();
		if(tbar)
			tbar.setDisabled(true);
		
		this.items.each(function (item, index, length) {
			item.hide();
		}, this);
		
		this.fireEvent('reset', this);
	},

	getState : function(){
		return Ext.apply(GO.DisplayPanel.superclass.getState.call(this) || {}, {hiddenSections:this.hiddenSections});
	},
	
	updateToolbar : function(){
		var tbar = this.getTopToolbar();
		if(tbar)
			tbar.setDisabled(false);

		if(this.editButton && this.editButton.rendered)
			this.editButton.setDisabled(this.data.permission_level<GO.permissionLevels.write);
		
		
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
		
		if(this.mainItem.body) { // TODO: this will unly render it the second time
			this.xtemplate.overwrite(this.mainItem.body, data);
		}
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
		
		if(target.tagName=='A' && target.attributes['href'])
		{			
			var href=target.attributes['href'].value;
			if(href.substr(0,3)=='go:')
			{
				var fn = href.substr(3);

				eval("this." + fn);

				e.preventDefault();				
			}else 
			{
				this.fireEvent('afterbodyclick', this, target, e, href);
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
	
		
		this.fireEvent('afterload',this.model_id);
		
	},
	
	load : function(id, reload)
	{
		this.fireEvent('beforeload',this, id);
		
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
					this.onLoad();
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
	
	//for compatibility with new detail view panels
	onLoad : function() {
		this.items.each(function(item, index, length){
			item.show();
			
			if(index == 0) {
				return;
			}
			
			if(item.tpl) {
				item.update(this.data);
			}
			if (item.onLoad) {
				item.onLoad.call(item, this);
			}
		},this);
		this.doLayout();
		this.body.scrollTo('top', 0);
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
