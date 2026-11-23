import {client, createUserDialogExtensions, modules} from "@intermesh/groupoffice-core";
import {checkbox, CheckboxField, fieldset, t, TextField, Window} from "@intermesh/goui";

let domainsRequest: Promise<string[]> | undefined;

/**
 * The mail domains of the "serverclient_domains" config option. Loaded once.
 */
function getDomains(): Promise<string[]> {
	if (!domainsRequest) {
		domainsRequest = client.jmap("community/serverclient/Serverclient/domains", {})
			.then((response: {domains: string[]}) => response.domains)
			.catch((e: any) => {
				domainsRequest = undefined;
				throw e;
			});
	}
	return domainsRequest;
}

modules.register({
	package: "community",
	name: "serverclient",
	init: () => {

		// Offer a mailbox on the mail server of each domain when creating a user
		createUserDialogExtensions.push(async (dialog, container) => {
			let domains: string[];
			try {
				domains = await getDomains();
			} catch (e) {
				console.error(e);
				return;
			}

			if (!domains.length) {
				return;
			}

			// Not named, so they aren't sent with the user
			const checkboxes: CheckboxField[] = domains.map((domain, i) => checkbox({
				label: domain,
				value: i === 0,
				listeners: {
					change: () => setDefaultEmail()
				}
			}));

			container.items.add(fieldset({legend: t("Mailboxes", "community", "serverclient")}, ...checkboxes));

			const form = dialog.form,
				usernameFld = form.findField("username") as TextField,
				displayNameFld = form.findField("displayName") as TextField;

			const checkedDomains = () => domains.filter((_domain, i) => checkboxes[i].value);

			// The e-mail address of the first checked domain
			const setDefaultEmail = () => {
				const domain = checkedDomains()[0], username = usernameFld.value as string;
				if (!domain || !username) {
					return;
				}
				const email = username.indexOf("@") > -1 ? username : username + "@" + domain;
				form.findField("email")!.value = email;
				form.findField("recoveryEmail")!.value = email;
			};

			// Runs after the listener of the dialog that sets the username and e-mail from the name
			displayNameFld.on("change", () => setDefaultEmail());
			usernameFld.on("change", () => setDefaultEmail());

			// The password isn't returned by the server, so remember it to create the mailbox with
			let password: string | undefined;
			form.on("beforesave", ({data}) => {
				password = (data as any).password;
			});

			form.on("save", async ({data, isNew}) => {
				const selected = checkedDomains();
				if (!isNew || !selected.length) {
					return;
				}

				try {
					await client.jmap("community/serverclient/Serverclient/setMailbox", {
						userId: data.id,
						domains: selected,
						password
					});
				} catch (e: any) {
					void Window.error(t("The mailbox couldn't be created", "community", "serverclient") + (e?.message ? ": " + e.message : ""));
				} finally {
					password = undefined;
				}
			});
		});
	}
});
