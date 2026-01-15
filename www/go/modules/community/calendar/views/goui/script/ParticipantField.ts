import {
	autocomplete, avatar, btn, Button, checkbox,
	column,
	comp,
	Component, Config, ContainerField, containerfield, createComponent, datasourcestore, DateTime, FieldEventMap, Format,
	hr,
	MapField,
	mapfield, Menu, menu, ObservableListenerOpts,
	table
} from "@intermesh/goui";
import {getParticipantStatusIcon, statusIcons, t} from "./Index.js";
import {client, jmapds, validateEmail} from "@intermesh/groupoffice-core";

interface ParticipantFieldEventMap extends FieldEventMap {
	beforeadd: {principal: any}
}
export const participantfield = (config?: Config<ParticipantField>) => createComponent(new ParticipantField(), config);

export class ParticipantField extends Component<ParticipantFieldEventMap> {

	list!: MapField

	constructor() {
		super();
		this.cls = 'participant-field';
	}

	protected internalRender() {
		this.items.add(
			this.list = mapfield({name: 'participants', cls:'goui-pit',
				listeners: {
					'setvalue': (e) => {
						this.fire('setvalue', e);
					},'change': (e) => {
						this.fire('change', e);
					}
				},
				buildField: (v: any) => {
					const userIcon = v.roles?.owner ?
							'manage_accounts' : (v.kind=='resource' ?
								'meeting_room' : (v.name ?
									'person' : 'contact_mail')
							),
						statusIcon = getParticipantStatusIcon(v);

					const f = containerfield({cls:'hbox', style: {alignItems: 'center', cursor:'default'}},
						comp({tagName:'i',cls:'icon',html:userIcon, style:{margin:'0 8px'}}),
						comp({
							itemId: 'name',
							flex: '1 0 60%',
							html: v.name ? v.name.htmlEncode() + (v.email ? '<br>' + v.email :'') : v.email
						}),
						comp({tagName:'i',cls:'icon '+statusIcon[2],html:statusIcon[0],title:statusIcon[1], style:{margin:'0 8px'}}),
						btn({icon:'more_vert', menu: menu({},
								btn({text: v.email, disabled: true}),
								hr(),
								checkbox({label: t('Optional'),value:!!v.roles?.optional, listeners: {'change': ({target}) => {
									if(!target.value) {
										delete v.roles.optional; // make optional (non-required)
									} else {
										v.roles.optional = true;
									}
								} }}),
								btn({icon:'delete',text:t('Delete'), handler: _ => {
										f.remove();
										this.fire('change', {newValue: this.list.value, oldValue:null});
									}
								}),
								hr(),
								//btn({icon:'insert_invitation',text:'Invite again'}),
								btn({icon:'email',text:t('Write email'), handler: _ =>{ go.showComposer({to:v.email})}})
							)})
					);
					return f;
				}
			}),
			autocomplete({
				placeholder:t('Invite people') + ' / ' + t('Resource request'),
				//valueProperty: "id",
				listeners: {
					'autocomplete': async ({target, input}) => {
						target.list.store.setFilter('text', {text: input})
						target.list.store.queryParams.limit = 20;
						target.list.store.sort = [{property: "name"}];
						await target.list.store.load();
						if(target.list.store.count() == 0) {
							target.menu.hide();
						}
					},
					'render' : ({target}) => {
						target.el.on('keydown' , ev => {
							if(ev.key === 'Enter') {
								ev.preventDefault();
								if(validateEmail(target.input!.value)) {
									const email = target.input!.value;
									this.addParticipant({id:email,email});
									target.menu.hide();
									target.value = "";
								}
							}
						});
					},
					'select': ({target, record}) => {

						if(record)
							this.addParticipant(record);

						target.value = "";
					}
				},
				list:table({
					style:{minWidth:'100%'},
					headers: false,
					store: datasourcestore({
						dataSource: jmapds('Principal'),
						filters: {
							default: {
								preferUser: true
							}
						}
						//properties: ['id', 'displayName', 'email']
					}),
					columns: [
						column({id:'type',htmlEncode: false,width:50, renderer: (v, r) => {
								if(r.avatarId) {
									return avatar({backgroundImage: client.downloadUrl(r.avatarId)});
								}
								return '<i class="icon">' + (v=='resource' ? 'meeting_room' : 'person') + '</i>'

							} }),
						column({
							id: "name",
							htmlEncode: false,
							renderer: (v, record) => {
								let name = Format.escapeHTML(v);
								if(!isNaN(record.id)) {
									name = '<b>'+name+'</b>';
								}
								return name + `<br><small>${Format.escapeHTML(record.email || record.description)}</small>`

							}
						})
					],
					listeners: {
						render: ({target}) => {
							// register the parent element to load store on scroll down
							target.store.addScrollLoader(target.findAncestorByType(Menu)!.el);
						}
					}
				})
			})
		);

		return super.internalRender();
	}

	private pendingAvailabilityIds : {[id:string]:true} = {};
	checkAvailability(start: DateTime, end: DateTime, allDay: boolean) {
		if(allDay)
			end = end.clone().addDays(1);

		const fm = allDay ? 'Y-m-d' : 'Y-m-d H:i:s',
			rows = this.list.value;

		for (const id in rows) {
			// not already pending and is user or resource
			if(this.shouldCheckAvailability(id))
				client.jmap('Principal/getAvailability', {start:start.format(fm),end:end.format(fm),id}).then((response)=> {
					const f = this.list.items?.find(v => v.dataSet.key === id) as ContainerField;
					if(f) {
						const name = f.findChild(f => f.itemId === 'name')!;
						if(response.list.length > 0) {
							name.style.color = 'red';
							if(id.startsWith('Calendar:')) {
								f.setInvalid(t('Resource already booked'))
							}
						} else {
							name.style.color = 'inherit';
							f.clearInvalid();
						}

					}

				this.pendingAvailabilityIds = {};
			})
			this.pendingAvailabilityIds[id]=true;
		}
	}

	private shouldCheckAvailability(id:string) : boolean {
		const existingKeys = Object.keys(this.list.getOldValue());
		if(existingKeys.indexOf(id)!==-1) return false;
		if(this.pendingAvailabilityIds[id]) return false;
		return id.startsWith('Calendar:') || !isNaN(+id); // Resource or User
	}

	private organizerId?: string;
	addOrganiser(p:any) {
		if(this.organizerId) this.list.items.removeAt(0);
		this.organizerId = p.id;

		this.list.insert(0,{
			email: p.email,
			name: p.name,
			roles: {attendee:true, owner:true},
			kind: p.type,
			participationStatus:"accepted",
			expectReply:false
		},p.id);
	}

	addParticipant(principal: any) {
		this.fire('beforeadd', {principal});

		this.list.add({
			email:principal.email,
			name: principal.name || principal.email,
			roles: {attendee:true},
			kind: principal.type ?? 'individual',
			participationStatus:"needs-action",
			expectReply:true
		}, principal.id);
	}
}