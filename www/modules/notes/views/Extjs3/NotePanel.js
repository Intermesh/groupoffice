/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: NotePanel.js 16947 2014-02-28 14:04:55Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.notes.NotePanel = Ext.extend(GO.DisplayPanel,{
	model_name : "GO\\Notes\\Model\\Note",
	
	stateId : 'no-note-panel',

	//deprecated. tabbedformdialog refreshes active displaypanel automatically.
	editGoDialogId : 'note',
	
	editHandler : function(){
		GO.notes.showNoteDialog(this.model_id);		
	},	
		
	initComponent : function(){	
		
		this.loadUrl=('notes/note/display');
		
		this.encryptId=Ext.id();
		
		this.template = 

				'<table class="display-panel" cellpadding="0" cellspacing="0" border="0">'+
					'<tr>'+
						'<td colspan="2" class="display-panel-heading">'+GO.notes.lang.note+': {name}</td>'+
					'</tr>'+
					'<tr>'+
						'<td>ID:</td>'+
						'<td>{id}</td>'+
					'</tr>'+
					'<tr>'+
						'<tpl if="GO.util.empty(encrypted)">'+
							'<td colspan="2">{content}</td>'+
						'</tpl>'+
						'<tpl if="!GO.util.empty(encrypted)">'+
							'<td colspan="2"><div id="encryptedNoteDisplaySecure'+this.encryptId+'"></div></td>'+
						'</tpl>'+
					'</tr>'+									
				'</table>';																		
				
		if(GO.customfields)
		{
			this.template +=GO.customfields.displayPanelTemplate;
		}

		if(GO.tasks)
			this.template +=GO.tasks.TaskTemplate;

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
		{
			this.template += GO.comments.displayPanelTemplate;
		}		
		
		if(GO.lists)
			this.template += GO.lists.ListTemplate;

		this.template += GO.createModifyTemplate;

		GO.notes.NotePanel.superclass.initComponent.call(this);
	},
	
	afterLoad : function(result) {
		if(this.data.encrypted){
			if (!this.passwordPanel){
				this.passwordPanel = new Ext.form.CompositeField({			
					renderTo: 'encryptedNoteDisplaySecure'+this.encryptId,
					
					items: [
						this.passwordField = new Ext.form.TextField({
							name: 'password',
							inputType: 'password',
							flex:2,
							listeners: {
                specialkey: function(field, e){
									// e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
									// e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
									if (e.getKey() == e.ENTER) {
										this._loadWithPassword();
									}
								},
								scope : this
							}
							
						}),
						this.passwordButton = new Ext.Button({
								flex:1,
								text: GO.lang['decryptContent'],
								handler: function(){
									this._loadWithPassword();									
								},
								scope: this
							})
					]
				});
			}else
			{
				var el = Ext.get('encryptedNoteDisplaySecure'+this.encryptId);
				//console.log(el);
				el.appendChild(this.passwordPanel.getEl());
			}
		}
	},
	
	_loadWithPassword : function() {
		
		var pass = this.passwordField.getValue();
		this.passwordField.setValue("");
		
		GO.request({
			url: 'notes/note/display',
			params: {
				'id' : this.model_id,
				'userInputPassword' : pass
			},
			success: function(options, response, result) {
				if (!GO.util.empty(result.feedback))
					Ext.MessageBox.alert('', result.feedback);
				if (GO.util.empty(result.data.encrypted)) {
					document.getElementById('encryptedNoteDisplaySecure'+this.encryptId).innerHTML = result.data.content;
				}
			},
			scope: this
		});
	}
});			