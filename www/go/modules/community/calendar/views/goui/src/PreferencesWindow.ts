import {FormWindow} from "@intermesh/groupoffice-core";
import {checkbox, containerfield, select, t} from "@intermesh/goui";
import {calendarStore} from "./Index.js";

export class PreferencesWindow extends FormWindow {
	constructor() {
		super('User');
		this.title = t('Preferences');
		this.generalTab.cls = 'flow pad scroll';
		this.generalTab.items.add(containerfield({name:'calendarPreferences'},
			//checkbox({name:'useTimeZones', label: t('Enable multiple time zone support')}),
			checkbox({name:'showWeekNumbers', label:t('Show week numbers in calendar')}),
			checkbox({name:'showDeclined', label: t('Show events that you have declined')}),
			select({name:'defaultCalendarId', label: t('Default calendar'), store: calendarStore, valueField: 'id',
				hint: t('Invitation to event will be added into this calendar')}),
			checkbox({name:'autoAddInvitations',label:t('Automatically add invitation to your calendar'),
				hint: t('Whenever an event invitation is received, add the event to your default calendar')}),
			checkbox({name:'autoUpdateInvitations', label: t('Automatically apply updates from organizer'),
				hint: t('Whenever an update to an event already in your calendar is received, update the event, or delete it if the event is cancelled')}),
			select({name:'weekViewGridSnap', label: t('Raster size for day/week view'),
				hint: t('The duration adjustment when resizing the event'),options: [
					{value:'5', name: '5 '+t('minutes')},
					{value:'15',name:  '15 '+t('minutes')},
					{value:'30',name:  '30 '+t('minutes')},
					{value:'60',name:  '1 '+t('hour')}
				]}),
			select({name:'startView', label:t('Default view when opening the calendar'),options: [
					{value:'day', name: t('Day')},
					{value:'week',name:  t('Week')},
					{value:'month',name:  t('Month')},
					{value:'year',name:  t('Year')},
					{value:'list',name:  t('List')}
			]}),
			select({name:'defaultDuration', label:t('Default duration'),
				hint: t('Duration for a new event when no range is selected'),options: [
				{value:'PT30M', name: '30 '+t('minutes')},
				{value:'PT1H',name:  '1 '+t('hour')},
				{value:'PT2H',name:  '2 '+t('hours')},
				{value:null,name:  t('All day')}
			]})
		));
	}
}