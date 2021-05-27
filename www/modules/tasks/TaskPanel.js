	/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: TaskPanel.js 22337 2018-02-07 08:23:15Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.tasks.TaskPanel = Ext.extend(GO.DisplayPanel,{
	
	
	model_name : "GO\\Tasks\\Model\\Task",
	
	stateId : 'ta-task-panel',

	editGoDialogId : 'task',
	
	editHandler : function(){		
		GO.tasks.showTaskDialog({task_id: this.model_id});		
	},	
	
	initComponent : function() {
		
		this.loadUrl=('tasks/task/display');

		this.template =
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
						'<td colspan="2"><h3>{name}</h3></td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+t("Tasklist", "tasks")+':</td>'+
						'<td>{tasklist_name}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+t("Starts at", "tasks")+':</td>'+
						'<td>{start_time}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+t("Due at", "tasks")+':</td>'+
						'<td<tpl if="late"> class="tasks-late"</tpl>>{due_time}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+t("Status")+':</td>'+
						'<td>{status_text}</td>'+
					'</tr>';
				
				if(go.Modules.isAvailable("legacy", "projects2")) {
					this.template += '<tpl if="project_name">'+
						'<tr>'+
							'<td>'+t("Project", "projects2")+':</td>'+
							'<td><a class="normal_link"  onclick="GO.tasks.TaskPanel.openProject({project_id});">{project_name:raw}</a></td>' +
						'</tr>'+
					'</tpl>';
				}
					
				this.template +=
					'<tpl if="!GO.util.empty(description)">'+
						'<tr>'+
							'<td colspan="2" class="display-panel-heading">'+t("Description")+'</td>'+
						'</tr>'+
						'<tr>'+
							'<td colspan="2">{description:raw}</td>'+
						'</tr>'+
					'</tpl>'+
									
				'</table>';																		

		if(go.Modules.isAvailable("legacy", "workflow")){
			this.template +=GO.workflow.WorkflowTemplate;
		}

		this.buttons = [{
			iconCls: 'ic-forward',
			text:t("Continue task", "tasks"),
			handler:function(){
				if(!this.continueTaskDialog){
					this.continueTaskDialog = new GO.tasks.ContinueTaskDialog({
						listeners:{
							submit:function(){
								this.reload();
								var tasksModulePanel =GO.mainLayout.getModulePanel('tasks');
								if(tasksModulePanel && tasksModulePanel.rendered){
									tasksModulePanel.gridPanel.store.reload();
								}
							},
							scope:this
						}
					});
				}

				this.continueTaskDialog.show(this.data.id,this.data);
			},
			scope:this
			//disabled:true
		}];
		
		GO.tasks.TaskPanel.superclass.initComponent.call(this);
	
		
		this.add(go.customfields.CustomFields.getDetailPanels("Task"));

	}


});
GO.tasks.TaskPanel.openProject = function(projectId) {
	debugger;
	var win = new go.links.LinkDetailWindow({
		entity: 'Project'
	});

	win.load(projectId);
};