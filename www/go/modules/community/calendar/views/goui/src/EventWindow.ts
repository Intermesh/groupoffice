import {
	autocompletechips,
	browser,
	btn, Button,
	checkbox, CheckboxField, checkboxselectcolumn,
	column,
	comp, containerfield,
	datasourcestore,
	DateField,
	datefield, DateInterval,
	DateTime,
	Format, MapField, mapfield, Notifier, numberfield,
	radio,
	select,
	store,
	table,
	textarea,
	textfield,
	TextField,
	win,
} from "@intermesh/goui";
import {client, FormWindow, JmapDataSource, jmapds, recurrencefield} from "@intermesh/groupoffice-core";
import {calendarStore, categoryStore,t} from "./Index.js";
import {ParticipantField, participantfield} from "./ParticipantField.js";
import {alertfield} from "./AlertField.js";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {AvailabilityWindow} from "./AvailabilityWindow.js";


export class EventWindow extends FormWindow {

	private item?: CalendarItem

	private store: JmapDataSource
	private submitBtn:Button
	private endDate: DateField
	private startDate: DateField
	private withoutTimeToggle: CheckboxField

	private attachments:MapField
	private btnFreeBusy: Button
	private locationField: TextField
	private participantFld: ParticipantField

	private titleField: TextField
	constructor() {
		super("CalendarEvent");

		const m = go.Modules.get('community','calendar');
		this.title = t('Event');
		this.width = 440;
		this.height = 820;
		this.store = this.form.dataSource as JmapDataSource; //jmapds("CalendarEvent");
		// this.startTime = textfield({type:'time',value: '12:00', width: 128})
		// this.endTime = textfield({type:'time',value: '13:00', width: 128})
		var recurrenceField = recurrencefield({name: 'recurrenceRule',flex:1});
		var alertField = alertfield(),
			alertUseDefault = checkbox({hidden:true, name:'useDefaultAlerts', listeners: {
				'setvalue': (_, newV) => {
					if(newV) {alertField.useDefault = true;}
				}
			}});
		alertField.on('change', (me, newValue) => {
			if(newValue === null) {
				alertUseDefault.value = me.useDefault = false;
			} else {
				const isDefault = (newValue === 'default' || Object.keys(newValue).length === 0);
				me.useDefault = isDefault;
				alertUseDefault.value = isDefault;
			}
		});

		const exceptionsBtn = btn({text:t('Exceptions'), handler: _b => {
			this.openExceptionsWindow();
		}});

		const now = new DateTime();

		this.form.on('beforesave', (frm,data) => {
			this.parseSavedData(data);
		});
		this.form.on('load', (_, data) => {
			const start = new DateTime(data.start);
			data.end = start.add(new DateInterval(data.duration))
				.addDays(data.showWithoutTime? -1 : 0)
				.format(data.showWithoutTime ? 'Y-m-d' : 'Y-m-dTH:i:s');
			exceptionsBtn.hidden = !data.recurrenceOverrides;
			//recurrenceField.setStartDate(start)
		});
		this.form.on('save', () => {this.close();});
		this.generalTab.cls = 'flow fit scroll pad';

		this.generalTab.items.add(
			this.titleField = textfield({placeholder: t('Enter a title, name or place'), name: 'title', flex: '0 1 60%', listeners: {
				'focus':()=> { this.titleField.input!.select();}
			}}),
			select({
				label: t('Calendar'), name: 'calendarId', required: true, flex: '1 30%',
				store: calendarStore,
				valueField: 'id',
				textRenderer: (r: any) => r.name,
				listeners: {
					'setvalue': (me, v) => {
						if(v)
						calendarStore.dataSource.single(v).then(r => {
							if(!r) return;
							this.item!.cal = r;
							const d = this.form.value.showWithoutTime ? r.defaultAlertsWithoutTime : r.defaultAlertsWithTime;
							alertField.setDefaultLabel(d)
							if(!this.item?.key && !this.participantFld.list.isEmpty()) {
								// calendar changed and event is new, check if organizer needs to change as well
								jmapds('Principal').single(this.item!.principalId).then(p=>{
									if(p)
										this.participantFld.addOrganiser(p);
								});
							}
						});

					}
				}
			}),
			this.locationField = textfield({name: 'location',flex:1, label:t('Location'), style:{minWidth:'80%'},
				listeners: {'setvalue': (me,v) => { me.buttons![0].hidden = !/^https?:\/\//.test(v); }},
				buttons:[btn({hidden:true,icon: 'open_in_browser', handler:(_b)=>{window.open(this.locationField.value as string)}})]
			}),
			btn({icon:'video_call', hidden: !m?.settings?.videoUri, cls:'filled', width:50, handler: async (btn) => {
					(btn.previousSibling() as TextField)!.value = await this.createVideoLink(m?.settings);
			}}),
			this.withoutTimeToggle = checkbox({type:'switch',name: 'showWithoutTime', label: t('All day'), style:{width:'auto'},
				listeners: {'setvalue':(_, checked) => {
					alertField.fullDay = checked;
					alertField.drawOptions();
					calendarStore.dataSource.single(this.form.value.calendarId).then(r => {
						if(!r) return;
						const d = checked ? r.defaultAlertsWithoutTime : r.defaultAlertsWithTime;
						alertField.setDefaultLabel(d)
					});
					this.startDate.withTime = this.endDate.withTime = !checked;
				}}
			}),
			comp({}),
			this.startDate = datefield({label: t('Start'), name:'start', flex:1, defaultTime: now.format('H')+':00',
				listeners:{'change': (me,_v, oldStartDate) => {
					const newStartDate = me.getValueAsDateTime(),
						endDate = this.endDate.getValueAsDateTime(),
						format= me.withTime ? "Y-m-dTH:i" : 'Y-m-d';
					if(newStartDate){
						recurrenceField.setStartDate(newStartDate);
					}

					if (endDate && newStartDate && newStartDate.date > endDate.date) {
						const oldStart = DateTime.createFromFormat(oldStartDate, format)!,
							duration = oldStart.diff(endDate);
						this.endDate.value = newStartDate.add(duration).format(format);
					}
				}}
			}),
			this.endDate = datefield({label:t('End'), name: 'end', flex:1, defaultTime: (now.getHours()+1 )+':00',
				listeners: {'change': (me,_v,oldEndDate) => {
					const newEndDate = me.getValueAsDateTime(),
						startDate = this.startDate.getValueAsDateTime(),
						format= me.withTime ? "Y-m-dTH:i" : 'Y-m-d';

					if (newEndDate && startDate && newEndDate.date < startDate.date) {

						const oldEnd = DateTime.createFromFormat(oldEndDate, format)!,
							duration = oldEnd.diff(startDate);
						this.startDate.value = newEndDate.add(duration).format(format);
					}
					if(newEndDate && this.item) {
						this.item.end = newEndDate; // for isInPast
					}
				}}
			}),
			comp({cls:'hbox'},
				recurrenceField,
				exceptionsBtn,
			),
			this.participantFld = participantfield({
				listeners: {
					'change': (_,v) => {
						const count = (v && Object.keys(v).length);
						this.submitBtn.text = t(count && (this.endDate.getValueAsDateTime()!.date > new Date()) ? 'Send' : 'Save');
						this.btnFreeBusy.hidden = !count;

					},
					'beforeadd': (field, v) => {
						if(field.list.isEmpty()) {
							jmapds('Principal').single(this.item!.principalId).then(p=>{
								if(p)
									field.addOrganiser(p);
							});
						}
					}
				}
			}),
			this.btnFreeBusy = btn({hidden: true,text:t('Check availability'), handler: () => {
				const dlg = new AvailabilityWindow();
				dlg.on('changetime', (_,s,e) => {
					this.startDate.value = s.format('Y-m-dTH:i');
					this.endDate.value = e.format('Y-m-dTH:i');
				});
				dlg.show(this.item, this.form.modified);
			} }),
			alertField,alertUseDefault,
			textarea({name:'description', label: t('Description'), autoHeight: true}),
			autocompletechips({
				list: table({fitParent: true, headers: false, store: datasourcestore({dataSource:categoryStore.dataSource}),
					rowSelectionConfig: {multiSelect: true},
					columns: [
						checkboxselectcolumn(),
						column({id: "name"})
					]
				}),
				label: "Categories",
				name: "categoryIds",
				chipRenderer: async (chip, id) => {
					categoryStore.dataSource.single(id).then(v => { chip.text = v?.name ?? '???'});
				},
				listeners: {
					autocomplete: (field, input) => {
						field.list.store.queryParams = {filter: {name: input}};
						field.list.store.load();
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

		this.bbar.items.clear().add(
			btn({icon:'attach_file', handler: _ => this.attachFile() }),
			comp({flex:1}),
			this.submitBtn = btn({text:t('Save'), cls:'primary filled',handler: _ => this.submit()})
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
		const id = s.videoJwtAppId,
			head= this.b64UrlEncode(JSON.stringify({alg: 'HS256', typ: 'JWT'})),
			load = this.b64UrlEncode(JSON.stringify({aud: id, iss: id, room})),
			key = await crypto.subtle.importKey('raw', (new TextEncoder()).encode(s.videoJwtSecret),
				{ name: 'HMAC', hash: 'SHA-256' }, false, ['sign']),
			sign = await window.crypto.subtle.sign("HMAC", key, (new TextEncoder()).encode(`${head}.${load}`));
		return  s.videoUri+room+'?jwt='+`${head}.${load}.${this.b64UrlEncode(String.fromCharCode(...new Uint8Array(sign)))}`;
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
					column({id: "recurrenceId", header:t('Start'), renderer(v,record) {
						return Format.dateTime(v)+ `<br><small>${record.excluded ? 'Excluded' : 'Override'}</small>`;
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
		if(this.form.value.showWithoutTime && data.start) {
			end.setHours(0,0,0).addDays(1);
			start.setHours(0,0,0);
			data.start = start.format('Y-m-d');// remove time
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

	submit() {
		if(this.item!.isRecurring) {

			this.item!.patch(this.parseSavedData(this.form.modified), () => {
				this.close();
			});
		} else {
			this.item!.confirmScheduleMessage(this.parseSavedData(this.form.modified), () => {
				this.form.submit();
			});
		}
	}

	private async attachFile() {
		 const files = await browser.pickLocalFiles(true);
		 this.mask();
		 const blobs = await client.uploadMultiple(files);
		 this.unmask();
		 for(const r of blobs)
		 	this.attachments.add({blobId:r.id, title:r.name, size:r.size, contentType:r.type}, );
		 //console.warn(blobs);
	}

}