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
					this.refreshNeeded = false;
					this.setAccountId(account_id);
					this.selectUser.setRemoteText(action.result.remoteComboTexts.user_id);
					this.aliasesButton.setDisabled(false);
					this.foldersTab.setDisabled(false);
					if(!action.result.data.email_enable_labels) {
						this.tabPanel.hideTabStripItem(this.labelsTab);
					} else {
						this.tabPanel.unhideTabStripItem(this.labelsTab);
					}
					this.permissionsTab.setAcl(action.result.data.acl_id);
					if(this.selectAuthMethodCombo.getValue()) {
						this.btnGetRefreshToken.show();
						this.incomingTab.hide();
						this.outgoingTab.hide();
					}
				},
				scope : this
			});
			return false;
		})
	});

});
