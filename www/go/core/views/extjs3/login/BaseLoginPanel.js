go.login.BaseLoginPanel = Ext.extend(Ext.FormPanel, {
	
	initComponent : function() {
		go.login.BaseLoginPanel.superclass.initComponent.call(this);
		
		this.addEvents({success: true, cancel: true});
	},

	/**
	 * Get the post data for this form
	 * 
	 * @return array
	 */
	getPostData : function(){	
		
		var values = {};
		
		values[this.getId()] = this.getForm().getFieldValues();
		
		return values;
	},
	
	/**
	 * Set the errors of this form
	 * 
	 * @param {} errors
	 * @return {undefined}
	 */				
	setErrors: function(errors){
		
	},
	
	/**
	 * Reset the form to the default values. (Ususally clear it)
	 * @return {undefined}
	 */
	reset : function(){
//		console.log('reset: '+this.id);
		this.getForm().reset();
	},
	
	submit : function() {		

		if(this.submitting) {
			//prevent double submit
			return;
		}
		this.submitting = true;
		this.getEl().mask(t("loading..."));
		go.AuthenticationManager.doAuthentication(this.getPostData(),function(authMan, success, result){
			this.submitting = false;
			this.getEl().unmask();

			if(result.errors && result.errors[this.getId()]){
				this.setErrors(result.errors[this.getId()]);
				return;
			}

			this.onSuccess();

		},this);
	},
	
	focus : function() {
		var	field = this.getForm().items.find(function(item){
			if(!item.disabled && item.isVisible())
				return true;
			});	
		field.focus(true);
	},
	
	onSuccess : function() {
		this.fireEvent('success', this);
	},
	
	cancel : function() {
		this.fireEvent('cancel', this);
	}
});
