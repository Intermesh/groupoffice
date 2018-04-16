go.modules.comments.QuickaddForm = Ext.extend(Ext.form.FormPanel, {
	
	
	initComponent : function(){
		
		this.saveButton = new Ext.Button({
			text: t('Save'),
			handler: this.submitForm,
			scope:this
		});
		
		Ext.apply(this,{
			closeAction:'hide',
			cls: 'go-form-panel',
			items:[
				new go.modules.comments.CategoryCombo(),
				{
					xtype: 'xhtmleditor',
					name: 'comment',
					fieldLabel: "",
					hideLabel: true,
					anchor: '100%',
					height: 116,
					allowBlank: false
				}
			],
			buttons:[
				this.saveButton
			]
		});
		
		this.addEvents({
			'submitStart' : true,
			'submitComplete' : true
		});
		
		go.modules.comments.QuickaddForm.superclass.initComponent.call(this);
	},
	
	setBaseParams: function(baseParams){
		this.baseparams = baseParams;
	},
	
	getSubmitValues : function() {
		return this.formPanel.getForm().getFieldValues();
	},

	submitForm: function () {

		if (!this.formPanel.getForm().isValid()) {
			return;
		}

		var id, params = {}, values = this.getSubmitValues();
		
		id = Ext.id();
		params.create = {};
		params.create[id] = values;
		
		this.actionStart();
		this.fireEvent('submitStart',this);
		
		this.entityStore.set(params, function (options, success, response) {
			var saved = response.created || {};
			if (saved[id]) {
				var serverId = response.created[id].id;
				this.entityStore.entity.goto(serverId);
				this.submitComplete(response);
			} else {
				for(name in response.notUpdated[id].validationErrors) {
					var field = this.formPanel.getForm().findField(name);
					if(field) {
						field.markInvalid(response.notUpdated[id].validationErrors[name].description);
					}
				}
			}
		}, this);

	},
	
	/**
	 * call this function when submitting of all tabs is completed
	 */
	submitComplete: function(result){
		this.fireEvent('submitcomplete',this, result);
		this.actionComplete();
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