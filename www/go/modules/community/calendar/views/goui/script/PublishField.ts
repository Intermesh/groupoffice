import {
	browser,
	btn,
	checkbox,
	CheckboxField,
	Component,
	DataSourceForm,
	hiddenfield,
	textfield
} from "@intermesh/goui";
import {client} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

/**
 * The switch to publish a calendar as ICS file and the field with its public URL. Used in the calendar and the
 * resource dialog.
 *
 * @param form The form of the calendar. It's saved when the switch changes, so the key of the URL is generated.
 * @param beforeSave Called before the form is saved by the switch, to keep the dialog open
 */
export function publishFields(form: DataSourceForm<any>, beforeSave: () => void): {publishField: CheckboxField, items: Component[]} {
	const publishField = checkbox({
			type: 'switch',
			name: 'publish',
			label: t('Publish calendar as ICS file'),
			hint: t('Only events from the first day of last month onward are published')
		}).on('change', () => {
			beforeSave();
			void form.submit().then(() => {
				void form.load(form.currentId!);
			});
		}),
		urlField = textfield({
			label: t("Public URL"),
			hidden: true, readOnly: true, buttons: [
				btn({icon: 'copy_all', title: t('Copy')})
					.on('click', () => {
						browser.copyTextToClipboard(urlField.value);
					})
			]
		}),
		keyField = hiddenfield({name: 'publishKey'}).on('setvalue', (e) => {
			urlField.hidden = !e.newValue;
			publishField.value = !!e.newValue;
			urlField.value = client.pageUrl('community/calendar/ics/' + e.newValue);
		});

	return {publishField, items: [publishField, urlField, keyField]};
}
