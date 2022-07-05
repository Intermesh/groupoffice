Ext.onReady(function(){
	Ext.override(go.users.CreateUserAccountPanel, {
		initComponent : go.users.CreateUserAccountPanel.prototype.initComponent.createSequence(function(){
			if(GO.serverclient && GO.serverclient.domains) {				
				

				for(var i=0;i<GO.serverclient.domains.length;i++)
				{
					this.serverclientDomainCheckboxes[i]=new Ext.form.Checkbox({						
						checked:(i==0),
						name: 'serverDomains',
						value: GO.serverclient.domains[i],				
						hideLabel:true,
						boxLabel: GO.serverclient.domains[i]
					});

					this.serverclientDomainCheckboxes[i].on('check', this.setDefaultEmail, this);
				}
				
				var items = this.serverclientDomainCheckboxes;
//				items.shift(new GO.form.HtmlComponent({
//					html:'<p class="go-form-text">'+t('Create a mailbox for domain')+':</p>'
//				}));
//				
				this.serverclientFieldSet = new Ext.form.FieldSet({
					title: t('Mailboxes'), 
					autoHeight:true,
					items: [
						new go.form.CheckboxGroup({
							hideLabel:true,
							items: items,
							name: "serverDomains"
						})
					]
				});
				

				this.add(this.serverclientFieldSet);

				this.on('render',function(){
					this.form.findField('username').on('change', this.setDefaultEmail, this);
				},this);
			

			}
		}),
		
		onSubmitStart : function(values) {
			//remove the domainvlaue from user
			this.serverDomains = values.serverDomains;
			if(!Ext.isArray(this.serverDomains)) {
				this.serverDomains = [this.serverDomains];
			}
			this.lastPassword = values.password;
			delete values.serverDomains;
		},
		
		onSubmitComplete : function(user, result) {
			// post domein value data removed in onsubmitstart
			if(this.serverDomains) {
				go.Jmap.request({
					method: 'community/serverclient/Serverclient/setMailbox',
					params: {
						userId: user.id, 
						domains: this.serverDomains, 
						password: this.lastPassword
					},
					callback: function(o,success,response) {
						console.error(response);
						if(!success) {
							Ext.MessageBox.alert(t("Error"), t("The mailbox couldn't be created") + ': ' + response.message);
						}
					}
				});
			}
			this.lastPassword = null;
			
		},
	
		serverclientDomainCheckboxes : [],

		setDefaultEmail : function(){

			if(this.rendered)
			{
				var username = this.form.findField('username').getValue();
				var emailField = this.form.findField('email');

				for(var i=0;i<this.serverclientDomainCheckboxes.length;i++)
				{
					if(this.serverclientDomainCheckboxes[i].getValue())
					{
						if(emailField) {
							var email = username.indexOf('@') > -1 ? username : username + '@' + GO.serverclient.domains[i];

							this.form.findField('email').setValue(email);
							this.form.findField('recoveryEmail').setValue(email);
						}
						break;
					}
				}
			}	
		}
	})
});