GO.moduleManager.onModuleReady('email',function() {
	Ext.override(GO.email.AccountDialog, {
		createOauthClientTab: function() {
			this.oauthClientTab = {
				title : t("OAuth2", "oauth2client", "community"),
				layout : 'form',
				defaultType : 'textfield',
				cls : 'go-form-panel',
				labelWidth : 120,
				items : [
					this.oauthClientIdFld = new Ext.form.TextField({
						fieldLabel : t("OAuth2 Client ID", 'oauth2client', 'community'),
						name : 'clientId',
						anchor: '80%',
						listeners : {
							change : function() {
								this.refreshNeeded = true;
							},
							scope : this
						}
					}),
					this.oauthClientSecretFld = new Ext.form.TextField({
						fieldLabel : t("OAuth2 Client Secret", 'oauth2client', 'community'),
						name : 'clientSecret',
						anchor: '80%',
						listeners : {
							change : function() {
								this.refreshNeeded = true;
							},
							scope : this
						}
					}),
					this.oauthProjectIdFld = new Ext.form.TextField({
						fieldLabel : t("OAuth2 Project Id", 'oauth2client', 'community'),
						name : 'projectId',
						anchor: '80%',
						listeners : {
							change : function() {
								this.refreshNeeded = true;
							},
							scope : this
						}
					}),
					this.btnGetRefreshToken = new Ext.Button({
						iconCls: 'ic-refresh',
						text: 'Refresh token',
						anchor: '20%',
						tooltip: t('Request or update a refresh token in a separate window.','oauth2client','community'),
						handler : function() {
							// TODO? This works, but it is ugly. Perhaps use a dialog with an iframe?
							window.open('/go/modules/community/oauth2client/gauth.php/authenticate/' + this.account_id, 'do_da_auth_thingy');
							this.refreshNeeded = true;
						},
						scope : this
					})

				]
			};
			this.tabPanel.add(this.oauthClientTab);

			this.doLayout();
		},
		loadAccount: GO.email.AccountDialog.prototype.loadAccount.createInterceptor(function (account_id) {
			this.createOauthClientTab();
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
					if (this.selectAuthMethodCombo.getValue()) {
						this.btnGetRefreshToken.show();
						this.incomingTab.hide();
						this.outgoingTab.hide();
						// WHY DO I HAVE TO SET THESE MANUALLY?
						this.oauthClientIdFld.setValue(action.result.data.clientId);
						this.oauthClientSecretFld.setValue(action.result.data.clientSecret);
						this.oauthProjectIdFld.setValue(action.result.data.projectId);
						this.tabPanel.unhideTabStripItem(this.oauthClientTab);
					} else {
						this.tabPanel.hideTabStripItem(this.oauthClientTab);
					}
					this.refreshNeeded = false;
				},
				scope : this
			});
			return false;
		})
	});

});
