/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: EventPanel.js 19228 2015-06-24 12:36:41Z wsmits $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.calendar.EventPanel = Ext.extend(GO.DisplayPanel,{
	model_name : "GO\\Calendar\\Model\\Event",

	stateId : 'cal-event-panel',

	editGoDialogId : 'event',

	editHandler : function(){		
		GO.calendar.showEventDialog({event_id: this.link_id});
	},
	
	updateToolbar : function(){
		
		GO.calendar.EventPanel.superclass.updateToolbar.call(this);
		
		
		this.editButton.setDisabled(!this.data.is_organizer);
		
	},
	
	initComponent : function(){
		
		this.loadUrl=('calendar/event/display');

		this.template =
				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
//					'<tr>'+
//						'<td colspan="2" class="display-panel-heading">'+GO.calendar.lang.event+': {name}</td>'+
//					'</tr>'+
//					'<tr>'+
//						'<td colspan="2"><table><tr><td>'+GO.calendar.lang.calendar+': </td><td>{calendar_name}</td></tr></table></td>'+
//					'</tr>'+
					'<tr>'+
						'<td colspan="2">{event_html}</td>'+
					'</tr>'+					
				'</table>';

		if(GO.customfields)
		{
			this.template +=GO.customfields.displayPanelTemplate;
		}

		if(GO.tasks)
			this.template +=GO.tasks.TaskTemplate;

		if(GO.workflow){
			this.template +=GO.workflow.WorkflowTemplate;
		}
		
		if(GO.calendar)
			this.template += GO.calendar.EventTemplate;

		this.template +=GO.linksTemplate;		
		
		if(GO.lists)
			this.template += GO.lists.ListTemplate;

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

		GO.calendar.EventPanel.superclass.initComponent.call(this);
	}
});