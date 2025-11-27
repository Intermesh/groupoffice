import {
	autocomplete,
	autocompletechips, AutocompleteField,
	browser,
	btn,
	Button,
	checkbox,
	CheckboxField,
	checkboxselectcolumn,
	column,
	comp,
	containerfield,
	datasourcestore,
	DateInterval,
	DateTime,
	datetimefield,
	DateTimeField,
	Format, listStoreType,
	MapField,
	mapfield,
	Notifier,
	numberfield,
	radio,
	select,
	store, storeRecordType,
	table, TableColumn,
	textarea,
	textfield,
	TextField,
	win,
	Window,
} from "@intermesh/goui";
import {client, FormWindow, JmapDataSource, jmapds, principalDS, recurrencefield} from "@intermesh/groupoffice-core";
import {categoryStore, t, writeableCalendarStore} from "./Index.js";
import {ParticipantField, participantfield} from "./ParticipantField.js";
import {AlertField, alertfield} from "./AlertField.js";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {AvailabilityWindow} from "./AvailabilityWindow.js";
import {locationfield, LocationField} from "./LocationField";


export class EventWindow extends FormWindow {

	private item?: CalendarItem

	private store: JmapDataSource
	// private submitBtn:Button
	private endDate: DateTimeField
	private startDate: DateTimeField
	private withoutTimeToggle: CheckboxField

	private attachments:MapField
	private btnFreeBusy: Button
	private locationField: LocationField
	public readonly participantFld: ParticipantField

	private titleField: TextField
	private alertField: AlertField
	private confirmedScheduleMessage: boolean = false;

	constructor() {
		super("CalendarEvent");

		const m = go.Modules.get('community','calendar');
		this.title = t('Event');
		this.width = 620;
		this.stateId = "calendar-event-window";
		this.height = 840;
		this.resizable = true;
		this.hasLinks = true;
		this.store = this.form.dataSource as JmapDataSource; //jmapds("CalendarEvent");
		// this.startTime = textfield({type:'time',value: '12:00', width: 128})
		// this.endTime = textfield({type:'time',value: '13:00', width: 128})
		var recurrenceField = recurrencefield({name: 'recurrenceRule',flex:1});
		this.alertField = alertfield();

		const exceptionsBtn = btn({text:t('Exceptions'), handler: _b => {
			this.openExceptionsWindow();
		}});

		const now = new DateTime();

		this.form.on('beforesave', ({data}) => {
			this.parseSavedData(data);
		});
		this.form.on('load', ({data}) => {
			const start = new DateTime(data.start);
			data.end = start.add(new DateInterval(data.duration))
				.addDays(data.showWithoutTime? -1 : 0)
				.format(data.showWithoutTime ? 'Y-m-d' : 'Y-m-dTH:i:s');
			exceptionsBtn.hidden = !data.recurrenceOverrides;
			if(data.useDefaultAlerts) {
				this.alertField.useDefault = true;
				delete data.alerts;
			}
			//recurrenceField.setStartDate(start)
		});
		this.form.on('save', () => { this.close();});
		this.form.on("beforesubmit", this.onBeforeSubmit, {bind:this});

		this.generalTab.cls = 'flow fit scroll pad';

		this.startDate = datetimefield({
			label: t('Start'),
			withTimeZone: false,
			name: 'start',
			flex: 1,
			defaultTime: now.format('H')+':00',
			listeners:{
				change: ({target, oldValue}) => {

					const newStartDate = target.getValueAsDateTime(),
						endDate = this.endDate.getValueAsDateTime(),
						format= target.withTime ? "Y-m-dTH:i" : 'Y-m-d',
						oldStartDate = oldValue ? DateTime.createFromFormat(oldValue, format) : undefined,
						di = endDate && oldStartDate ? oldStartDate.diff(endDate) : new DateInterval(client.user.calendarPreferences.defaultDuration);

					if(newStartDate){
						recurrenceField.setStartDate(newStartDate);
					}

					if (endDate && newStartDate && newStartDate.date >= endDate.date) {
						this.endDate.value = newStartDate.clone()
							.add(di)
							.format(format);
					}
					this.participantFld.checkAvailability(this.startDate.getValueAsDateTime()!, this.endDate.getValueAsDateTime()!, !!this.withoutTimeToggle.value);
				},
			setvalue: ({target}) => {
				const d = target.getValueAsDateTime();
				if(d){
					recurrenceField.setStartDate(d);
				}
			}}
		});

		this.endDate = datetimefield({
			label: t('End'),
			withTimeZone: false,
			name: 'end',
			flex:1,
			defaultTime: (now.getHours()+1 )+':00',
			listeners: {
				change: ({target, oldValue}) => {
					const newEndDate = target.getValueAsDateTime(),
						startDate = this.startDate.getValueAsDateTime(),
						format= target.withTime ? "Y-m-dTH:i" : 'Y-m-d',
						oldEndDate = oldValue ? DateTime.createFromFormat(oldValue, format) : undefined,
						di = startDate && oldEndDate ? oldEndDate.diff(startDate) : new DateInterval("-" + (client.user.calendarPreferences.defaultDuration ?? "P0"));

					if (newEndDate && startDate && ( (target.withTime &&  newEndDate.date <= startDate.date) || (!target.withTime &&  newEndDate.date < startDate.date))) {
						this.startDate.value = newEndDate.clone()
							.add(di)
							.format(format);
					}
					if(newEndDate && this.item) {
						this.item.end = newEndDate; // for isInPast
					}
					this.participantFld.checkAvailability(this.startDate.getValueAsDateTime()!, this.endDate.getValueAsDateTime()!, !!this.withoutTimeToggle.value);
				},
				validate: ev => {

					const end = ev.target.getValueAsDateTime(), start = this.startDate.getValueAsDateTime();
					if(!end) {
						ev.target.setInvalid(t("Invalid date"));
						return;
					}
					if(!start) {
						this.startDate.setInvalid(t("Invalid date"));
						return;
					}

					if(ev.target.withTime && end.date <= start.date) {
						ev.target.setInvalid(t("The end time must be greater than the start date"));
					}
				}
			}
		});

		this.generalTab.items.add(
			this.titleField = textfield({placeholder: t('Enter a title, name or place'), name: 'title', flex: '0 1 60%', listeners: {
				'focus':()=> { this.titleField.input!.select();}
			}}),
			select({
				label: t('Calendar'), name: 'calendarId', required: true, flex: '1 30%',
				store: writeableCalendarStore,
				valueField: 'id',
				textRenderer: (r: any) => r.name,
				listeners: {
					render: () => {
						if(!writeableCalendarStore.loaded) {
							void writeableCalendarStore.load();
						}
					},
					'setvalue': ({newValue}) => {
						if(newValue) {
							const r = writeableCalendarStore.findById(newValue);
							if (!r) return;
							if(this.item)
								this.item.cal = r;

							const d = this.form.value.showWithoutTime ? r.defaultAlertsWithoutTime : r.defaultAlertsWithTime;
							this.alertField.setDefaultLabel(d)
							if (!this.item?.key && !this.participantFld.list.isEmpty()) {
								// calendar changed and event is new, check if organizer needs to change as well
								principalDS.single(this.item!.ownerId).then(p => {
									this.participantFld.addOrganiser(p);
									// this.participantFld.list.trackReset();
								}).catch(e => {
									void Window.error(t("Could not add organizer because the calendar principal could not be read from the server. Do you have permissions?"));
								})
							}
						}

					}
				}
			}),
			this.locationField = locationfield({flex:1, style:{minWidth:'80%'}}),
			btn({icon:'video_call', hidden: !m?.settings?.videoUri, cls:'filled', width:50, handler: async (btn) => {
					(btn.previousSibling() as AutocompleteField)!.value = await this.createVideoLink(m?.settings);
			}}),
			this.withoutTimeToggle = checkbox({type:'switch',value:undefined, name: 'showWithoutTime', label: t('All day'), style:{width:'auto'},
				listeners: {'setvalue':({newValue}) => {
					this.alertField.fullDay = newValue;
					this.alertField.drawOptions();

					if(this.form.value.calendarId) {
						writeableCalendarStore.dataSource.single(this.form.value.calendarId).then(r => {
							if (!r) return;
							const d = newValue ? r.defaultAlertsWithoutTime : r.defaultAlertsWithTime;
							this.alertField.setDefaultLabel(d)
						});
					}
					this.startDate.withTime = this.endDate.withTime = !newValue;
				}}
			}),
			comp({}),
			this.startDate,this.endDate,
			comp({cls:'hbox'},
				recurrenceField,
				exceptionsBtn,
			),
			this.participantFld = participantfield({
				listeners: {
					'setvalue': ({newValue}) => {
						const count = (newValue && Object.keys(newValue).length);
						this.btnFreeBusy.hidden = !count;
					},
					'change' :({newValue}) => {
						const count = (newValue && Object.keys(newValue).length);
						this.submitBtn.text = t(count && (this.endDate.getValueAsDateTime()!.date > new Date()) ? 'Send' : 'Save');

						this.participantFld.checkAvailability(this.startDate.getValueAsDateTime()!, this.endDate.getValueAsDateTime()!, !!this.withoutTimeToggle.value);
					},
					'beforeadd': ({target, principal}) => {
						if(target.list.isEmpty()) {
							principalDS.single(this.item!.ownerId).then(p=>{
								target.addOrganiser(p);
							}).catch(e => {
								void Window.error(t("Could not add organizer because the calendar principal could not be read from the server. Do you have permissions?"));
							})
						}
					}
				}
			}),
			this.btnFreeBusy = btn({hidden: true,text:t('Check availability'), handler: () => {
				const dlg = new AvailabilityWindow();
				dlg.on('changetime', ({start, end}) => {
					this.startDate.value = start.format('Y-m-dTH:i');
					this.endDate.value = end.format('Y-m-dTH:i');
					this.participantFld.checkAvailability(this.startDate.getValueAsDateTime()!, this.endDate.getValueAsDateTime()!, !!this.withoutTimeToggle.value);
				});
				dlg.show(this.item, this.form.modified);
			} }),
			this.alertField,

			textarea({
				name:'description',
				label: t('Description'),
				autoHeight: true
			}),

			autocompletechips({
				list: table({fitParent: true, headers: false, store: datasourcestore({dataSource:categoryStore.dataSource}),
					rowSelectionConfig: {multiSelect: true},
					columns: [
						checkboxselectcolumn(),
						column({id: "name"})
					]
				}),
				label: t("Categories",'core','core'),
				name: "categoryIds",
				chipRenderer: async (chip, id) => {
					categoryStore.dataSource.single(id).then(v => { chip.text = v?.name ?? '???'});
				},
				listeners: {
					autocomplete: ( {target, input}) => {
						target.list.store.queryParams = {filter: {name: input}};
						target.list.store.load();
					}
				}
			}),
			radio({type:'button',value: 'busy', name: 'freeBusyStatus', flex:'1 40%',options: [
				{value:'busy',text: t('Busy')},
				{value:'free',text: t('Free')}
			]}),
			select({name: 'privacy', flex:'1 40%', required:true, value: 'public', label: t('Visibility'), options: [
				{value:'public', name: t('Public')},
				{value:'private',name:  t('Private')},
				{value:'secret',name:  t('Secret')}
			]}),
			this.attachments = mapfield({name: 'links', cls:'goui-pit',
				buildField: (v: any) => {
					return containerfield({flex:'1 0 100%',cls: 'flow'},
						textfield({hidden:true,name:'title'}),
						numberfield({hidden:true,name:'size'}),
						textfield({hidden:true,name:'contentType'}),
						textfield({hidden:true,name:'blobId'}),
						btn({icon: "description", text: v.title, flex:'1', style:{textAlign:'left'}, handler() {
							client.downloadBlobId(v.blobId, v.title).catch((error) => {
								Notifier.error(error);
							})
						}}),
						btn({icon: "delete", width:50, handler(btn) {btn.parent!.remove();}})
					);
				}
			})
		);

		this.bbar.items.insert(2,
			btn({icon:'attach_file', handler: _ => this.attachFile() })
		);

		this.addCustomFields();
	}

	private b64UrlEncode(data:string) {
		const base64 = btoa(data);
		return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
	}

	private async createVideoLink(s: any) {
		const room = this.b64UrlEncode(String.fromCharCode(...crypto.getRandomValues(new Uint8Array(8))));

		if(!s.videoJwtEnabled) {
			return s.videoUri+room;
		}

		const response = await client.jmap("CalendarEvent/generateJWT", {room}, 'pJwt');

		return  s.videoUri+room+'?jwt='+response.jwt;
		// const id = s.videoJwtAppId,
		// 	head= this.b64UrlEncode(JSON.stringify({alg: 'HS256', typ: 'JWT'})),
		// 	load = this.b64UrlEncode(JSON.stringify({aud: id, iss: id, room})),
		// 	key = await crypto.subtle.importKey('raw', (new TextEncoder()).encode(s.videoJwtSecret),
		// 		{ name: 'HMAC', hash: 'SHA-256' }, false, ['sign']),
		// 	sign = await window.crypto.subtle.sign("HMAC", key, (new TextEncoder()).encode(`${head}.${load}`));
		// return  s.videoUri+room+'?jwt='+`${head}.${load}.${this.b64UrlEncode(String.fromCharCode(...new Uint8Array(sign)))}`;
	}

	private openExceptionsWindow() {
		const o = this.form.value.recurrenceOverrides;
		let d = [];
		for(let recurrenceId in o) {
			d.push({recurrenceId, excluded: o[recurrenceId]?.excluded})
		}
		const exceptionStore = store({data:d}),
			exceptionView = win({
				title: t('Exceptions'),
				height:400,
			}, table({
				fitParent:true,
				store: exceptionStore,
				columns: [
					column({id: "recurrenceId",htmlEncode:false, header:t('Start'), renderer(v,record) {
						return Format.dateTime(v)+ '<br><small>'+t(record.excluded ? 'Excluded' : 'Override')+'</small>';
					}}),
					column({id: "excluded", header: '', width:90, renderer: (v,r) => btn({icon:'delete',handler:()=>{
						this.item!.undoException(r.recurrenceId).then(_=> { exceptionStore.remove(r)})
					}})
					}),
				]
			}));

		exceptionView.show();
	}

	parseSavedData(data: any) {

		const end = this.endDate.getValueAsDateTime()!,// DateTime.createFromFormat(data.end, 'Y-m-dTh:i'),
			start = this.startDate.getValueAsDateTime()!;
		if(this.form.value.showWithoutTime) {
			end.setHours(0,0,0).addDays(1);
			start.setHours(0,0,0);

			if(data.start)
				data.start = start.format('Y-m-d');// remove time
		}
		if(this.alertField.isModified() || !this.item?.data.id) {
			data.useDefaultAlerts = this.alertField.useDefault;
		}
		if(this.form.isNew)
			data.timeZone = go.User.timezone; // enh: option to change in dialog?
		if(data.start || data.end)
			data.duration = start.diff(end).toIso8601();
		delete data.end;
		return data;
	}

	loadEvent(ev: CalendarItem) {

		//this.title = t(!ev.key ? 'New event' : 'Edit event');
		if(ev.data.calendarId) {
			ev.data.calendarId = ev.data.calendarId + ""; // select fields will change it to string and will trigger a modification
		}
		if (!ev.key) {
			this.item = ev;
			this.form.create(ev.data);
		} else {
			this.form.load(ev.data.id!).then(() => {
				if(ev.recurrenceId) {
					this.startDate.value = ev.start.format(ev.data.showWithoutTime ? 'Y-m-d' : 'Y-m-d\TH:i');
					this.startDate.trackReset();
					this.endDate.value = ev.end.clone().addDays(ev.data.showWithoutTime? -1 : 0).format(ev.data.showWithoutTime ? 'Y-m-d' : 'Y-m-d\TH:i');
					this.endDate.trackReset();
				}
				if(ev.override) {
					for(const k in ev.patched) {
						const f = this.form.findField(k)
						if(f) {
							f.value = ev.patched[k as keyof CalendarEvent];
							f.trackReset();
						}
					}
				}
				// set item here because 'setvalue' of end field will change the item.end value
				this.item = ev;
			});
		}
		this.titleField.focus();

	}

	private onBeforeSubmit() {
		if(this.confirmedScheduleMessage) {
			return;
		}
		if(this.form.currentId && !this.form.isModified()) {
			this.close();
			return false;
		}
		if(this.item!.isRecurring) {

			this.item!.patch(this.parseSavedData(this.form.modified), () => {
				this.close();
			});
			// cancel normal submit
			return false;
		} else {
			this.item!.confirmScheduleMessage(this.parseSavedData(this.form.modified), () => {
				this.confirmedScheduleMessage = true;

				// allow long timeout for sending invitations
				client.raiseNextRequestTimeout(180000);
				this.form.submit();
				this.confirmedScheduleMessage = false;
			});

			// cancel normal submit
			return false;
		}
	}



	private attachFile() {
		 browser.pickLocalFiles(true).then(files => {
			 this.attachments.mask();
			 client.uploadMultiple(files).then(blobs => {
				 for(const r of blobs)
					 this.attachments.add({blobId:r.id, title:r.name, size:r.size, contentType:r.type}, );
			 }).finally(() => {
				 this.attachments.unmask();
			 });
		 });
	}

}