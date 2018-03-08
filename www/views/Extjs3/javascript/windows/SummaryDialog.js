/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SummaryDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 *
 * If you extend this class, you MUST use the addPanel method to add at least
 * one panel to this dialog. A tabPanel is automatically created if and only if
 * more than one panel is added to the dialog in this way.
 */

//GO.dialog.TabbedFormDialog = function(config) {
//	
//	config = config | {};
//	
//	if (config.title)
//		this.baseTitle = config.title;
//	
//	GO.dialog.TabbedFormDialog.superclass.constructor(this,config);
//}
GO.dialog.SummaryDialog = Ext.extend(GO.Window, {

	summaryLog : false,

	initComponent : function(){

		this.xTemplate = new Ext.XTemplate(
			GO.lang.summarylogImportText+'<br /><br />'+
			'<tpl if="errorCount &gt;= 1">'+
				'<font class="summary-error-font">'+
				GO.lang.summarylogErrorText+'<br />'+
				'</font>'+
				'<hr />'+
				'<table class="summary-log-table">'+
					'<tpl for="errors">'+
						'<tr>'+
							'<td class="summary-log-name">'+
								'{name}'+
							'</td>'+
							'<td class="summary-log-message">'+
								'{[Ext.util.Format.nl2br(values.message)]}'+
							'</td>'+
						'</tr>'+
					'</tpl>'+
				'</table>'+
			'</tpl>'+
			'<tpl if="errorCount < 1">'+
				'<font class="summary-success-font">'+
				GO.lang.summarylogSuccessText+'<br />'+
				'</font>'+
			'</tpl>'
		);

		this.templatePanel = new Ext.Panel({
			border:false,
			padding: '10px',
			autoScroll:true,
			html: this.xTemplate.apply({
				importCount: this.summaryLog.total,
				errorCount: this.summaryLog.errorCount,
				errors: this.summaryLog.errors
			})
		});

		Ext.applyIf(this, {
			modal:false,
			layout:'fit',
			height: 230,
			width: 480,
			resizable: false,
			closeAction:'hide',
			title:'Import Summary',
			items: this.templatePanel,		
			buttons: [{				
				text: GO.lang['cmdClose'],
				handler: function(){
					this.hide()
				},
				scope:this
			}]
		});
		
		GO.dialog.SummaryDialog.superclass.initComponent.call(this);
		
	},
	
	show : function(){
		GO.dialog.SummaryDialog.superclass.show.call(this);
		
		this.xTemplate.overwrite(this.templatePanel.body, {
			importCount: this.summaryLog.total - this.summaryLog.errorCount,
			errorCount: this.summaryLog.errorCount,
			errors: this.summaryLog.errors
		});
		
	},
	
	setSummaryLog : function(summarylog){
		this.summaryLog = summarylog;
	}
});

