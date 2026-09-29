import {datefield, fieldset, Notifier, t, textfield} from "@intermesh/goui";
import {client, FormWindow} from "@intermesh/groupoffice-core";
import * as openpgp from "openpgp";
import {privatePGPKeyDS} from "./Index.js";

export class KeyDialog extends FormWindow {
	private generatedPrivateKey: string | null = null;
	private generatedPublicKey: string | null = null;

	constructor() {
		super("PublicKey");

		this.title = t("Generate key-pair");
		this.height = 500;
		this.width = 400;
		this.stateId = "pgp-public-key-dialog";

		const emailField = textfield({
			name: "email",
			label: t("E-mail address"),
			required: true
		});

		const passphraseField = textfield({
			type: "password",
			label: t("Passphrase"),
			required: true
		});

		const expiresAtField = datefield({
			name: "expiresAt",
			label: t("Expires at"),
			required: true
		});

		this.generalTab.items.add(
			fieldset({},
				emailField,
				passphraseField,
				expiresAtField
			)
		);

		this.submitBtn.type = "button";
		this.submitBtn.on("click", async () => {
			if (!this.form.isValid()) {
				this.form.findFirstInvalid()?.focus();
				return;
			}

			this.submitBtn.disabled = true;
			this.mask();

			try {
				const keyExpirationTime = Math.floor(
					(new Date(expiresAtField.value!).getTime() - Date.now()) / 1000
				);

				const {privateKey, publicKey} = await openpgp.generateKey({
					type: "ecc",
					userIDs: [{email: emailField.value}],
					passphrase: passphraseField.value,
					keyExpirationTime,
					format: "armored"
				});

				this.generatedPrivateKey = privateKey;
				this.generatedPublicKey = publicKey;

				await this.form.handler!(this.form);
			} catch (e) {
				console.error(e);
				Notifier.error(t("Could not generate the key pair"));
			} finally {
				this.submitBtn.disabled = false;
				this.unmask();
			}
		});

		this.form.on("beforesave", ({data}) => {
			if (this.generatedPublicKey) {
				data.key = this.generatedPublicKey;
			}
		});

		this.form.on("save", async ({data}) => {
			if (!this.generatedPrivateKey) {
				return;
			}

			try {
				await privatePGPKeyDS.create({
					email: data.email,
					publicKeyId: data.id,
					key: this.generatedPrivateKey,
					expiresAt: expiresAtField.value,
					userId: client.user.id
				});
			} catch (e) {
				console.error(e);
				Notifier.error(t("Public key was saved, but the private key could not be stored"));
			} finally {
				this.generatedPrivateKey = null;
			}
		});
	}
}