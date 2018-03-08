GO.email.FilterDialog = Ext.extend(GO.dialog.TabbedFormDialog, {
	initComponent : function(){
		
		Ext.apply(this, {
			titleField:'keyword',
			title:GO.email.lang.filter,			
			formControllerUrl: 'email/filter',
			height:200
		});
		
		GO.email.FilterDialog.superclass.initComponent.call(this);	
	},
	
	buildForm:function(){
		this.addPanel({
			layout : 'form',
			defaults : {
				anchor : '100%'
			},
			defaultType : 'textfield',
			labelWidth : 125,
			border : false,
			cls : 'go-form-panel',
			waitMsgTarget : true,
			items : [new Ext.form.ComboBox({
					fieldLabel : GO.email.lang.field,
					hiddenName : 'field',
					store : new Ext.data.SimpleStore({
						fields : ['value', 'text'],
						data : [
							[
								'from',
								GO.email.lang.sender],
							[
								'subject',
								GO.email.lang.subject],
							['to', GO.email.lang.sendTo],
							[
								'cc',
								GO.email.lang.ccField]]
					}),
					value : 'from',
					valueField : 'value',
					displayField : 'text',
					typeAhead : true,
					mode : 'local',
					triggerAction : 'all',
					editable : false,
					selectOnFocus : true,
					forceSelection : true
				}), {
					fieldLabel : GO.email.lang.keyword,
					name : 'keyword',
					allowBlank : false
				}, new Ext.form.ComboBox({
					fieldLabel : GO.email.lang.moveToFolder,
					hiddenName : 'folder',
					store : GO.email.subscribedFoldersStore,
					valueField : 'name',
					displayField : 'name',
					typeAhead : true,
					mode : 'local',
					triggerAction : 'all',
					editable : false,
					selectOnFocus : true,
					forceSelection : true,
					allowBlank : false
				}), {
					xtype:'xcheckbox',
					boxLabel : GO.email.lang.markAsRead,
					name : 'mark_as_read',
					checked : false,
					hideLabel : true
				}]
			});
		}
	});