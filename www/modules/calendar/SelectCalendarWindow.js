/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SelectCalendarWindow.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

SelectCalendarWindow = function(){
	return {
		accept : function(event_id)
		{
			Ext.Ajax.request({
				url: 'action.php',
				params:{
					task: 'accept', 
					event_id: event_id
				},
				callback: function(options, success, response){
					
					if(!success)
					{
						Ext.MessageBox.alert(GO.lang.strError, GO.lang.strRequestError);
					}else
					{						
						var responseParams = Ext.decode(response.responseText);
						if(responseParams.success)
						{
							Ext.MessageBox.alert(GO.lang.strSuccess, GO.calendar.lang.closeWindow);
							this.window.close();
						}else
						{
							Ext.MessageBox.alert(GO.lang.strError, responseParams.feedback);
							
						}
					}
											
				},
				scope: this		
			});
		},
		show : function(event_id){

			this.selectCalendar = new GO.form.ComboBox({
				value:GO.calendar.defaultCalendar.id,
				remoteText:GO.calendar.defaultCalendar.name,
				fieldLabel:GO.calendar.lang.selectCalendarForAppointment,
				store:new GO.data.JsonStore({
					url: GO.settings.modules.calendar.url+'json.php',
					baseParams: {
						'task': 'user_calendars'
					},
					root: 'results',
					totalProperty: 'total',
					id: 'id',
					fields:['id','name'],
					remoteSort:true
				}),
				displayField:'name',
				valueField:'id',
				triggerAction:'all',
				editable: false,
				forceSelection: true,
				emptyText:GO.lang.strPleaseSelect
			});
	

			this.window = new Ext.Window({
				renderTo:document.body,
				title: GO.calendar.lang.selectCalendar,
				modal:false,
				autoHeight:true,
				width:500,
				closable:false,
				items: new Ext.FormPanel({
					autoHeight:true,
					items:this.selectCalendar,
					labelAlign:'top',
					cls:'go-form-panel',
					waitMsgTarget:true
				}),
				buttons:[{
					text:GO.lang.cmdOk,
					handler: function(){
						Ext.Ajax.request({
							url: 'action.php',
							params:{
								task: 'accept',
								calendar_id: this.selectCalendar.getValue(),
								event_id: event_id
							},
							callback: function(options, success, response){
								if(!success)
								{
									Ext.MessageBox.alert(GO.lang.strError, GO.lang.strRequestError);
								}else
								{
									var responseParams = Ext.decode(response.responseText);
									if(responseParams.success)
									{
										Ext.MessageBox.alert(GO.lang.strSuccess, GO.calendar.lang.closeWindow);
										this.window.close();
									}else
									{
										Ext.MessageBox.alert(GO.lang.strError, responseParams.feedback);

									}
								}							},
							scope: this
						});
					},
					scope: this
				}]
			});			
			
			this.window.show();
		}
	}
}