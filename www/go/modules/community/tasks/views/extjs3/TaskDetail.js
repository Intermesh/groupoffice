/* global go, Ext, GO, mcrypt */

go.modules.community.tasks.TaskDetail = Ext.extend(go.detail.Panel, {
	
	entityStore: "Task",

	stateId: 'ta-tasks-detail',

	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
				xtype: 'readmore',
				onLoad: function (dv) {
					this.setText("<h3>" + dv.data.title +
					 "</h3><div class='go-html-formatted'>" +(dv.data.description||"") + "</div>");

				}
			},{
				tpl: '<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
					'<td colspan="2"><h3>{name}</h3></td>'+
					'</tr>'+
					'<tr>'+
					'<td>'+t("Tasklist", "tasks")+':</td>'+
					'<td>{tasklistId}</td>'+
					'</tr>'+
					'<tr>'+
					'<td>'+t("Starts at", "tasks")+':</td>'+
					'<td>{[go.util.Format.date(values.start)]}</td>'+
					'</tr>'+
					'<tr>'+
					'<td>'+t("Due at", "tasks")+':</td>'+
					'<td{[go.util.Format.date(values.due)]}</td>'+
					'</tr>'+
					'<tr>'+
					'<td>'+t("Status")+':</td>'+
					'<td>{[values.completed ? "Done" : "Open"]}</td>'+
					'</tr>'+
				'<tpl if="!GO.util.empty(description)">'+
						'<tr>'+
					'<td colspan="2" class="display-panel-heading">'+t("Description")+'</td>'+
					'</tr>'+
					'<tr>'+
					'<td colspan="2">{description}</td>'+
					'</tr>'+
					'</tpl>'+

					'</table>'
			}]
		});
		

		go.modules.community.tasks.TaskDetail.superclass.initComponent.call(this);
		this.addCustomFields();
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
