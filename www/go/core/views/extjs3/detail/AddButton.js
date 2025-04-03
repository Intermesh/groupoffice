/* global GO, go, Ext */

/**
 * 
 * A add menu button for detail views. 
 * 
 * Each detailview panel component can have a property "addMenuItems". These
 * will be added to this menu button.
 */

go.detail.addButton = Ext.extend(Ext.Button, {
	tooltip: t('Add'),
	iconCls: 'ic-add-link',
	menu: [],
	detailView: null,
	documentTemplates: true,
	initComponent: function () {
		
		this.menu = this.buildMenu();
		
		go.detail.addButton.superclass.initComponent.call(this);

		//TODO refactor
		
		if(!this.noFiles && this.documentTemplates) {


			//noFiles is used in GO.email.LinkedMessagePanel
			if(go.Modules.isAvailable("legacy", "documenttemplates")) {

				var lastEmailIndex = 0;
				this.menu.items.each(function(item, index) {
					if(item.text && item.text.indexOf('E-mail') > -1) {
						lastEmailIndex = index;
					}
				});

				if(!lastEmailIndex) {
					lastEmailIndex = this.menu.items.getCount() - 1;
				}

				this.menu.insert(lastEmailIndex + 1,	{
					iconCls: 'ic-mail bluegrey',
					text:  t("E-mail from template","documenttemplates", "legacy"),
					scope: this,
					handler: function() {
						if(!GO.documenttemplates.emailTemplateDialog){
							GO.documenttemplates.emailTemplateDialog = new GO.documenttemplates.EmailTemplateDialog();
						}


						GO.documenttemplates.emailTemplateDialog.entity = this.getEntity();
						GO.documenttemplates.emailTemplateDialog.entityId = this.getEntityId();

						GO.documenttemplates.emailTemplateDialog.show();

						GO.documenttemplates.emailTemplateDialog.on('hide', function(){
							this.detailView.reload();
						}, this, {single: true});
					}
				});

				this.menu.add(	{
					iconCls: 'ic-description pink',
					text:  t("Document from template", "documenttemplates", "legacy"),
					scope: this,
					handler: function() {
						if(!GO.documenttemplates.templateDocumentDialog){
							GO.documenttemplates.templateDocumentDialog = new GO.documenttemplates.TemplateDocumentDialog();
						}


						GO.documenttemplates.templateDocumentDialog.entity = this.getEntity();
						GO.documenttemplates.templateDocumentDialog.entityId = this.getEntityId();
						GO.documenttemplates.templateDocumentDialog.folderId = this.detailView.data.files_folder_id;

						GO.documenttemplates.templateDocumentDialog.show();//.show(this.entityId, this.entity);

						GO.documenttemplates.templateDocumentDialog.on('hide', function(){
							this.detailView.reload();
						}, this, {single: true});
					}
				});
			}
		}

		this.on("afterrender", this.onAfterRender, this);
		
	},

	
	onAfterRender : function() {

		if(go.Modules.isAvailable("legacy", "workflow")) {

			if(['File', 'Folder', 'Order'].indexOf(this.getEntity()) > -1) {
				this.menu.add({
					iconCls: 'go-module-icon-workflow',
					text: t("Workflow"),
					scope: this,
					handler: function () {
						var modelDialog = new GO.workflow.ModelDialog();
						modelDialog.closeAction = "close";

						modelDialog.addBaseParam('model_id', this.getEntityId());
						modelDialog.addBaseParam('model_name', this.getEntity());

						modelDialog.show();
					}
				});
			}
		}

		this.detailView.on('load', function (dv) {
			if(!dv.data) {
				this.setDisabled(true);
				return;
			}
			var pl = Ext.isDefined(dv.data.permissionLevel ) ? dv.data.permissionLevel : dv.data.permission_level;
			this.setDisabled(pl < go.permissionLevels.write);
		}, this);
	},

			
	findCreateLinkButton : function(window) {
		
		var tbars = [window.getFooterToolbar(), window.getBottomToolbar(), window.getTopToolbar()];
		for(var i = 0, l = tbars.length; i < l; i++) {
			if(!tbars[i]) {
				continue;
			}
			
			var btn = tbars[i].findByType("createlinkbutton");
			if(btn[0]) {
				return btn[0];
			}			
		}
		return false;
	},

	buildMenu: function () {
		var items = [
			{
				iconCls: 'ic-search',
				text: t("Existing item"),
				handler: function () {
					var linkWindow = new go.links.CreateLinkWindow({
						entityId: this.getEntityId(),
						entity: this.getEntity()
					}
					);
					linkWindow.show();
				},
				scope: this
			},
			
			'-'
		];

		go.Entities.getLinkConfigs().filter(function(l) {
			return !!l.linkWindow;
		}).forEach(function (l) {

			items.push({
				iconCls: l.iconCls,
				text: l.title,				
				handler: function () {
					var window = l.linkWindow.call(l.scope, this.getEntity(), this.getEntityId(), this.detailView.data, this.detailView);

					if (!window) {
						return;
					}

					if(!(window instanceof Ext.Window)) {
						return this.handleGOUI(window);
					}

					//If go.form.Dialog turn off redirect to detail view.
					window.redirectOnSave = false;

					if (!window.isVisible() &&  !(GO.email && window instanceof GO.email.EmailComposer)) {
						window.show();
					}
					
					//Windows may implement setLinkEntity() so they can do stuff on linking.
					if (window.setLinkEntity) {
						//window.on('show', function () {
							window.setLinkEntity({
								entity: this.getEntity(),
								entityId: this.getEntityId(),
								data: structuredClone(this.detailView.data) // to avoid that the detailview data is modified
							});
						//}, this, {single: true});
					}
					var win = window.win || window; //for some old dialogs that have a "win" prop (TaskDialog and EventDialog)
					var createLinkButton = this.findCreateLinkButton(win);

					if(createLinkButton) {
						//if window has a create link button then use this. Otherwise add a save listener.
						if(window.isVisible()) {
							createLinkButton.addLink(this.getEntity(), this.getEntityId());
						} else {
							//sometimes show is overriden and perhaps does an async load before showing. See FinanceDocumentDialog for example.
							window.on("show", () => {
								createLinkButton.addLink(this.getEntity(), this.getEntityId());
							}, this, {single: true});
						}
					} else {
						window.on('save', function (window, entity) {

							//hack for event dialog because save event is different
							if (l.entity === "Event") {
								entity = arguments[2].result.id;
							}

							var link = {
								fromEntity: this.getEntity(),
								fromId: this.getEntityId(),
								toEntity: l.entity,
								toId: null
							};

							if (!Ext.isObject(entity)) {
								//old modules just pass ID
								link.toId = entity;
							} else {
								//in this case it's a go.form.Dialog							
								link.toId = entity.id;
							}

							go.Db.store("Link").set({
								create: {clientId : link}
							}, function (options, success, result) {
								if (result.notCreated 
										&& !(result.notCreated.clientId 
										&& result.notCreated.clientId.validationErrors 
										&& result.notCreated.clientId.validationErrors.toId
										&& result.notCreated.clientId.validationErrors.toId.code
										&& result.notCreated.clientId.validationErrors.toId.code === 11)) { //already exists
									Ext.MessageBox.alert(t("Error"), t("Could not create link"));
								}
							});

						}, this, {single: true});
					}
				},
				scope: this
			});
			// add E-mail files after E-mail
			if(l.title == "E-mail") {
				items.push({
					iconCls: "entity LinkedEmail bluegrey",
					text: t("E-mail files"),
					handler: function () {
						var dv = this.detailView;
						this.folderId = dv.data.filesFolderId || dv.data.files_folder_id;
						GO.email.openFolderTree(this.folderId, this.folderId, dv);
					},
					scope: this
				});
			}
		}, this);

		return items;
	},
	
	getEntityId : function() {
		return this.detailView.currentId || this.detailView.model_id; //for old display panel
	},
	
	getEntity : function() {
		return this.detailView.entity || this.detailView.entityStore.entity.name; //entity must be set on old panels
	},


	handleGOUI: function (w) {
		w.addLinkOnSave(this.getEntity(), this.getEntityId());
		w.show();
	}
});

Ext.reg("detailaddbutton", go.detail.addButton);