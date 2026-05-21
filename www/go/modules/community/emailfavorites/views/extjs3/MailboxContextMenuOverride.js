GO.mainLayout.on('authenticated', (mainLayout, user, password) => {
	if(!go.Modules.isAvailable("community", "emailfavorites") || !go.Modules.isAvailable("legacy", "email")) {
		return;
	}
	GO.email.extraTreeContextMenuItems.push(
		'-',
		new Ext.menu.Item({
			iconCls: "ic-star",
			text: t("Add to favorites", "email"),
			handler: function () {
				const node = this.parentMenu.node;

				const account_id = node.attributes.account_id;
				const mailbox = node.attributes.mailbox;
				const name = node.attributes.name;

				if (account_id && mailbox && name) {
					const record = go.Db.store("Favoritefolder").findBy(item => item.mailbox === mailbox && item.account_id === account_id && item.userId == go.User.id);

					if (!record) {
						go.Db.store("Favoritefolder").save({
							account_id: account_id,
							userId: go.User.id,
							mailbox: mailbox,
							name: name
						});
					}
				}
			}
		})
	);
})
