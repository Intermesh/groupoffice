import {
	btn,
	t,
	tbar
} from "@intermesh/goui";
import {ModuleSettingsFieldset} from "@intermesh/groupoffice-core";
import {LdapAuthServerTable} from "./LdapAuthServerTable";
import {LdapAuthServerDialog} from "./LdapAuthServerDialog";

export class Settings extends ModuleSettingsFieldset {
	constructor() {
		super("LDAP Authenticator", "community", "ldapauthenticator");
		const tbl = new LdapAuthServerTable();
		void tbl.store.load();

		this.items.add(tbar({},
				"->",
				btn({
					icon: "add",
					cls: "filled primary",
					text: t("Add"),
					handler: () => {
						const dlg = new LdapAuthServerDialog();
						dlg.show();
					}
				})
			),
			tbl
		)
	}

	async load(): Promise<any>  {
		// Do not do anything
	}
}