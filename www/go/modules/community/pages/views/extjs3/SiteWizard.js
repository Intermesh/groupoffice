go.modules.community.pages.SiteWizard = Ext.extend(go.Wizard, {
	title: t('Create site'),
	height: dp(500),
	initComponent : function() {
		
		
		this.propForm = new go.modules.community.pages.SitePropertiesForm({
			});
		this.shareEntityPanel = new go.form.EntityPanel({
			entityStore: go.Stores.get("Acl"),
			items: [
			    new go.modules.core.core.SharePanel({
				anchor: '100% -' + dp(32),
				hideLabel: true,
				name: "groups",
				title: 'Site permissions'
			})]
			
		});
		
	
		this.items = [
			this.propForm,
			this.shareEntityPanel
		]
		go.modules.community.pages.SiteWizard.superclass.initComponent.call(this);
		this.on({
			finish: this.onFinish,
			afterrender: this.onAfterRender,
			scope: this
		});
		
	},
	onAfterRender: function(){
	    this.shareEntityPanel.items.first().store.load();
	    this.shareEntityPanel.items.first().setValue([{groupId: 1, level: 50}]);//,{isUserGroupFor: go.User.id, level: 50}]);
	},
	
	onSaveSuccess : function(response){
		this.shareEntityPanel.currentId = response.aclId;
	},
	
	onFinish: function(wiz, lastItem) {
	    
		var id = Ext.id(), params = {};
		params.create = {};
		params.create[id] = this.propForm.getForm().getValues();
		
		go.Stores.get("Site").set(params, function (options, success, response) {
			if (response.created && response.created[id]) {				
				
				this.onSaveSuccess(response.created[id]);
				this.close();
				
			} else
			{
				//something went wrong
				var notSaved = response.notCreated || {};
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
							var field = this.findField(name);
							if(field) {
								this.setActiveItem(field[1]);
								field[0].markInvalid(notSaved[id].validationErrors[name].description);
							}
						}
						
						Ext.MessageBox.alert(t("Error"), t("Sorry, something went wrong. Please try again."));
						break;
				}
				return;
			}
			
		}, this);
		if(this.shareEntityPanel.currentId){
		this.shareEntityPanel.submit();
		}
		
		
	},
	
	findField : function(name) {
		var field, pnl;
		pnl = this.propForm;
		field = pnl.getForm().findField(name);
		if(field) {
		    return [field, pnl];
		}
		return false;
	}
		
		
});

