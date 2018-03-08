/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PresidentPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.presidents.PresidentPanel = Ext.extend(GO.DisplayPanel,{
	model_name : "GO\\Presidents\\Model\\President",
	stateId : 'pm-president-panel',
	noFileBrowser : true,
	
	editHandler : function(){
		if(!GO.presidents.presidentDialog)
			GO.presidents.presidentDialog = new GO.presidents.PresidentDialog();
		GO.presidents.presidentDialog.show(this.link_id);
	},
		
	initComponent : function(){	
		
		this.loadUrl=('presidents/president/display');
		
		this.template = 
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
						'<td colspan="2" class="display-panel-heading">{firstname} {lastname}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>ID:</td>'+
						'<td>{id}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+t("Party", "presidents")+':</td>'+
						'<td>{partyName}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+t("Entering Office", "presidents")+':</td>'+
						'<td>{tookoffice}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+t("Leaving Office", "presidents")+':</td>'+
						'<td>{leftoffice}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+t("Income", "presidents")+':</td>'+
						'<td>$ {income}</td>'+
					'</tr>'+
				'</table>';																		
				
		if(go.ModuleManager.isAvailable("customfields"))
			this.template += GO.customfields.displayPanelTemplate;
		
		if(go.ModuleManager.isAvailable("tasks"))
			this.template += GO.tasks.TaskTemplate;

		if(go.ModuleManager.isAvailable("calendar"))
			this.template += GO.calendar.EventTemplate;
		
		if(go.ModuleManager.isAvailable("workflow"))
			this.template +=GO.workflow.WorkflowTemplate;

		this.template += GO.linksTemplate;	
				
		if(go.ModuleManager.isAvailable("files"))
		{
			Ext.apply(this.templateConfig, GO.files.filesTemplateConfig);
			this.template += GO.files.filesTemplate;
		}
		Ext.apply(this.templateConfig, GO.linksTemplateConfig);
		
		if(go.ModuleManager.isAvailable("comments"))
			this.template += GO.comments.displayPanelTemplate;

		GO.presidents.PresidentPanel.superclass.initComponent.call(this);
	}
});