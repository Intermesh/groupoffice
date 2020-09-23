/* global go, Ext, GO, mcrypt */

go.modules.community.tasks.TaskDetail = Ext.extend(go.detail.Panel, {
	
	entityStore: "Task",
	width:dp(400),
	stateId: 'ta-tasks-detail',

	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
				tpl: '<h3 class="title s8">{title}</h3>\
					<h4 style="text-transform:uppercase; float:right; padding:12px 8px 0 0;">{[go.modules.community.tasks.progress[values.progress]]}</h4>\
					<p class="s6 pad">\
					<label>'+t("Starts at")+'</label>\
					<span>{[fm.date(values.start)]}</span><br><br>\
					<label>'+t("Tasklist")+'</label>\
					<span>{[fm.date(values.start)]}</span><br><br>\
					<label>'+t('Email')+'</label><span>{tasklistId}</span><br><br>\
				</p>\
				<p class="s6">\
					\<label>'+t("Due at")+'</label>\
					<span>{[fm.date(values.due)]}</span><br><br>\
				</p><tpl if="!GO.util.empty(description)"><p class="s12 pad">\
					<label>'+t('Description')+'</label>\
					<span>{description}</span>\
				</p></tpl>'
			}]
		});
		

		go.modules.community.tasks.TaskDetail.superclass.initComponent.call(this);
		this.addCustomFields();
		this.addComments();
		this.addLinks();
		this.addFiles();
	},

	onLoad: function () {
		this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < go.permissionLevels.write);
		this.deleteItem.setDisabled(this.data.permissionLevel < go.permissionLevels.writeAndDelete);

		go.modules.community.tasks.TaskDetail.superclass.onLoad.call(this);
	},

	initToolbar: function () {

		var items = this.tbar || [];

		items = items.concat([
			'->',
			{
				itemId: "edit",
				iconCls: 'ic-edit',
				tooltip: t("Edit"),
				handler: function (btn, e) {
					var taskEdit = new go.modules.community.tasks.TaskDialog();
					taskEdit.load(this.data.id).show();
				},
				scope: this
			},

			new go.detail.addButton({
				detailView: this
			}),

			this.moreMenu = {
				iconCls: 'ic-more-vert',
				menu: [
					{
						xtype: "linkbrowsermenuitem"
					},
					'-',
					{
						iconCls: "btn-print",
						text: t("Print"),
						handler: function () {
							this.body.print({title: this.data.name});
						},
						scope: this
					}, "-",
					this.deleteItem = new Ext.menu.Item({
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
								if (btn !== "yes") {
									return;
								}
								this.entityStore.set({destroy: [this.currentId]});
							}, this);
						},
						scope: this
					})
				]
			}]);
		
		if(go.Modules.isAvailable("legacy", "files")) {
			this.moreMenu.menu.splice(1,0,{
				xtype: "filebrowsermenuitem"
			});
		}

		var tbarCfg = {
			disabled: true,
			items: items
		};

		this.buttons = [{
			iconCls: 'ic-forward',
			text:t("Continue task", "tasks"),
			handler:function(){
				this.continueTaskDialog = new go.modules.community.tasks.ContinueTaskDialog({
					listeners:{
						submit:function(){
							this.reload();
							var tasksModulePanel =GO.mainLayout.getModulePanel('task');
							if(tasksModulePanel && tasksModulePanel.rendered){
								//tasksModulePanel.gridPanel.store.reload();
							}
						},
						scope:this
					},
					baseParams:{
						permissionLevel: GO.permissionLevels.create
					},
				});
				this.continueTaskDialog.load(this.data.id).show();
			},
			scope:this
			//disabled:true
		}];
		return new Ext.Toolbar(tbarCfg);
	}
});
