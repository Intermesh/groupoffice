go.modules.core.users.CreateUserWizard = Ext.extend(go.Wizard, {
	title: t('Create user'),
	user : null,
	initComponent : function() {
		
		//store all form data here
		this.user = {};
	
		this.items = [
			new go.modules.core.users.CreateUserAccountPanel(),
			new go.modules.core.users.CreateUserPasswordPanel()
		]
		go.modules.core.users.CreateUserWizard.superclass.initComponent.call(this);
		
		this.on({
			continue: this.onContinue,
			finish: this.onFinish,
			scope: this
		});
	},
	
	onContinue: function(wiz, item, nextItem) {
		this.user = Ext.apply(this.user, item.getForm().getValues());
		console.log(this.user);
	},
	
	onFinish: function(wiz, lastItem) {
		this.user = Ext.apply(this.user, lastItem.getForm().getValues());
		
		console.log(this.user);
		
		var id = Ext.id(), params = {};
		params.create = {};
		params.create[id] = this.user;
		
		go.Stores.get("User").set(params, function (options, success, response) {

			if (response.created && response.created[id]) {				
				
				//var serverId = params.create ? response.created[id].id : response.updated[id].id;

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

			}
		}, this);
	},
	
	findField : function(name) {
		var field, pnl;
		for(var i = 0, l = this.items.getCount(); i < l; i++) {
			pnl = this.items.itemAt(i);
			field = pnl.getForm().findField(name);
			if(field) {
				return [field, pnl];
			}
		}
		return false;
	}
		
		
});

