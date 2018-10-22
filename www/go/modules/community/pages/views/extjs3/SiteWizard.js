go.modules.community.pages.SiteWizard = Ext.extend(go.Wizard, {
	title: t('Create user'),
	site : null,
	initComponent : function() {
		
		//store all form data here
		this.site = {};
		
		this.propForm = new go.modules.community.pages.SitePropertiesForm({
			});
		this.sharePanel = new go.form.EntityPanel({
			entityStore: go.Stores.get("Group"),
			items: [
			    new go.modules.core.core.SharePanel({
				anchor: '100% -' + dp(32),
				hideLabel: true,
				name: "groups"
			})]
			
		});
		
	
		this.items = [
			this.propForm,
			this.sharePanel
		]
		go.modules.community.pages.SiteWizard.superclass.initComponent.call(this);
		this.on({
			continue: this.onContinue,
			finish: this.onFinish,
			afterrender: this.onAfterRender,
			scope: this
		});
		
	},
	onAfterRender: function(){
	    this.sharePanel.items.first().store.load();
	},
	onContinue: function(wiz, item, nextItem) {
		
		this.applyPanelData(item);
	},
		
	applyPanelData : function(item) {
		if(item == this.propForm) {
			this.site = Ext.apply(this.site, item.getForm().getValues());
		}
	},
	
	onSaveSuccess : function(response){
		
	},
	
	onFinish: function(wiz, lastItem) {
		this.applyPanelData(lastItem);
		
		
		
		var id = Ext.id(), params = {};
		params.create = {};
		params.create[id] = this.site;
		
		
		go.Stores.get("Site").set(params, function (options, success, response) {
			console.log(response);
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
		
		this.sharePanel.currentId = id;
		this.sharePanel.submit(function(){},this);
		
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

