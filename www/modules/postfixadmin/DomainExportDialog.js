GO.postfixadmin.DomainExportDialog = Ext.extend(GO.dialog.TabbedFormDialog,{

	initComponent : function(){
		
		this.id = null;
		this.domain = null;
		
		
		var buttons = [];
		
		this.buttonDownload = new Ext.Button({
			text: GO.lang['download'],
			handler: function(){
				if(this.resetPasswordsCbx.getValue()){
					if(confirm(GO.postfixadmin.lang.resetPasswordsConfirm)){
						this.submitForm(true);
					}
				} else {
					this.submitForm(true);
				}
			},
			scope: this
		});
		
		buttons.push(this.buttonDownload);
		
		Ext.apply(this, {
			formPanelConfig : {
				standardSubmit:true
			},
			title: GO.postfixadmin.lang.exportDomain,
			formControllerUrl: 'postfixadmin/domain',
			width:500,
			height:220,
			loadOnNewModel:false,
			enableApplyButton : false,
			submitAction: 'DomainExport',
			buttons:buttons
		});
		
		GO.postfixadmin.DomainExportDialog.superclass.initComponent.call(this);
	},
	
	show:function(data){
		
		this.remoteModelId = data.remoteModelId;
		this.domain = data.domain;
		
		if(!this.origTitle)
			this.origTitle=this.title;
		
		this.setTitle(Ext.util.Format.htmlEncode(this.origTitle+": "+this.domain));
		
		this.remoteModelIdField.setValue(this.remoteModelId);
		this.domainField.setValue(this.domain);
		
		GO.postfixadmin.DomainExportDialog.superclass.show.call(this);
	},
	
	buildForm : function () {
		
		this.resetPasswordsCbx = new Ext.ux.form.XCheckbox({
			hideLabel:true,
			boxLabel: GO.postfixadmin.lang.resetPasswords,
			name: 'resetPasswords'
		});
		
		this.domainField = new Ext.form.Hidden({
			name: 'domain'
		});
		
		this.remoteModelIdField = new Ext.form.Hidden({
			name: 'remoteModelId'
		});
		
		this.resetPasswordsCbx = new Ext.ux.form.XCheckbox({
			hideLabel:true,
			boxLabel: GO.postfixadmin.lang.resetPasswords,
			name: 'resetPasswords'
		});
		
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],
			waitMsgTarget:true,
			layout:'form',
			autoScroll:true,
			items:[{
					border:false,
					html:	GO.postfixadmin.lang.exportDomainText
				},
				{
					html:'<hr>'
				},
				this.resetPasswordsCbx,
				this.domainField,
				this.remoteModelIdField
			]
		});
		
		this.addPanel(this.propertiesPanel);
	},
	
	submitForm : function(){
		this.formPanel.form.getEl().dom.target='_blank';
		this.formPanel.form.getEl().dom.action = GO.url(this.formControllerUrl+'/'+this.submitAction);
		
		this.formPanel.form.submit();		
		
		this.hide();
	}
	
});