import {FormWindow, principalcombo} from "@intermesh/groupoffice-core";
import {browser, btn, comp, fieldset, t, tbar, textfield, win} from "@intermesh/goui";

export class KeyDialog extends FormWindow {
	constructor() {
		super("Key");

		this.title = t("API key");
		this.stateId = "key-dialog";

		this.resizable = true;

		this.generalTab.items.add(
			fieldset({},
				textfield({
					name: "name",
					label: t("Name"),
					required: true
				}),
				principalcombo({
					entity: "User",
					name: "userId",
					label: t("User"),
					required: true
				})
			)
		)

		// The access token is only returned once by the server, when the key is created
		this.form.on("save", ({data, isNew}) => {
			if(isNew && data.accessToken) {
				KeyDialog.showToken(data.accessToken);
			}
		});
	}

	/**
	 * Show the new access token once. Nothing is copied until the user asks.
	 */
	private static showToken(token: string): void {
		const w = win({
				title: t("Access token"),
				modal: true,
				closable: false,
				width: 560
			},
			comp({cls: "pad flow"},
				comp({html: t("Copy the access token now. It's not possible to view it again.")}),
				textfield({
					label: t("Access token"),
					value: token,
					readOnly: true,
					buttons: [btn({
						icon: "content_copy",
						title: t("Copy"),
						handler: () => browser.copyTextToClipboard(token)
					})]
				})
			),
			tbar({},
				"->",
				btn({
					text: t("Close"),
					cls: "filled primary",
					handler: () => w.close()
				})
			)
		);

		w.show();
	}
}
