/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PresidentPanel.js 17133 2014-03-20 08:25:24Z mschering $
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
						'<td>'+GO.presidents.lang.party_id+':</td>'+
						'<td>{partyName}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+GO.presidents.lang.tookoffice+':</td>'+
						'<td>{tookoffice}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+GO.presidents.lang.leftoffice+':</td>'+
						'<td>{leftoffice}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>'+GO.presidents.lang.income+':</td>'+
						'<td>$ {income}</td>'+
					'</tr>'+
				'</table>';																		
				
		if(GO.customfields)
			this.template += GO.customfields.displayPanelTemplate;
		
		if(GO.tasks)
			this.template += GO.tasks.TaskTemplate;

		if(GO.calendar)
			this.template += GO.calendar.EventTemplate;
		
		if(GO.workflow)
			this.template +=GO.workflow.WorkflowTemplate;

		this.template += GO.linksTemplate;	
				
		if(GO.files)
		{
			Ext.apply(this.templateConfig, GO.files.filesTemplateConfig);
			this.template += GO.files.filesTemplate;
		}
		Ext.apply(this.templateConfig, GO.linksTemplateConfig);
		
		if(GO.comments)
			this.template += GO.comments.displayPanelTemplate;

		GO.presidents.PresidentPanel.superclass.initComponent.call(this);
	}
});