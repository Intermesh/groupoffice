
go.Modules.register("community", "googleauthenticator");

GO.mainLayout.on('authenticated', (mainLayout, user, password) => {

	if(user.googleauthenticator && user.googleauthenticator.isEnabled) {
		return;
	}

	if(!go.modules.community.googleauthenticator.isEnforced(user)) {
		return;
	}

	go.modules.community.googleauthenticator.enable(user, password);

});


go.modules.community.googleauthenticator.isEnforced = (user) => {
	const enforceForGroupId = go.Modules.get("community", "googleauthenticator").settings.enforceForGroupId;

	return enforceForGroupId && user.groups.indexOf(enforceForGroupId) > -1;
}

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

		let msg = t("Provide your current password before you can enable Google authenticator.");

		if(go.modules.community.googleauthenticator.isEnforced(user)) {

			msg = "<p class='info'><i class='icon'>info</i> " + t("Your system administrator requires you to setup two factor authentication") + '</p>' + msg;
		}

		return go.AuthenticationManager.passwordPrompt(
			t('Enable Google authenticator'),
			msg)

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