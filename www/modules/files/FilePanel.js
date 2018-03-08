/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: FilePanel.js 20453 2016-09-22 13:40:32Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.files.FilePanel = Ext.extend(GO.DisplayPanel,{
	model_name : "GO\\Files\\Model\\File",

	noFileBrowser:true,

	stateId : 'fs-file-panel',

	/*
	 *Can be filled by other modules to display extra info
	 */
	extraTemplateProperties : '',

	editGoDialogId : 'file',

	editHandler : function(){

		//browsers don't like loading a json request and download dialog at the same time.'
		if(this.loading)
		{
			this.editHandler.defer(200, this);
		}else
		{	
			//GO.files.openFile({id:this.data.id});
			this.data.handler.call(this);
		}
	},

	createTopToolbar : function(){
		var tbar = GO.files.FilePanel.superclass.createTopToolbar.call(this);
				
		this.editButton.setText(GO.files.lang.open);

		tbar.splice(1,0,this.downloadButton= new Ext.Button({
			iconCls: 'btn-save',
			text: GO.lang.download,
			cls: 'x-btn-text-icon',
			handler: function(){
				GO.files.downloadFile(this.model_id);
			},
			scope: this
		}),this.propertiesBtn = new Ext.Button({
			iconCls: 'btn-settings',
			text: GO.lang.strProperties,
			cls: 'x-btn-text-icon',
			handler: function(){
				GO.files.showFilePropertiesDialog(this.model_id+"");
				//this.addSaveHandler(GO.files.filePropertiesDialog);
			},
			scope: this
		}));

		return tbar;
	},

	reset : function(){
		GO.files.FilePanel.superclass.reset.call(this);
//		this.setTitle('&nbsp;');
	},

	setData : function(data)
	{
		GO.files.FilePanel.superclass.setData.call(this, data);
//		this.setTitle(data.name);		
		this.editButton.setDisabled(data.locked || !this.data.write_permission);	
		
		//custom fields pass path as ID and it will be looked up by the controller. So we must set the actual ID here.
		//see actionDisplay in FileController
		this.model_id=this.data.id;

		this.propertiesBtn.setDisabled(!this.data.write_permission);
	},

	initComponent : function(){
		
		this.on('bodyclick',function(panel,target, e){
			
			target = Ext.get(target);
			
			if(target.hasClass("fs-unlock")){
				GO.request({
					url:'files/file/submit',
					params:{
						id:this.data.id,
						locked_user_id:0
					},
					success:function(action, response, result){
						this.reload();
						var filesModulePanel = GO.mainLayout.getModulePanel('files');
						if(filesModulePanel && filesModulePanel.folder_id==this.data.folder_id)
							filesModulePanel.getActiveGridStore().load();
						if (!GO.util.empty(GO.files.fileBrowser))
							GO.files.fileBrowser.gridStore.load();
						if (!GO.util.empty(GO.selectFileBrowser))
							GO.selectFileBrowser.gridStore.load();
					},
					scope:this
				})
			}
			
			if(target.hasClass("fs-deleteDL")){
				
			var answer = confirm(GO.files.lang.deleteDownloadLink);
			if(answer){
				
					GO.request({
						url:'files/file/submit',
						params:{
							id:this.data.id,
							expire_time:0,
							random_code:null
						},
						success:function(action, response, result){
							this.reload();
							var filesModulePanel = GO.mainLayout.getModulePanel('files');
							if(filesModulePanel && filesModulePanel.folder_id==this.data.folder_id)
								filesModulePanel.getActiveGridStore().load();
						},
						scope:this
					})
				}
			}
			
			
		}, this);
		
		this.loadUrl=('files/file/display');
		
		this.template =

				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
//					'<tr>'+
//						'<td width="120">'+GO.files.lang.path+':</td>'+
//						'<td>{path}</td>'+
//					'</tr>'+
					'<tr>'+
						'<td colspan="2" class="display-panel-heading">'+GO.files.lang.file+': {path}</td>'+
					'</tr>'+
					
					'<tr>'+
						'<td>ID</td><td>{id}</td>'+
					'</tr>'+
					
					'<tr>'+
						'<td>'+GO.lang.strType+':</td>'+
						'<td colspan=><div class="go-grid-icon filetype filetype-{extension}">{type}</div></td>'+						
					'</tr>'+

					'<tr>'+
						'<td>'+GO.lang.strSize+':</td>'+
						'<td>{size}</td>'+
						
					'</tr>'+

					'<tr>'+
						'<td>'+GO.lang.strCtime+':</td>'+
						'<td>{ctime}</td>'+
						
					'</tr>'+

					'<tr>'+
						'<td>'+GO.lang.strMtime+':</td>'+
						'<td>{mtime}</td>'+						
					'</tr>'+

					'<tr>'+
						'<td>'+GO.lang['strUser']+':</td>'+'<td>{username}</td>'+
					'</tr><tr>'+
						'<td>'+GO.lang['mUser']+':</td>'+'<td>'+
							'<tpl if="muser_id">{musername}</tpl>'+
							'</td>'+
					'</tr>'+
					
					'<tr>'+
						'<td>URL:</td>'+
						'<td><a target="_blank" href="{url}">'+GO.files.lang.rightClickToCopy+'</a></td>'+
					'</tr>'+
										
					'<tpl if="!GO.util.empty(locked_user_name)">'+
						'<tr>'+
            '<td>'+GO.files.lang.lockedBy+':</td>'+
            '<td><div class="go-grid-icon btn-lock">{locked_user_name}'+
						'<tpl if="unlock_allowed">'+
							' <span class="fs-unlock" style="cursor:pointer;text-decoration:underline;">['+GO.files.lang.unlock+']</span>'+
						'</tpl>'+
						'</div></td>'+
						'</tr>'+
          '</tpl>'+
					

          '<tpl if="!GO.util.empty(expire_time)">'+
						'<tr>'+
							'<td colspan="2" class="display-panel-heading">'+GO.files.lang.strDownloadActive+'</td>'+
						'</tr>'+
						'<tr>'+
            '<td style="white-space:nowrap">'+GO.files.lang.downloadExpireTime+':</td>'+
            '<td>{expire_time}</td>'+
						'</tr>'+
						
						'<tr>'+
            '<td>'+GO.files.lang.downloadUrl+':</td>'+
            '<td><a href="{download_link}" target="_blank">'+GO.files.lang.rightClickToCopy+'</a>'+
						//'<tpl if="unlock_allowed">'+
							' <span class="fs-deleteDL" style="cursor:pointer;text-decoration:underline;">['+GO.files.lang.deletedDownloadLink+']</span>'+
						//'</tpl>'+
						'</td>'+
						'</tr>'+
						
						'<tpl if="!GO.util.empty(delete_when_expired)">'+
							'<tr>'+
								'<td colspan="2"><span style="color:red;">'+GO.files.lang['automaticallyDeleted']+'</span></td>'+
							'</tr>'+
						'</tpl>'+
						
          '</tpl>'+
					
					'<tpl if="!GO.util.empty(content_expire_date)">'+
						'<tr>'+
            '<td>'+GO.files.lang.contentExpiresAt+':</td>'+
						
						'<tpl if="GO.files.isContentExpired(content_expire_date) == false">'+
							'<td><span>{content_expire_date}</span></td>'+
						'</tpl>'+
						'<tpl if="GO.files.isContentExpired(content_expire_date)">'+
							'<td><span class="content-expired">{content_expire_date}</span></td>'+
						'</tpl>'+
						'</tr>'+
          '</tpl>'+

					'<tpl if="!GO.util.empty(thumbnail_url)"><tr><td colspan="2">'+
						'<img src="{thumbnail_url}" style="max-width:100px;max-height:100px;" />'+
					'</td></tr></tpl>'+

					this.extraTemplateProperties +

					/*'<tr>'+
						'<td>'+GO.lang.Atime+'</td>'+
						'<td>{atime}</td>'+
					'</tr>'+*/

					'<tpl if="!GO.util.empty(comment)">'+
						'<tr>'+
							'<td colspan="2" class="display-panel-heading">'+GO.files.lang.comments+'</td>'+
						'</tr>'+
						'<tr>'+
							'<td colspan="2">{comment}</td>'+
						'</tr>'+
					'</tpl>'+
				'</table>';


	

		if(GO.customfields)
		{
			this.template +=GO.customfields.displayPanelTemplate;
		}

		if(GO.tasks)
			this.template +=GO.tasks.TaskTemplate;

		if(GO.calendar)
			this.template += GO.calendar.EventTemplate;
		
		if(GO.workflow)
			this.template +=GO.workflow.WorkflowTemplate;

		if(GO.lists)
			this.template += GO.lists.ListTemplate;

		this.template += GO.linksTemplate;
		
		Ext.apply(this.templateConfig, GO.linksTemplateConfig);

		if(GO.comments)
		{
			this.template += GO.comments.displayPanelTemplate;
		}

		GO.files.FilePanel.superclass.initComponent.call(this);
	}
});