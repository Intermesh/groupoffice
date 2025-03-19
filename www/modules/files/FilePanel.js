/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: FilePanel.js 22151 2018-01-17 13:59:21Z mschering $
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
		GO.files.showFilePropertiesDialog(this.model_id+"");
		
	},

	createTopToolbar: function () {

		const tbar = GO.files.FilePanel.superclass.createTopToolbar.call(this);

		tbar.splice(1, 0, this.downloadButton = new Ext.Button({
				iconCls: 'ic-save',
				tooltip: t("Download"),
				handler: function () {
					GO.files.downloadFile(this.model_id);
				},
				scope: this
			}), this.propertiesBtn = new Ext.Button({
				iconCls: 'ic-launch',
				tooltip: t("Open"),
				handler: function () {
					this.launch();
				},
				scope: this
			}),
			this.shareMenuButton = new Ext.Button({
				iconCls: "ic-share",
				tooltip: t("Share", "files"),
				menu: [
					new Ext.menu.Item({
						iconCls: "ic-open-in-browser",
						text: t("Copy direct link", "files"),
						handler: function () {
							let url = GO.settings.config.full_url + "#" + this.entity.toLowerCase() + "/" + this.model_id;
							go.util.copyTextToClipboard(url);
							Ext.MessageBox.alert(t("Success"), t("Value copied to clipboard"));
						},
						scope: this
					}),
					new Ext.menu.Item({
						iconCls: 'ic-link',
						text: t("Create download link", "files"),
						handler: function(){
							GO.files.createDownloadLink([{data: this.data}],false);
						},
						scope: this
					})
				]
			})
		);
		if (go.Modules.isAvailable("legacy", "email")) {
			this.shareMenuButton.menu.add(
				new Ext.menu.Item({
					iconCls: 'ic-email',
					text: t("Email download link", "files"),
					handler: function () {
						GO.files.createDownloadLink([{data: this.data}],true);
					},
					scope: this
				}),

				new Ext.menu.Item({
					iconCls: 'ic-email',
					text: t("Email files", "email"),
					handler: function () {
						if(this.data.folder_id) {
							this.data.name = String(this.data.name).split('/').reverse()[0];
						}
						GO.email.emailFiles([this.data]);
					},
					scope: this
				}))
			;
		}
		return tbar;
	},

	launch : function() {
		//browsers don't like loading a json request and download dialog at the same time.'
		if(this.loading) {
			this.launch.defer(200, this);
		}else {
			this.data.handler.call(this);
		}		
	},

	reset : function(){
		GO.files.FilePanel.superclass.reset.call(this);
	},

	setData : function(data)
	{
		GO.files.FilePanel.superclass.setData.call(this, data);
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

				const answer = confirm(t("You are going to delete this link, are you sure?", "files"));
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

			if(target.hasClass("go-scrollintoview")){

				const scrollToName = target.getAttribute('data-scrollintoview-el');

				if(!Ext.isEmpty(scrollToName)){
					var p = this.getEl();
					var scrollTo = p.child('.'+scrollToName);
					scrollTo.dom.scrollIntoView({behavior: "smooth", block: "start", inline: "nearest"});
				}				
			}
			
			
		}, this);
		
		this.loadUrl=('files/file/display');
		
		this.template ='<tpl if="!GO.util.empty(thumbnail_url)">\
				<figure style="background-image: url({thumbnail_url});" ></figure>\
					</tpl>' +

				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
						'<td colspan="2" class="display-panel-heading">{path}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+t("Size")+':</td>'+
						'<td>{[values.size==\'-\' ? values.size : Ext.util.Format.fileSize(values.size)]}</td>'+
						
					'</tr>'+

					'<tr>'+
						'<td>'+t("Created at")+':</td>'+
						'<td>{ctime}</td>'+
						
					'</tr>'+

					'<tr>'+
						'<td>'+t("Modified at")+':</td>'+
						'<td>{mtime}</td>'+						
					'</tr>'+

					'<tr>'+
						'<td>'+t("Created by")+':</td>'+'<td>{username}</td>'+
					'</tr><tr>'+
						'<td>'+t("Modified by")+':</td>'+'<td>'+
							'<tpl if="muser_id">{musername}</tpl>'+
							'</td>'+
					'</tr>'+
					
					'<tr>'+
						'<td>URL (Authenticated):</td>'+
						'<td><a target="_blank" href="{url}">'+t("Right click to copy", "files")+'</a></td>'+
					'</tr>'+
										
					'<tpl if="!GO.util.empty(locked_user_name)">'+
						'<tr>'+
            '<td>'+t("Locked by", "files")+':</td>'+
            '<td><div class="go-grid-icon btn-lock">{locked_user_name}'+
						'<tpl if="unlock_allowed">'+
							' <span class="fs-unlock" style="cursor:pointer;text-decoration:underline;">['+t("Unlock", "files")+']</span>'+
						'</tpl>'+
						'</div></td>'+
						'</tr>'+
          '</tpl>'+
					

          '<tpl if="!GO.util.empty(expire_time)">'+
						'<tr>'+
							'<td colspan="2" class="display-panel-heading">'+t("External download link enabled", "files")+'</td>'+
						'</tr>'+
						'<tr>'+
            '<td style="white-space:nowrap">'+t("Link expires after", "files")+':</td>'+
            '<td>{expire_time}</td>'+
						'</tr>'+
						
						'<tr>'+
            '<td>'+t("URL", "files")+':</td>'+
            '<td><a href="{download_link}" target="_blank">'+t("Right click to copy", "files")+'</a>'+
						//'<tpl if="unlock_allowed">'+
							' <span class="fs-deleteDL" style="cursor:pointer;text-decoration:underline;">['+t("Delete this link", "files")+']</span>'+
						//'</tpl>'+
						'</td>'+
						'</tr>'+
						
						'<tpl if="!GO.util.empty(delete_when_expired)">'+
							'<tr>'+
								'<td colspan="2"><span style="color:var(--hue-red);">'+t("File will be automatically deleted when its download link expires", "files")+'</span></td>'+
							'</tr>'+
						'</tpl>'+
						
          '</tpl>'+
					
					'<tpl if="!GO.util.empty(content_expire_date)">'+
						'<tr>'+
            '<td>'+t("Content expires at", "files")+':</td>'+
						
						'<tpl if="GO.files.isContentExpired(content_expire_date) == false">'+
							'<td><span>{content_expire_date}</span></td>'+
						'</tpl>'+
						'<tpl if="GO.files.isContentExpired(content_expire_date)">'+
							'<td><span class="content-expired">{content_expire_date}</span></td>'+
						'</tpl>'+
						'</tr>'+
          '</tpl>'+

					this.extraTemplateProperties +
				'</table>';

	
		
		if(go.Modules.isAvailable("legacy", "workflow")) {
			this.template += GO.workflow.WorkflowTemplate;
		}

		GO.files.FilePanel.superclass.initComponent.call(this);
		
		this.add(go.customfields.CustomFields.getDetailPanels("File"));
	}
});
