go.login.BaseLoginPanel = Ext.extend(Ext.FormPanel, {

	/**
	 * Get the post data for this form
	 * 
	 * @return array
	 */
	getPostData : function(){	
		
		var values = {};
		
		values[this.id] = this.getForm().getFieldValues();
		
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
	
	submit : function(form) {
		
		go.AuthenticationManager.doAuthentication(form.wizard.getLayout().activeItem.getPostData(),function(authMan, success, result){
			
			var activeItemId = this.wizard.getLayout().activeItem.id;

			if(result.errors && result.errors[activeItemId]){
				this.wizard.getLayout().activeItem.setErrors(result.errors[activeItemId]);
				return
			}

			this.next();

		},form);
	},
	focus : function() {
		var	field = this.getForm().items.find(function(item){
			if(!item.disabled && item.isVisible())
				return true;
			});	
		field.focus(true);
	}
});