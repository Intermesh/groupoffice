/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: TaskPanel.js 17728 2014-07-03 08:25:10Z wilmar1980 $
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
	
	initComponent : function(){
	
		this.loadUrl=('tasks/task/display');
	
		this.template = 			
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
						'<td colspan="2" class="display-panel-heading">'+GO.tasks.lang.task+': {name}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>ID:</td>'+
						'<td>{id}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+GO.tasks.lang.tasklist+':</td>'+
						'<td>{tasklist_name}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+GO.tasks.lang.startsAt+':</td>'+
						'<td>{start_time}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+GO.tasks.lang.dueAt+':</td>'+
						'<td<tpl if="late"> class="tasks-late"</tpl>>{due_time}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+GO.lang.strStatus+':</td>'+
						'<td>{status_text}</td>'+
					'</tr>';
				
				if(GO.projects2){
					this.template +=
					'<tpl if="project_name">'+
						'<tr>'+
							'<td>'+GO.projects2.lang.project+':</td>'+
							'<td><a href="#" onclick="GO.linkHandlers[\'GO\\\\\\\\Projects2\\\\\\\\Model\\\\\\\\Project\'].call(this, {project_id});">{project_name}</a></td>'+
						'</tr>'+
					'</tpl>';
				} else if(GO.projects){
					this.template +=
					'<tpl if="project_name">'+
						'<tr>'+
							'<td>'+GO.projects.lang.project+':</td>'+
							'<td><a href="#" onclick="GO.linkHandlers[\'GO\\\\\\\\Projects\\\\\\\\Model\\\\\\\\Project\'].call(this, {project_id});">{project_name}</a></td>'+
						'</tr>'+
					'</tpl>';
				}
					
				this.template +=
					'<tpl if="!GO.util.empty(description)">'+
						'<tr>'+
							'<td colspan="2" class="display-panel-heading">'+GO.lang.strDescription+'</td>'+
						'</tr>'+
						'<tr>'+
							'<td colspan="2">{description}</td>'+
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

		if(GO.workflow){
			this.template +=GO.workflow.WorkflowTemplate;
		}


		this.template += GO.linksTemplate;	
				
		if(GO.files)
		{
			Ext.apply(this.templateConfig, GO.files.filesTemplateConfig);
			this.template += GO.files.filesTemplate;
		}
		Ext.apply(this.templateConfig, GO.linksTemplateConfig);
		
		
		if(GO.comments)
		{
			this.template += GO.comments.displayPanelTemplate;
		}

		this.template += GO.createModifyTemplate;

		this.buttons=[this.continueTaskButton = new Ext.Button({
			text:GO.tasks.lang.continueTask,
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
			scope:this,
			disabled:true
		})];
		
		GO.tasks.TaskPanel.superclass.initComponent.call(this);
	},
	setData : function(data){
		GO.tasks.TaskPanel.superclass.setData.call(this, data);

		this.continueTaskButton.setDisabled(!data.write_permission);
	},
	reset : function(){
		GO.tasks.TaskPanel.superclass.reset.call(this);

		this.continueTaskButton.setDisabled(true);
	}
});			