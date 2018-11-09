go.modules.community.pages.SitePropertiesForm = Ext.extend(Ext.form.FormPanel, {
	currentId: null,
	isValid: function () {

		return  this.getForm().isValid();
	},
	
	initComponent: function () {
	    
		this.items = [{
				xtype: 'fieldset',
				title: t('Site properties'),
				items: [
					{
						xtype: 'textfield',
						name: 'siteName',
						fieldLabel: t("site name"),
						anchor: '100%',
						allowBlank: false,
					}, {
					    xtype: 'combo',
						name: 'documentFormat',
						fieldLabel: t("document format"),
						anchor: '100%',
						allowBlank: false,
						triggerAction: 'all',
						hiddenName: 'documentFormat',
						emptyText: t("Please select..."),
						editable: true,
						selectOnFocus: true,
						forceSelection: true,
						store: [['html', 'html'],['mark','markdown']],
						value: "html"
					},
				]
			}
		];
		 go.modules.community.pages.SitePropertiesForm.superclass.initComponent.call(this);
	},
	
	submit: function (cb, scope){
	    
		if (!this.isValid()) {
			return;
		}

		var id, params = {}, values = this.getForm().getFieldValues(this.currentId ? true : false);
		
		if (this.currentId) {

			id = this.currentId;
			params.update = {};
			params.update[this.currentId] = values;
		} else {

			id = Ext.id();
			params.create = {};
			params.create[id] = values;
		}
		
		go.Stores.get("Site").set(params, function (options, success, response) {

		var saved = (params.create ? response.created : response.updated) || {};
		if (id in saved) {				
			    this.fireEvent("save", this, values);
			    if(cb && response.created) {
				var serverId = params.create ? response.created[id].id : id;
				this.currentId = serverId;
				var aclId = response.created[id].aclId;
				cb.call(scope, this, true, aclId);
			    } else if(cb && response.updated){
				cb.call(scope, this, true, null);
			    }
			} else
			{
			//something went wrong
			var notSaved = (params.create ? response.notCreated : response.notUpdated) || {};
			if (!notSaved[id]) {
				notSaved[id] = {type: "unknown"};
			}
			switch (notSaved[id].type) {
				case "forbidden":
					Ext.MessageBox.alert(t("Access denied"), t("Sorry, you don't have permissions to update this item"));
					break;
				default:
						
					//mark validation errors
					for(name in notSaved[id].validationErrors) {
					var field = this.getForm().findField(name);
					if(field) {
						field.markInvalid(notSaved[id].validationErrors[name].description);
					    }
					}
					Ext.MessageBox.alert(t("Error"), t("Sorry, something went wrong. Please try again."));
					break;
				}
				if(cb) {
					cb.call(scope, this, false, null);
				}
			}
		}, this);
	}
	
});


