/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: EventPanel.js 22112 2018-01-12 07:59:41Z mschering $
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
//						'<td colspan="2" class="display-panel-heading">'+t("Event", "calendar")+': {name}</td>'+
//					'</tr>'+
//					'<tr>'+
//						'<td colspan="2"><table><tr><td>'+t("Calendar", "calendar")+': </td><td>{calendar_name}</td></tr></table></td>'+
//					'</tr>'+
					'<tr>'+
						'<td colspan="2">{event_html}</td>'+
					'</tr>'+					
				'</table>';

		
		if(go.Modules.isAvailable("legacy", "workflow")){
			this.template +=GO.workflow.WorkflowTemplate;
		}
		


	
		GO.calendar.EventPanel.superclass.initComponent.call(this);
	}
});
