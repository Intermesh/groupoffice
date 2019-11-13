/* global go, Ext, GO, mcrypt */

go.modules.community.task.TaskDetail = Ext.extend(go.detail.Panel, {
	
	entityStore: "Task",

	stateId: 'ta-tasks-detail',

	initComponent: function () {


		this.tbar = this.initToolbar();

		Ext.apply(this, {
			items: [{
					xtype: 'readmore',
					onLoad: function (detailView) {
						
						this.setText("<h3>" + detailView.data.title +
						 "</h3><div class='go-html-formatted'>" +
						  "</div>");

						//   this.template = 			
						//   '<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
						// 	  '<tr>'+
						// 		  '<td colspan="2"><h3>{name}</h3></td>'+
						// 	  '</tr>'+
						// 	  '<tr>'+
						// 		  '<td>'+t("Tasklist", "tasks")+':</td>'+
						// 		  '<td>{tasklist_name}</td>'+
						// 	  '</tr>'+
						// 	  '<tr>'+
						// 		  '<td>'+t("Starts at", "tasks")+':</td>'+
						// 		  '<td>{start_time}</td>'+
						// 	  '</tr>'+
						// 	  '<tr>'+
						// 		  '<td>'+t("Due at", "tasks")+':</td>'+
						// 		  '<td<tpl if="late"> class="tasks-late"</tpl>>{due_time}</td>'+
						// 	  '</tr>'+
						// 	  '<tr>'+
						// 		  '<td>'+t("Status")+':</td>'+
						// 		  '<td>{status_text}</td>'+
						// 	  '</tr>';
						  
						//   if(go.Modules.isAvailable("legacy", "projects2")){
						// 	  this.template +=
						// 	  '<tpl if="project_name">'+
						// 		  '<tr>'+
						// 			  '<td>'+t("Project", "projects2")+':</td>'+
						// 			  '<td><a  onclick="GO.linkHandlers[\'GO\\\\\\\\Projects2\\\\\\\\Model\\\\\\\\Project\'].call(this, {project_id});">{project_name}</a></td>'+
						// 		  '</tr>'+
						// 	  '</tpl>';
						//   } 
							  
						//   this.template +=
						// 	  '<tpl if="!GO.util.empty(description)">'+
						// 		  '<tr>'+
						// 			  '<td colspan="2" class="display-panel-heading">'+t("Description")+'</td>'+
						// 		  '</tr>'+
						// 		  '<tr>'+
						// 			  '<td colspan="2">{description}</td>'+
						// 		  '</tr>'+
						// 	  '</tpl>'+
											  
						//   '</table>';	
					}
				}
			]
		});
		

		go.modules.community.task.TaskDetail.superclass.initComponent.call(this);
		this.addCustomFields();
		this.addLinks();
		this.addFiles();
	},

	onLoad: function () {
		this.getTopToolbar().getComponent("edit").setDisabled(this.data.permissionLevel < go.permissionLevels.write);
		this.deleteItem.setDisabled(this.data.permissionLevel < go.permissionLevels.writeAndDelete);

		go.modules.community.task.TaskDetail.superclass.onLoad.call(this);
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
					var taskEdit = new go.modules.community.task.TaskDialog();
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
				this.continueTaskDialog = new go.modules.community.task.ContinueTaskDialog({
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
