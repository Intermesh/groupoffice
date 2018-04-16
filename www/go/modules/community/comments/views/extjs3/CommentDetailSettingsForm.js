go.modules.comments.CommentDetailSettingsForm = Ext.extend(go.Window, {
	title: t("Settings"),
	modal:true,
	resizable:false,
	maximizable:false,
	iconCls: 'ic-settings',
	currentUser:null,

	initComponent: function () {
		
		this.saveButton = new Ext.Button({
			text: t('Save'),
			handler: this.submit,
			scope:this
		});
				
		this.formPanel = new Ext.form.FormPanel({
			waitMsgTarget:true,
			region:'center',
			cls: 'go-form-panel',
			baseParams : {},
			items:[
				this.cbEnableQuickAdd = new Ext.ux.form.XCheckbox({
					hideLabel: true,
					boxLabel: t("Enable quick add form", "comments"),
					name: 'commentSettings.enableQuickAdd'
				})
			]
		});
		
		Ext.apply(this,{
			width:dp(300),
			height:dp(180),
			layout:'border',
			closeAction:'hide',
			items: [
				this.formPanel
			],
			buttons:[
				this.saveButton
			]
		});
		
		this.addEvents({
			'loadStart' : true,
			'loadComplete' : true,
			'submitStart' : true,
			'submitComplete' : true
		});
		
		go.modules.comments.CommentDetailSettingsForm.superclass.initComponent.call(this);
		
		// When the form is loaded, reset the 'modified' state to NOT modified.
		this.formPanel.getForm().trackResetOnLoad  = true;
		
	},
	
	/**
	 * The show function of this dialog.
	 * This immediately starts loading all tabpanels in this dialog.
	 * 
	 * @param int userId
	 */
	show: function(userId){
		this.currentUser = userId;

		go.modules.comments.CommentDetailSettingsForm.superclass.show.call(this);
				
		this.load();
	},
	
	/**
	 * Initiates the load of all tabpanels.
	 * 
	 * @param int userId (Optional)
	 */
	load : function(userId){
		this.currentUser = userId ? userId : this.currentUser;
		
		this.actionStart();
		this.fireEvent('loadstart',this, this.currentUser);

		go.Stores.get("User").get([this.currentUser], function(users){
			this.formPanel.getForm().setValues(users[0]);
			this.loadComplete(users[0]);
		}, this);

	},
	
	submit : function(){
		this.actionStart();
		this.fireEvent('submitStart',this);
		
		var id, params = {}, values =  this.formPanel.getForm().getFieldValues(true);
		
		//		//this.id is null when new
		if(this.currentUser) {
			id = this.currentUser;
			params.update = {};
			params.update[this.currentUser] = values;
		} else {			
			id = Ext.id();
			params.create = {};
			params.create[id] = values;
		}

		go.Stores.get("User").set(params, function(options, success, response){
		
			if(response.updated && response.updated[id]){
				this.submitComplete(response);
			} else
			{
				for(name in response.notUpdated[id].validationErrors) {
					var field = this.formPanel.getForm().findField(name);
					if(field) {
						field.markInvalid(response.notUpdated[id].validationErrors[name].description);
					}
				}
			}

		},this);
	},
		
	/**
	 * call this function when loading of all tabs is completed
	 */
	loadComplete: function(data){
		this.fireEvent('loadcomplete', this, data);
		this.actionComplete();
	},
		
	/**
	 * call this function when submitting of all tabs is completed
	 */
	submitComplete: function(result){
		this.fireEvent('submitcomplete',this, result);
		this.actionComplete();
		this.hide();
	},	
		
	/**
	 * Call this function when an action is started.
	 * This will disable all action buttons of this window.
	 */
	actionStart : function() {
		if(this.getBottomToolbar()) {
			this.getBottomToolbar().setDisabled(true);
		}
		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(true);
		}
		
		if(this.getFooterToolbar()) {
			this.getFooterToolbar().setDisabled(true);
		}
	},
	
	/**
	 * Call this function when an action is completed.
	 * This will enable all action buttons of this window again.
	 */
	actionComplete : function() {
		if(this.getBottomToolbar()) {
			this.getBottomToolbar().setDisabled(false);
		}
		
		if(this.getTopToolbar()) {
			this.getTopToolbar().setDisabled(false);
		}
		if(this.getFooterToolbar()) {
			this.getFooterToolbar().setDisabled(false);
		}
	}	
});
