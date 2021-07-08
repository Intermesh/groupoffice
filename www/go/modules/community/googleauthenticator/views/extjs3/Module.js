
go.Modules.register("community", "googleauthenticator");

GO.mainLayout.on('authenticated', (mainLayout, user, password) => {

	if(user.googleauthenticator && user.googleauthenticator.isEnabled) {
		return;
	}


	const enforceForGroupId = go.Modules.get("community", "googleauthenticator").settings.enforceForGroupId;

	if(!enforceForGroupId || go.User.groups.indexOf(enforceForGroupId) == -1) {
		return;
	}

	go.modules.community.googleauthenticator.enable(user, password);

});


go.modules.community.googleauthenticator.enable = (user, password) => {

	function requestSecret (user, currentPassword){

		const data = {
			googleauthenticator: {
				requestSecret:true
			}
		};
		if(currentPassword) {
			data.currentPassword = currentPassword;
		}
		return go.Db.store("User").save(data, user.id)
			.then((user) => {
				const enableDialog = new go.modules.community.googleauthenticator.EnableAuthenticatorDialog();
				enableDialog.load(user.id).show();
			})
			.catch((error) => {

				if(error.message && !error.response) {
					GO.errorDialog.show(error.message);
				}

				// When the password is not correct, call itself again to try again
				return go.modules.community.googleauthenticator.enable(user);
			});
	}


	if(!user.isAdmin && !password) {
		return go.AuthenticationManager.passwordPrompt(
			t('Enable Google authenticator'),
			t("Provide your current password before you can enable Google authenticator."))

			.then((password) => {
				return requestSecret(user, password);

			}).catch(() => {
				//user cancelled
				this.close();
			});
	} else
	{
		return requestSecret(user, password);
	}

}

Ext.getBody().dom.addEventListener('focus', () => {debugger});