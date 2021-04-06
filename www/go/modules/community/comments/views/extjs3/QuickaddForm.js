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
					xtype: 'ckeditor',
					name: 'text',
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
		return this.form.getFieldValues();
	},

	submitForm: function () {

		if (!this.form.isValid()) {
			return;
		}

		var id, params = {}, values = this.getSubmitValues();
		
		id = Ext.id();
		params.create = {};
		params.create[id] = values;
	
		this.fireEvent('submitStart',this);
		
		this.entityStore.set(params, function (options, success, response) {
			var saved = response.created || {};
			if (saved[id]) {
				var serverId = response.created[id].id;
				this.entityStore.entity.goto(serverId);
				this.submitComplete(response);
			} else {
				for(name in response.notUpdated[id].validationErrors) {
					var field = this.form.findField(name);
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
	}
	
	
});