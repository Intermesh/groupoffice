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
GO.DetailView = Ext.extend(Ext.Panel,{

	data: {},
	entityId: null,
	entityType : null,
	pk : 'id',
	loadParams: {}, // todo remove
	loadUrl : '', // todo remove

	cls : 'go-detail-view',
	//layout:'accordion',
	newMenuButton: false,
	templateConfig : {},
	basePanels: [],
	panels: [], // set in override
	autoScroll:true,

	noLinkBrowser: false,
	noFileBrowser : false,
	editGoDialogId : false,
	loading:false,
	initComponent : function(){
		
		this.tbar = new Ext.Toolbar({
			enableOverflow:true,
			items:this.buildToolbar()
		});

		GO.DetailView.superclass.initComponent.call(this,arguments);
		this.initItems();

		if(GO.lists)
			this.basePanels.push({id:'tplList',tpl: GO.lists.ListTemplate});

		if(GO.customfields) {
			this.basePanels.push({id:'tplCf',title: 'Customfields', tpl: GO.customfields.displayPanelTemplate});
			this.basePanels.push({id:'tplCfBlocks',tpl: GO.customfields.displayPanelBlocksTemplate});
		}

		if(GO.tasks)
			this.basePanels.push({id:'tplTask',tpl: GO.tasks.TaskTemplate});

		if(GO.workflow){
			this.basePanels.push({id:'tplWorkFlow',tpl: GO.workflow.WorkflowTemplate});
		}

		if(GO.calendar)
			this.basePanels.push({id:'tplCalendarEvents',tpl: GO.calendar.EventTemplate});

		this.basePanels.push({id:'tplLinks',tpl: GO.linksTemplate});

		Ext.apply(this.templateConfig, GO.linksTemplateConfig);


		if(GO.files){
			Ext.apply(this.templateConfig, GO.files.filesTemplateConfig);
			this.basePanels.push({id:'tplFiles',tpl: GO.files.filesTemplate});
		}

		if(GO.comments){
			this.basePanels.push({id:'tplComments',tpl: GO.comments.displayPanelTemplate});
		}

		this.basePanels.push({id:'tplCreateModify',title: GO.lang.createModify,tpl: GO.createModifyTemplate});

		for(var p in this.panels) {
			if(this.panels[p].id) {
				this.items.add(new Ext.Panel(this.panels[p]));
			}
		}
		for(var p in this.basePanels) {
			if(this.basePanels[p].tpl) {
				this.items.add(new Ext.Panel({
					id: this.basePanels[p].id,
					title: this.basePanels[p].title,
					tpl: new Ext.XTemplate(this.basePanels[p].tpl, {
						collapsibleSectionHeader: function(title, id, dataKey,extraClassName){
							var extraclassname = '';

							if(typeof(extraClassName)!='undefined')
								extraclassname = extraClassName;

							return '<div class="collapsible-display-panel-header '+extraclassname+'"><div style="float:left">'+title+'</div><div class="x-tool x-tool-toggle" style="float:right;margin:0px;padding:0px;cursor:pointer" id="toggle-'+id+'">&nbsp;</div></div>';
						}
					})
				}));
			}
		}
		this.doLayout();

	},
	buildToolbar : function(){

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

		if(GO.tasks){
			this.scheduleCallItem = new GO.tasks.ScheduleCallMenuItem();
			this.newMenuButton.menu.add(this.scheduleCallItem);
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

	onSave : function(panel, saved_id){
		if(saved_id > 0 &&  this.model_id==0){
			this.load(saved_id, true);
		}
	},

	gridDeleteCallback : function(config){
		if(!this.data) {
			return;
		}

		var keys = Ext.decode(config.params.delete_keys);
		for(var i = 0; i < keys.length; i++) {
			if(this.data.id == keys[i]) {
				this.reset();
			}
		}
		
	},

	reset : function(){
		if(this.body)
			this.body.update("");

		this.data={};
		this.entityId = null;
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
		this.data=data;
		this.updateToolbar();

		this.items.each(function(item, index, length){
			if(item.tpl) {
				item.update(data);
			}
		});
		this.doLayout();
	},

	/**
	 * Magic js function for click events for the view
	 * @param {type} e
	 * @param {type} target
	 */
	onBodyClick :  function(e, target) {

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
			}
		}
	},

	afterLoad : function(loadResponseData) {

		// display panel added comments here

		this.fireEvent('afterload',this.model_id);

	},

	load : function(id, reload)
	{
		if(this.loading && id == this.entityId)
			return false;

		if(!this.rendered){
			//model_id is needed for editHandlers
			this.entityId=id;
		}else//else if(this.model_id!=id || reload)
		{
			this.loading=true;

			this.loadParams[this.pk] = this.entityId = id;

			GO.request({
				maskEl:this.body,
				method:'GET',
				url: this.loadUrl,
				params:this.loadParams,
				success: function(options, response, result) {
					this.setData(result.data);
					if(!reload)
						this.body.scrollTo('top', 0);

					this.stopLoading.defer(300, this);

					this.afterLoad(result.data);


					this.fireEvent('load',this, this.entityId);
				},
				scope: this
			});
		}
	},
	stopLoading : function(){
		this.loading=false;
	},

	reload : function(){
		if(this.data.id)
			this.load(this.data.id, true);
	},

	editHandler : function(){

	}
});

Ext.reg('detailview',GO.DetailView);
