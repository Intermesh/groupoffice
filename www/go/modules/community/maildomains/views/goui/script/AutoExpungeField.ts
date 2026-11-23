import {checkbox, containerfield, numberfield, p, t} from "@intermesh/goui";

/**
 * Field for a Dovecot auto expunge period like "30d". "0" means disabled.
 *
 * @param name The property name, e.g. "autoExpunge"
 */
export function autoExpungeField(name: string) {
	return containerfield({
			name: name,

			listeners: {

				beforesetvalue: (e) => {
					if(e.value == "0") {
						e.value = {enabled: false, days: 30}
					} else {
						e.value = {enabled: true, days: parseInt(e.value.substring(0, e.value.length-1))}
					}
					e.target.findField("days")!.disabled = !e.value.enabled;

				},

				beforegetvalue: ( e) => {

					if(!e.value.enabled) {
						e.value = "0";
					} else {
						e.value = e.value.days + "d";
					}
				}
			},
		},
		checkbox({
			label: t("Auto expunge"),
			type: "switch",
			value: true,
			name: "enabled",
			listeners: {
				change:( {newValue, target}) => {
					const p = target.nextSibling()!
					p.disabled = !newValue;
					p.nextSibling()!.disabled = !newValue;
				},
			}
		}),

		p({text: t("Automatically delete mail from the Trash and Spam folder after a period of time.")}),

		numberfield({
			flex: 1,
			name: "days",
			decimals: 0,
			label: t("Expunge after days"),
			value: 30
		})
	);
}
