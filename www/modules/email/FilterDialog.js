GO.email.FilterDialog = Ext.extend(GO.dialog.TabbedFormDialog, {
	initComponent : function(){
		
		Ext.apply(this, {
			titleField:'keyword',
			title:t("Filter", "email"),			
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
					fieldLabel : t("Field", "email"),
					hiddenName : 'field',
					store : new Ext.data.SimpleStore({
						fields : ['value', 'text'],
						data : [
							[
								'from',
								t("From field", "email")],
							[
								'subject',
								t("Subject", "email")],
							['to', t("Send To", "email")],
							[
								'cc',
								t("CC field", "email")]]
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
					fieldLabel : t("Keyword", "email"),
					name : 'keyword',
					allowBlank : false
				}, new Ext.form.ComboBox({
					fieldLabel : t("Move to folder", "email"),
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
					boxLabel : t("Mark as read", "email"),
					name : 'mark_as_read',
					checked : false,
					hideLabel : true
				}]
			});
		}
	});
