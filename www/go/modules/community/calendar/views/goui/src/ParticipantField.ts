import {
	autocomplete, btn, checkbox,
	column,
	comp,
	Component, Config, containerfield, createComponent, datasourcestore, FieldEventMap,
	hr,
	MapField,
	mapfield, menu, ObservableListenerOpts,
	t,
	table
} from "@intermesh/goui";
import {jmapds, validateEmail} from "@intermesh/groupoffice-core";

interface Participant {
	email:string
	name?:string
	picture?:string
	roles:string[]
	kind:string
}

export const participantfield = (config?: Config<ParticipantField, FieldEventMap<ParticipantField>>) => createComponent(new ParticipantField(), config);

export interface ParticipantField extends Component {
	on<K extends keyof FieldEventMap<this>, L extends Function>(eventName: K, listener: Partial<FieldEventMap<this>>[K], options?: ObservableListenerOpts): L;
	un<K extends keyof FieldEventMap<this>>(eventName: K, listener: Partial<FieldEventMap<this>>[K]): boolean;
	fire<K extends keyof FieldEventMap<this>>(eventName: K, ...args: Parameters<FieldEventMap<any>[K]>): boolean;
}
export class ParticipantField extends Component {

	private static statusIcons : {[status:string]: string[]} = {
		'accepted':		['check_circle', t('Accepted')],
		'tentative':	['help', t('Maybe')],
		'declined':		['block', t('Declined')],
		'needs-action':['schedule', t('Awaiting reply')]
	}

	list!: MapField

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
					const userIcon = v.name?'person':'email',
						statusIcon = ParticipantField.statusIcons[v.participationStatus] || v.participationStatus;
					const f = containerfield({cls:'hbox', style: {alignItems: 'center', cursor:'default'}},
						comp({tagName:'i',cls:'icon',html:userIcon, style:{margin:'0 8px'}}),
						comp({
							flex: '1 0 60%',
							html: v.name ? v.name + '<br>' + v.email : v.email
						}),
						comp({tagName:'i',cls:'icon',html:statusIcon[0],title:statusIcon[1], style:{margin:'0 8px'}}),
						btn({icon:'more_vert', menu: menu({},
								btn({text: v.email, disabled: true}),
								hr(),
								checkbox({label:'Optioneel',/* enableToggle: true*/}),
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
				placeholder:t('Invite people'),
				//valueProperty: "id",
				listeners: {
					'autocomplete': async (field, input) => {
						field.list.store.queryParams = {filter: {text: input}};
						await field.list.store.load();
						if(field.list.store.count() == 0) {
							field.list.hide();
						}
					},
					'render' : (me) => {
						me.el.on('keydown' , ev => {
							if(ev.key === 'Enter') {
								if(validateEmail(me.input!.value)) {
									this.addParticipant({email:me.input!.value});
									me.menu.hide();
									me.value = null;
								}
							}
						});
					},
					'select': (me, record) => {

						if(record)
							this.addParticipant(record);

						me.value=null;
					}
				},
				list:table({
					style:{minWidth:'100%'},
					headers: false,
					store: datasourcestore({ // TODO: use Search/email but store doesn't support this yet
						dataSource: jmapds('UserDisplay')
						//properties: ['id', 'displayName', 'email']
					}),
					columns: [
						column({
							id: "displayName",
							renderer: (columnValue, record) => columnValue + `<br><small>${record.email}</small>`
						})
					]
				})
				//	listeners: {'blur' : (_,v) => {/*check if valid email, if so add to _.previous.add(participant);*/}}
			}),
		);

		return super.internalRender();
	}

	addSelfAsOrganiser() {
		this.list.add({
			email: go.User.email,
			name: go.User.displayName,
			roles: {attendee:true, owner:true},
			kind: 'individual',
			participationStatus:"accepted",
			expectReply:false
		}, go.User.id);
	}

	addParticipant(user: any) {
		if(this.list.isEmpty()) {
			this.addSelfAsOrganiser();
		}
		this.list.add({
			email:user.email,
			name: user.displayName || user.email,
			roles: {attendee:true},
			scheduleAgent: user.id ? 'server' : 'server',
			kind: 'individual',
			participationStatus:"needs-action",
			expectReply:true
		}, user.id || Math.round(Math.random()*1000)+1000);
	}
}