import {
	checkbox,
	colorfield,
	combobox,
	comp,
	hiddenfield,
	textarea,
	textfield,
} from "@intermesh/goui";
import {client, FormWindow, modules, principalDS} from "@intermesh/groupoffice-core";
import {alertfield} from "./AlertField.js";
import {t} from "./Index.js";
import {publishFields} from "./PublishField.js";

export class CalendarWindow extends FormWindow {


	constructor() {
		super('Calendar');
		this.title = 'calendar';
		this.width = 460;
		this.height = 856;

		this.on('beforerender', () => {
			this.title = t(this.form.currentId ? 'Edit calendar' : 'Create calendar');
		})

		const alertField = alertfield({name: 'defaultAlertsWithTime',isForDefault:true, label:t('Events with time')}),
			fdAlertField = alertfield({name: 'defaultAlertsWithoutTime',isForDefault:true, fullDay:true, label:t('Events without time (Full-day)')});

		// The owner of a calendar gets the notifications that others set when creating an event already
		const useCreatorAlertsCb = checkbox({name: 'useCreatorAlerts', label: t('Use the notifications of the creator'),
			hint: t('Get the notifications that the creator set on an event instead of your default notifications')});

		const ownerIdField = combobox({
				dataSource: principalDS, placeholder: t('Shared'),displayProperty: 'name', filter: {entity: 'User'},
				label: t("Owner"), name: "ownerId", filterName: "text", flex:'1 0', clearable:true
			}).on('setvalue', (e) => {
				includeInAvailability.value = !availabilityAffectCb.value ? 'none' : e.newValue == client.user.id ? 'all' : 'attending';
			}),
			availabilityAffectCb = checkbox({label: t('Events affect availability')}).on('setvalue', (e) => {
				const allOrAttending = ownerIdField.value == client.user.id ? 'all' : 'attending';
				includeInAvailability.value = e.newValue ? allOrAttending : 'none';
			}),
			includeInAvailability = hiddenfield({name: 'includeInAvailability'}).on('setvalue', (e) => {
				availabilityAffectCb.value = e.newValue!=='none'
			}),
			descriptionFld = textarea({name:'description', label: t('Description'), autoHeight:true}),
				nameFld = textfield({name: 'name', label: t('Name'), flex:1}),
			{publishField, items: publishItems} = publishFields(this.form, () => {
				this.closeOnSave = false;
			});

		this.generalTab.items.add(
			comp({cls:'flow pad'},
				nameFld,
				colorfield({name: 'color', label: t('Color'), width: 100, required: true}),
				descriptionFld,
				// radio({style:{'width':'auto'}, type:'button',itemId:'type', value: 'personal', options: [
				// 	{text:t('Personal'), value: 'personal'},
				// 	{text:t('Shared'), value: 'shared'}
				// ]}),
				ownerIdField,
				availabilityAffectCb,
				includeInAvailability,
				checkbox({name: 'syncToDevice', label: t('Sync to device'), hint: t('Make calendar available in CalDAV and ActiveSync')}),
				...publishItems,
				comp({tagName:'h3',flex:'1 0 100%',text:t('Default notifications') }),
				alertField,
				fdAlertField,
				useCreatorAlertsCb
			),
		);

		alertField.drawOptions();
		fdAlertField.drawOptions();
		//
		// this.cards.items.add(comp({title: t('Categories')},
		// 	table({
		// 		columns: [
		// 			column({id: 'name', header: t('Name')})
		// 		],store:datasourcestore({dataSource:jmapds('CalendarCategory')})
		// 	})));

		this.addCustomFields();
		this.addSharePanel();

		this.form.on('beforeload', ({data}) => {
			// becasue this may be set to false during publish
			this.closeOnSave = true;

			const rights = modules.get("community", "calendar")!.userRights;
			const editable = data.id ? data.myRights.mayAdmin :rights.mayChangeCalendars,
				unsubscribed = ('id' in data && !data.isSubscribed);
			this.sharePanel!.disabled = !editable;
			this.generalTab!.disabled = unsubscribed;
			if(unsubscribed)
				this.sharePanel!.show();
			ownerIdField.hidden = !editable;
			useCreatorAlertsCb.hidden = !!data.ownerId && data.ownerId == client.user.id;
			descriptionFld.hidden = !editable;
			publishField.hidden = !editable || !data.id; // no publish button for new calendars
			nameFld.readOnly = !editable || !!data.davaccountId;
		})
	}
}