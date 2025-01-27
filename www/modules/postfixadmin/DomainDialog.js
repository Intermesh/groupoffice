/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: DomainDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.postfixadmin.DomainDialog = Ext.extend(GO.dialog.TabbedFormDialog,{
	
	enableOkButton: false,
	
	enableApplyButton: false,
	
	initComponent : function(){
		
		
		var buttons = [];
	
		buttons.push(this.buttonExport = new Ext.Button({
			text: t("Export"),
			handler: function(){
				var domainExportDialog = new GO.postfixadmin.DomainExportDialog();

				var data = {
					remoteModelId:this.remoteModelId,
					domain:this.loadData.domain
				};

				domainExportDialog.show(data);
			},
			scope:this
		}));
		
			buttons.push('->');
		
		// These three buttons are enabled by default.
		
			buttons.push(this.buttonOk = new Ext.Button({
				text: t("Save"),
				handler: function(){
					this.submitForm(true);
				},
				disabled :  go.Modules.get('legacy','postfixadmin').permissionLevel < GO.permissionLevels.manage,
				scope: this
			}));
			
			buttons.push(this.buttonApply = new Ext.Button({
				disabled :  go.Modules.get('legacy','postfixadmin').permissionLevel < GO.permissionLevels.manage,
				text: t('Apply'),
				handler: function(){
					this.submitForm();
				},
				scope:this
			}));
			
			
		
		
		Ext.apply(this, {
			buttons: buttons
		});


		Ext.apply(this, {
			buttonAlign:'left',
			titleField:'domain',
			title: t("Domain", "postfixadmin"),
			formControllerUrl: 'postfixadmin/domain',
			width:700,
			height:600
		});
		
		
		GO.postfixadmin.DomainDialog.superclass.initComponent.call(this);	
	},
	
	beforeLoad : function(remoteModelId, config){
		if(GO.settings.modules.postfixadmin.write_permission)
			this.formPanel.form.findField('domain').setDisabled(remoteModelId>0);
	},
	
	afterLoad : function(remoteModelId, config, action){
		
		if(action.result.data.permission_level >= GO.permissionLevels.write) {
			this.buttonExport.setVisible(true);
		} else {
			this.buttonExport.setVisible(false);
		}
		
		this.setBackupMX(action.result.data.backupmx=='1');
		
		if(!GO.settings.modules.postfixadmin.write_permission)
			this.mailboxesGrid.store.load();
	},

	afterSubmit : function(action){
		this.fireEvent('save', this);
	},
	
	setBackupMX : function(backupmx)
	{
		this.mailboxesGrid.setDisabled(backupmx || !this.remoteModelId);
		this.aliasesGrid.setDisabled(backupmx || !this.remoteModelId);

		if(GO.settings.modules.postfixadmin.write_permission){
			var f = this.formPanel.form;

			f.findField('max_aliases').setDisabled(backupmx);
			f.findField('max_mailboxes').setDisabled(backupmx);
			f.findField('total_quota').setDisabled(backupmx);
			f.findField('default_quota').setDisabled(backupmx);
		}
	},	
	
	buildForm : function () {

		this.mailboxesGrid = new GO.postfixadmin.MailboxesGrid();   

		if(GO.settings.modules.postfixadmin.write_permission){
			this.propertiesPanel = new Ext.Panel({
				title:t("Properties"),
				cls:'go-form-panel',waitMsgTarget:true,
				layout:'form',
				autoScroll:true,
				items:[this.selectUser = new GO.form.SelectUser({
					fieldLabel: t("User"),
					disabled: !GO.settings.modules['postfixadmin']['write_permission'],
					value: GO.settings.user_id,
					anchor: '-20'
				}),{
					xtype: 'textfield',
					name: 'domain',
					anchor: '-20',
					allowBlank:false,
					fieldLabel: t("Domain", "postfixadmin")
				},{
					xtype: 'textfield',
					name: 'description',
					anchor: '-20',
					fieldLabel: t("Description")
				},new GO.form.NumberField({
					decimals:"0",
					disabled:!GO.settings.modules.postfixadmin.write_permission,
					name: 'max_aliases',
					anchor: '-20',
					allowBlank:false,
					fieldLabel: t("Max aliases", "postfixadmin"),
					value:'0'
				}),new GO.form.NumberField({
					decimals:"0",
					disabled:!GO.settings.modules.postfixadmin.write_permission,
					name: 'max_mailboxes',
					anchor: '-20',
					allowBlank:false,
					fieldLabel: t("Max mailboxes", "postfixadmin"),
					value:'0'
				}),this.maxQuotaField = new GO.form.NumberField({
					decimals:"0",
					disabled:!GO.settings.modules.postfixadmin.write_permission,
					name: 'total_quota',
					anchor: '-20',
					allowBlank:false,
					fieldLabel: t("Max quota (MB)", "postfixadmin"),
					value:'0'
				}),this.quotaField = new GO.form.NumberField({
					decimals:"0",
					name: 'default_quota',
					anchor: '-20',
					allowBlank:false,
					fieldLabel: t("Default quota (MB)", "postfixadmin"),
					value:'0'
				}),{
					xtype: 'xcheckbox',
					name: 'active',
					anchor: '-20',
					boxLabel: t("Active", "postfixadmin"),
					hideLabel: true,
					checked: true
				},{
					xtype: 'xcheckbox',
					name: 'backupmx',
					anchor: '-20',
					boxLabel: t("Backup MX", "postfixadmin"),
					hideLabel: true,
					listeners:{
						check:function(cb, check){
							this.setBackupMX(check);

						},
						scope:this
					}
				}]

			});
			
			this.addPanel(this.propertiesPanel);
		}
    
    
		this.addPanel(this.mailboxesGrid, 'domain_id');
    
		this.aliasesGrid = new GO.postfixadmin.AliasesGrid();   
		this.addPanel(this.aliasesGrid,'domain_id');		

		this.addPermissionsPanel(new GO.grid.PermissionsPanel({
			hideLevel:true,
			addLevel: GO.permissionLevels.writeAndDelete
		})); 
	}
});
