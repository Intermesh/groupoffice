import {checkbox, Fieldset, HiddenField, hiddenfield, p, t} from "@intermesh/goui";
import {enableOTP} from "./EnableOTPWindow.js";

export class OtpSettingsFieldset extends Fieldset {
	private otpBtn;
	private otpField: HiddenField;
	constructor() {
		super();

		this.legend = t('Two Factor Authentication', "otp", "community");

		this.items.add(
			p(t("Setup one-time password authentication using an OTP application which generates a unique PIN for each login.", "otp", "community")),
			this.otpField = hiddenfield({
				name: "otp",
				listeners: {
					setvalue: ({newValue, oldValue}) => {
						this.otpBtn.value = !!newValue;
					}
				}
			}),
			hiddenfield({
				name: "currentPassword"
			}),
			this.otpBtn = checkbox({
				type: "switch",
				label: t("2FA Enabled"),
				listeners: {

					change: async (ev) =>		{

						if (!ev.newValue) {
							this.otpField.value = null;
						} else {
							try {
								const secret = await enableOTP("test");
								this.otpField.value = {secret};
							} catch(e) {
								this.otpBtn.value = false;
							}
						}
					}
				}
			})
		)
	}

}