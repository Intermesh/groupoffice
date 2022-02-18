GO.moduleManager.onModuleReady('email',function() {
	Ext.override(GO.email.AccountDialog, {

		loadAccount: GO.email.AccountDialog.prototype.loadAccount.createInterceptor(function (account_id) {
			this.account_id = account_id;
			this.propertiesPanel.form.load({
				url : GO.url("email/account/load"),
				params : {
					id : account_id
				},
				waitMsg : t("Loading..."),
				success : function(form, action) {
					this.setAccountId(account_id);
					this.selectUser.setRemoteText(action.result.remoteComboTexts.user_id);
					this.aliasesButton.setDisabled(false);
					this.foldersTab.setDisabled(false);
					if (!action.result.data.email_enable_labels) {
						this.tabPanel.hideTabStripItem(this.labelsTab);
					} else {
						this.tabPanel.unhideTabStripItem(this.labelsTab);
					}
					this.permissionsTab.setAcl(action.result.data.acl_id);
					if (this.oauth2ClientCombo.getValue()) {
						this.btnGetRefreshToken.show();
						this.incomingTab.hide();
						this.outgoingTab.hide();
					}
					this.refreshNeeded = false;
				},
				scope : this
			});
			return false;
		}),

		save: GO.email.AccountDialog.prototype.save.createInterceptor(function(hide) {
			hide = hide || false;
			this.propertiesPanel.form.submit({
				url : GO.url("email/account/submit"),
				params : {
					'id' : this.account_id
				},
				waitMsg : t("Saving..."),
				success : function(form, action) {
					action.result.refreshNeeded = (this.refreshNeeded || this.account_id === 0);
					if (action.result.id) {
						this.loadAccount(action.result.id);
						if(action.result.needs_refresh_token) {
							hide = false;
							Ext.MessageBox.alert(t('Get a refresh token','oauth2client', 'community'),
								t('Please press the button "Refresh token" to finish the OAuth2 connection', 'oauth2client', 'community')
							);
							action.result.refreshNeeded = false;
							this.btnGetRefreshToken.fireEvent('click');
						}
					}

					//This will reload the signature when it is changed
					if(GO.email.composers && GO.email.composers[0]) {
						GO.email.composers[0].fromCombo.store.reload();
					}
					this.refreshNeeded = false;
					this.fireEvent('save', this, action.result);

					if (hide) {
						this.hide();
					}

				},

				failure : function(form, action) {
					let error = '';
					if (action.failureType === 'client') {
						error = t("You have errors in your form. The invalid fields are marked.");
					} else if (action.result) {
						error = action.result.feedback;
					} else {
						error = t("Could not connect to the server. Please check your internet connection.");
					}

					Ext.MessageBox.alert(t("Error"), error);

					if(action.result.validationErrors){
						for(let field in action.result.validationErrors){
							form.findField(field).markInvalid(action.result.validationErrors[field]);
						}
					}
				},
				scope : this

			});
			return false;
		})
	});

});
