import {browser, btn, comp, fieldset, form, img, p, t, tbar, textfield, Window} from "@intermesh/goui";
import {generateSecret, generateURI, verify} from "otplib/functional";
import * as QRCode from "qrcode";

class EnableOTPWindow extends Window {
	constructor() {
		super();

		this.title = t("Enable OTP Authenticator");
		this.modal = true;
		this.width = 600;
		this.height = 800
	}

	public async init(username:string) : Promise<string> {

		return new Promise(async (resolve, reject) => {
			const secret = generateSecret();

			// Create otpauth:// URI
			const uri = generateURI({
				issuer: "GroupOffice",
				label: username,
				secret,
			});

			const qrDataUrl = await QRCode.toDataURL(uri), rej = () => {
				reject();
			};

			this.on("close", rej, {once: true})

			this.items.add(
				form({
						cls: "fit vbox",
						handler: form1 => {
							resolve(secret);
							this.un("close", rej);
							this.close();
						}
					},

					comp({flex: 1},
						fieldset({},

							p(t("Scan the QR code below with the OTP Authenticator app on your mobile device, after that fill in the field below with the code generated in the app.")),

							img({
								width: 200,
								src: qrDataUrl
							}),

							textfield({
								label: t("Secret"),
								value: secret,
								readOnly: true,
								buttons: [
									btn({
										icon: "content_copy",
										handler: button => {
											browser.copyTextToClipboard(secret);
										}
									})
								]
							})
						),

						fieldset({},
							textfield({
								label: t("Token"),
								required: true,
								name: 'verify',
								maxLength: 6,
								minLength: 6,
								listeners: {
									validate: async (ev) => {

										try {
											const result = await verify({secret, token: ev.target.value});

											if (result.valid) {
												ev.target.clearInvalid();
											} else {
												ev.target.setInvalid("Invalid token");
											}
										} catch(e:any) {
											ev.target.setInvalid(e.message);
										}
									}
								}
							})
						)
					),

					tbar({},
						btn({
							text: t("Setup later"),
							handler: () => {
								this.close();
							}
						}),
						"->",
						btn({
							type: "submit",
							text: t("Save")
						})
					)
				)
			)

			this.show();
		});


	}
}

export async function enableOTP (username: string) {
	const win = new EnableOTPWindow();

	return win.init(username);
}