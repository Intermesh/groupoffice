import {
	checkbox,
	containerfield,
	DataSourceForm,
	datasourceform,
	DataSourceStore,
	datasourcestore,
	DefaultEntity,
	fieldset,
	Notifier,
	select
} from "@intermesh/goui";
import {AppSettingsPanel, client, JmapDataSource, jmapds, User} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

export class PreferencesPanel extends AppSettingsPanel {
	private form: DataSourceForm<User>;
	private calendarStore: DataSourceStore<JmapDataSource<DefaultEntity>>;
	private personalCalendarStore: DataSourceStore<JmapDataSource<DefaultEntity>>;

	constructor() {
		super();
		this.title = t('Calendar');
		// this.cls = 'fit scroll';

		this.calendarStore = datasourcestore({
			dataSource: jmapds('Calendar'),
			filters: {default: {davaccountId: null}},

			sort: [{property:'sortOrder'},{property:'name'}]
		});
		this.personalCalendarStore = datasourcestore({
			dataSource:jmapds('Calendar'),
			filters:{
				default:{davaccountId : null, isResource:false, permissionLevel:30/*writeOwn*/},
				owner:{ownerId:client.user.id},
				sub: {isSubscribedFor: client.user.id}
			},
			sort: [{property:'sortOrder'},{property:'name'}]
		})

		this.form = datasourceform<User>(
			{
				dataSource: jmapds("User"),
				cls: "vbox",
				flex: 1
			}
			,

			containerfield({name:'calendarPreferences'},
				fieldset({},
					checkbox({name:'showWeekNumbers', label:t('Show week numbers in calendar')}),
					checkbox({name:'showTooltips', label:t('Show pop-up info when hovering over appointments')}),
					checkbox({name:'showDeclined', label: t('Show events that you have declined')}),
					select({name:'defaultCalendarId', label: t('Default calendar'), store: this.calendarStore, valueField: 'id',
						hint: t('Start with this calendar selected')}),
					select({name:'personalCalendarId', label: t('Personal calendar'), store: this.personalCalendarStore, valueField: 'id',
						hint: t('Invitation to event will be added into this calendar')}),
					select({name:'weekViewGridSnap', label: t('Raster size for day/week view'),
						hint: t('The duration adjustment when resizing an event'),options: [
							{value:'5', name: '5 '+t('minutes')},
							{value:'15',name:  '15 '+t('minutes')},
							{value:'30',name:  '30 '+t('minutes')},
							{value:'60',name:  '1 '+t('hour')}
						]}),
					select({name:'weekViewGridSize', label: t('Height for day/week view'),
						hint: t('The height of a single day'),options: [
							{value:'4', name: t('Extra small')},
							{value:'5',name:  t('Small')},
							{value:'7',name:  t('Regular')},
							{value:'8',name:  t('Medium')},
							{value:'9',name:  t('Large')}
						]}),
					select({name:'startView', label:t('Default view when opening the calendar'),options: [
							{value:'day', name: t('Day')},
							{value:'week',name:  t('Week')},
							{value:'days-5',name:  t('Workweek')},
							{value:'weeks-14',name:  '2 ' +t('Weeks')},
							{value:'weeks-21',name:  '3 ' +t('Weeks')},
							{value:'month',name:  t('Month')},
							{value:'split-5',name:  t('Split')},
							{value:'year',name:  t('Year')},
							{value:'list',name:  t('List')}
						]}),
					select({name:'defaultDuration', label:t('Default duration'),
						hint: t('Duration for a new event when no range is selected'),options: [
							{value:'PT30M', name: '30 '+t('minutes')},
							{value:'PT1H',name:  '1 '+t('hour')},
							{value:'PT2H',name:  '2 '+t('hours')},
							{value:null, name:  t('All day')}
						]})
					//,checkbox({name:'useTimeZones', label: t('Enable multiple time zone support')}),
				),
				fieldset({legend:t('Process e-mail in')+': '+client.user.email},
					checkbox({name:'autoAddInvitations',label:t('Automatically add invitation to your calendar'),
						hint: t('Whenever an event invitation is received, add the event to your default calendar'),
						listeners: {'setvalue': ({target}) => {target.nextSibling()!.hidden = !target.value}}}),
					checkbox({name:'markReadAndFileAutoAdd', style:{marginLeft: '2.4rem'}, label:t('Mark invitation as read and archive'),hidden:true
						//,listeners: {'setvalue': b => {b.nextSibling()!.hidden = !b.value}}
					}),
					//select({name: 'autoAddFileIn', style:{marginLeft: '2.4rem'}, label:t('Archive folder'),value:'archive', options: [{value:'archive', name: t('Archive')}],hidden:true}),

					checkbox({name:'autoUpdateInvitations', label: t('Automatically apply updates from organizer'),
						hint: t('Whenever an update to an event already in your calendar is received, update the event, or delete it if the event is cancelled'),
						listeners: {'setvalue': ({target}) => {target.nextSibling()!.hidden = !target.value}}}),
					checkbox({name:'markReadAndFileAutoUpdate', style:{marginLeft: '2.4rem'}, label:t('Mark updates as read and archive'),hidden:true
						//,listeners: {'setvalue': b => {b.nextSibling()!.hidden = !b.value}}
					})
					//,select({name: 'autoUpdateFileIn', style:{marginLeft: '2.4rem'}, label:t('Archive folder'),value:'archive', options: [{value:'archive', name: t('Archive')}],hidden:true})
				)

			)
		)


		this.items.add(this.form);
	}

	async load(user:User) {
		this.form.value = user;
		this.form.currentId = user.id;

		this.personalCalendarStore
			.setFilter('owner', {ownerId: user.id})
			.setFilter("sub", {isSubscribedFor: user.id})
			.load().catch(e => Notifier.error(e))

		this.calendarStore.setFilter("sub", {isSubscribedFor: user.id});
		this.calendarStore.load().catch(e => Notifier.error(e))
	}

	async save(): Promise<any> {
		return this.form.submit();
	}


	// onSubmit() {
	// 	return this.form.submit()
	// }
}