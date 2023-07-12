
go.Modules.register("community", "otp");

GO.mainLayout.on('authenticated', (mainLayout, user, password) => {

	if(!go.Modules.isAvailable("community", "otp")) {
		return;
	}

	if(user.otp && user.otp.isEnabled) {
		return;
	}

	if(!go.modules.community.otp.isEnforced(user)) {
		return;
	}

	const s = go.Modules.get("community", "otp").settings;

	go.modules.community.otp.enable(user, password, s.countDown, s.block);

});


go.modules.community.otp.isEnforced = (user) => {
	const enforceForGroupId = parseInt(go.Modules.get("community", "otp").settings.enforceForGroupId);

	return enforceForGroupId && user.groups.indexOf(enforceForGroupId) > -1;
}

go.modules.community.otp.enable = (user, password, countDown, block) => {

	function requestSecret (user, currentPassword){

		const data = {
			otp: {
				requestSecret:true
			}
		};
		if(currentPassword) {
			data.currentPassword = currentPassword;
		}
		Ext.getBody().mask(t("Loading..."));
		return go.Db.store("User").save(data, user.id)
			.then((user) => {
				const enableDialog = new go.modules.community.otp.EnableAuthenticatorDialog({
					block: block,
					countDown: countDown
				});
				enableDialog.load(user.id).show();
			})
			.catch((error) => {

				if(error.message && !error.response) {
					GO.errorDialog.show(error.message);
				}

				// When the password is not correct, call itself again to try again
				return go.modules.community.otp.enable(user, null, countDown, block);
			}).finally(() => {
				Ext.getBody().unmask();
			})
	}


	if(!go.User.isAdmin && !password) {

		let msg = t("Provide your current password before you can enable OTP Authenticator.");

		if(go.modules.community.otp.isEnforced(user)) {

			msg = "<p class='info'><i class='icon'>info</i> " + t("Your system administrator requires you to setup two factor authentication") + '</p>' + msg;
		}

		const passwordPrompt = () => {
			return go.AuthenticationManager.passwordPrompt(
				t('Enable OTP Authenticator'),
				msg,
				!countDown && !block
				)

				.then((password) => {
					return requestSecret(user, password);

				}).catch(() => {
					//user cancelled
					if(countDown || block) {
						// cancel not allowed
						return passwordPrompt();
					} else {
						this.close();
					}
			});
		}

		return passwordPrompt();
	} else
	{
		return requestSecret(user, password);
	}

}

Ext.getBody().dom.addEventListener('focus', () => {debugger});