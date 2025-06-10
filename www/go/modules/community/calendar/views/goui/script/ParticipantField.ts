import {
	autocomplete, avatar, btn, Button, checkbox,
	column,
	comp,
	Component, Config, containerfield, createComponent, datasourcestore, FieldEventMap, Format,
	hr,
	MapField,
	mapfield, Menu, menu, ObservableListenerOpts,
	table
} from "@intermesh/goui";
import {statusIcons, t} from "./Index.js";
import {client, jmapds, validateEmail} from "@intermesh/groupoffice-core";

interface ParticipantFieldEventMap<Type> extends FieldEventMap<Type> {
	beforeadd: (me:Type, principal: any) => void
}
export const participantfield = (config?: Config<ParticipantField, ParticipantFieldEventMap<ParticipantField>>) => createComponent(new ParticipantField(), config);

export interface ParticipantField extends Component {
	on<K extends keyof ParticipantFieldEventMap<this>, L extends Function>(eventName: K, listener: Partial<ParticipantFieldEventMap<this>>[K], options?: ObservableListenerOpts): L;
	un<K extends keyof ParticipantFieldEventMap<this>>(eventName: K, listener: Partial<ParticipantFieldEventMap<this>>[K]): boolean;
	fire<K extends keyof ParticipantFieldEventMap<this>>(eventName: K, ...args: Parameters<ParticipantFieldEventMap<any>[K]>): boolean;
}
export class ParticipantField extends Component {

	list!: MapField
	btnFreeBusy!: Button

	constructor() {
		super();
		this.cls = 'participant-field';
	}

	protected internalRender() {
		this.items.add(
			this.list = mapfield({name: 'participants', cls:'goui-pit',
				listeners: {
					'change': (me,v) => {
						this.fire('change', this, v, null);
					}
				},
				buildField: (v: any) => {
					const userIcon = v.roles?.owner ?
							'manage_accounts' : (v.kind=='resource' ?
								'meeting_room' : (v.name ?
									'person' : 'contact_mail')
							) ,
						statusIcon = statusIcons[v.participationStatus] || v.participationStatus;
					const f = containerfield({cls:'hbox', style: {alignItems: 'center', cursor:'default'}},
						comp({tagName:'i',cls:'icon',html:userIcon, style:{margin:'0 8px'}}),
						comp({
							flex: '1 0 60%',
							html: v.name ? v.name + (v.email ? '<br>' + v.email :'') : v.email
						}),
						comp({tagName:'i',cls:'icon '+statusIcon[2],html:statusIcon[0],title:statusIcon[1], style:{margin:'0 8px'}}),
						btn({icon:'more_vert', menu: menu({},
								btn({text: v.email, disabled: true}),
								hr(),
								checkbox({label:'Optioneel',listeners: {'change': (cb) => {
									if(cb.value) {
										delete v.roles.attendee; // make optional (non-required)
									} else {
										v.roles.attendee = true;
									}
								} }}),
								btn({icon:'delete',text:t('Delete'), handler: _ => {
										f.remove();
										this.fire('change', this, this.list.value, null);
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
					'autocomplete': async (field, input) => {
						field.list.store.setFilter('text', {text: input})
						field.list.store.queryParams.limit = 20;
						field.list.store.sort = [{property: "name"}];
						await field.list.store.load();
						if(field.list.store.count() == 0) {
							field.menu.hide();
						}
					},
					'render' : (me) => {
						me.el.on('keydown' , ev => {
							if(ev.key === 'Enter') {
								ev.preventDefault();
								if(validateEmail(me.input!.value)) {
									const email = me.input!.value;
									this.addParticipant({id:email,email});
									me.menu.hide();
									me.value = "";
								}
							}
						});
					},
					'select': (me, record) => {

						if(record)
							this.addParticipant(record);

						me.value = "";
					}
				},
				list:table({
					style:{minWidth:'100%'},
					headers: false,
					store: datasourcestore({
						dataSource: jmapds('Principal')
						//properties: ['id', 'displayName', 'email']
					}),
					columns: [
						column({id:'type',width:50, renderer: (v, r) => {
								if(r.avatarId) {
									return avatar({backgroundImage: client.downloadUrl(r.avatarId)});
								}
								return '<i class="icon">' + (v=='resource' ? 'meeting_room' : 'person') + '</i>'

							} }),
						column({
							id: "name",
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
						render: table => {
							// register the parent element to load store on scroll down
							table.store.addScrollLoader(table.findAncestorByType(Menu)!.el);
						}
					}
				})
			})
		);

		return super.internalRender();
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
		this.fire('beforeadd', this, principal);

		this.list.add({
			email:principal.email,
			name: principal.name || principal.email,
			roles: {attendee:true},
			scheduleAgent: principal.id ? 'server' : 'server',
			kind: principal.type ?? 'individual',
			participationStatus:"needs-action",
			expectReply:true
		}, principal.id);
	}
}