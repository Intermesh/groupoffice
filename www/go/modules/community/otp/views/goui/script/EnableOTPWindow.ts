import {
	browser,
	btn,
	Button,
	ButtonEventMap,
	comp,
	fieldset,
	form,
	img,
	p, secrets,
	t,
	tbar,
	textfield,
	Window
} from "@intermesh/goui";
import * as QRCode from "qrcode";
import {client} from "@intermesh/groupoffice-core";



interface TotpUriOptions {
	secret: string;      // Base32-encoded secret
	accountName: string; // e.g. user's email or username
	issuer: string;      // e.g. your app/company name
	digits?: number;     // default 6
	period?: number;     // default 30 (seconds)
	algorithm?: 'SHA1' | 'SHA256' | 'SHA512'; // default SHA1
}

function generateTotpURI({
													 secret,
													 accountName,
													 issuer,
													 digits = 6,
													 period = 30,
													 algorithm = 'SHA1',
												 }: TotpUriOptions): string {
	const label = encodeURIComponent(`${issuer}:${accountName}`);

	const params = new URLSearchParams({
		secret,
		issuer,
		algorithm,
		digits: digits.toString(),
		period: period.toString(),
	});

	return `otpauth://totp/${label}?${params.toString()}`;
}

class EnableOTPWindow extends Window {
	private setupLaterBtn?: Button<ButtonEventMap>;
	constructor() {
		super();

		this.title = t("Enable OTP Authenticator");
		this.modal = true;
		this.width = 600;
		this.height = 800;
		this.closable = false;
	}

	public async init(username:string, countDown:number = 0, block = false) : Promise<string> {

		return new Promise(async (resolve, reject) => {
			const secret = secrets.otp();

			// Create otpauth:// URI
			const uri = generateTotpURI({
				issuer: "GroupOffice",
				accountName: username,
				secret,
			});

			const qrDataUrl = await QRCode.toDataURL(uri), rej = () => {
				reject();
			};

			this.on("close", rej, {once: true})

			this.items.add(
				form({
						cls: "fit vbox",
						handler: async (form1) => {

							const tokenField = form1.findField("code")!, code = tokenField.value;
							try {
								const result = await client.jmap("community/otp/Secret/verify", {secret, code});

								if (result.valid) {
									tokenField.clearInvalid();

									resolve(secret);
									this.un("close", rej);
									this.close();

								} else {
									tokenField.setInvalid(t("Invalid code"));
								}
							} catch(e:any) {
								tokenField.setInvalid(e.message);
							}
						}
					},

					comp({flex: 1},
						fieldset({},

							p(t("Scan the QR code below with the OTP Authenticator app on your mobile device, after that fill in the field below with the code generated in the app.")),

							img({
								style: {margin: "1.6rem auto"},
								cls: "frame",
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
								autocomplete: "one-time-code",
								label: t("Code"),
								required: true,
								name: 'code',
								maxLength: 6,
								minLength: 6
							})
						)
					),

					tbar({},
						this.setupLaterBtn = btn({
							hidden: block,
							disabled: countDown > 0,
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

			if(countDown > 0) {
				let currentCountDown = countDown;

				this.setupLaterBtn.text = t("Setup later") + " (" + currentCountDown-- + ")";

				const interval = setInterval(() => {
					let text = t("Setup later") + " (" + currentCountDown-- + ")"
					if (currentCountDown == -1) {
						text = t("Setup later");
						this.setupLaterBtn!.disabled = false;
						clearInterval(interval);
					}
					this.setupLaterBtn!.text = text;
				}, 1000);
			}

			this.show();

		});


	}
}

export async function enableOTP (username: string, countDown:number = 0, block = false) {
	const win = new EnableOTPWindow();


	return win.init(username, countDown, block);
}