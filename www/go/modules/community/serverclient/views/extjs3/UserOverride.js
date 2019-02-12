Ext.onReady(function(){
	Ext.override(go.users.CreateUserAccountPanel, {
		initComponent : go.users.CreateUserAccountPanel.prototype.initComponent.createSequence(function(){
			if(GO.serverclient && GO.serverclient.domains) {				
				

				for(var i=0;i<GO.serverclient.domains.length;i++)
				{
					this.serverclientDomainCheckboxes[i]=new Ext.form.Checkbox({						
						checked:(i==0),
						//name: 'serverDomains',
						name: 'serverDomains',
						autoCreate: {tag: "input", type: "checkbox", value: GO.serverclient.domains[i]},						
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
					items:items
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
						if(!success) {
							alert('Could not create mailbox');
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
				for(var i=0;i<this.serverclientDomainCheckboxes.length;i++)
				{
					if(this.serverclientDomainCheckboxes[i].getValue())
					{
						var username = this.form.findField('username').getValue();
						var emailField = this.form.findField('email');

						if(emailField)
							this.form.findField('email').setValue(username+'@'+GO.serverclient.domains[i]);

						break;
					}
				}
			}	
		}
	})
});