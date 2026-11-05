


go.modules.community.addressbook.typeStoreData = function (langKey) {
	var types = [], typeLang = t(langKey);

	for (var key in typeLang) {
		types.push([key, typeLang[key]]);
	}
	return types;
};

go.modules.community.addressbook.importVcf = function(config) {
	Ext.MessageBox.confirm(t('Confirm'), t('Are you sure that you would like to import this VCard?'),
		function(btn) {
			if (btn !== "yes") {
				return;
			}
			Ext.getBody().mask(t("Importing..."));
			go.Jmap.request({
				method: "Contact/loadVCF",
				params: {
					fileId: config.id
					// account_id: panel.account_id,
					// mailbox: panel.mailbox,
					// uid: panel.uid,
					// number: attachment.number,
					// encoding: attachment.encoding
				},
				callback: function (options, success, response) {
					Ext.getBody().unmask();
					if (!success) {
						Ext.MessageBox.alert(t("Error"), response.errors.join("<br />"));
					} else {
						var dlg = new go.modules.community.addressbook.ContactDialog();
						dlg.load(response.contactId).show();
					}
				}
			});
		});
}

go.modules.community.addressbook.renderName = function(contact) {
	const sortBy = go.User.addressBookSettings.sortBy;
	let name;
	if(!contact.isOrganization && sortBy == 'lastName' && !go.util.empty(contact.lastName)) {
		name = contact.lastName + ', ' + contact.firstName;
		if(!go.util.empty(contact.middleName)) {
			name += " " + contact.middleName;
		}
	} else{
		name = contact.name;
	}

	return name;
};


go.modules.community.addressbook.lookUpUserContact = async (userId) => {
	//lookup in address book
	const ids = await go.Db.store("Contact").query({
		filter: {
			isUser: userId
		}
	}).then(r=>r.ids);

	if(!ids.length) {
		Ext.MessageBox.alert(t("Not found"), t("Could not find this user in the address book for you."));
	} else
	{
		go.Entities.get("Contact").goto(ids[0]);
	}
};

Ext.onReady(function () {
	if (!go.modules.business || !go.modules.business.newsletters) {
		return;
	}

	go.modules.business.newsletters.registerEntity({
		name: "Contact",
		grid: go.modules.community.addressbook.ContactGrid,
		add: function () {
			return new Promise(function (resolve, reject) {
				var select = new go.util.SelectDialog({
					entities: ['Contact'],
					mode: 'id',
					scope: this,
					selectMultiple: function (ids) {
						this.resolved = true;
						resolve(ids);
					},
					listeners: {
						close: function () {
							if (!this.resolved) {
								reject();
							}
						}
					}
				});
				select.show();
			});
		}
	});
});
