import {
	autocomplete, btn,
	column,
	comp,
	Component, datasourcestore,
	displayfield, hr,
	MapField,
	mapfield, menu,
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

export class ParticipantField extends Component {

	list: MapField

	constructor() {
		super();
		const contextMenu = menu({removeOnClose:true},

		);
		this.items.add(
			this.list = mapfield({name: 'participants',
				buildField: (v: any) => displayfield({cls: "hbox"},
					comp({tagName:'i',cls:'icon',html:v.name?'person':'email', style:{marginRight:'8px'}}),
					comp({
						flex: '1 0 80%',
						html: v.name ? v.name + '<br>' + v.email : v.email
					}),
					btn({icon:'arrow_drop_down', menu: menu({},
						btn({text: v.email, disabled: true}),
							hr(),
						btn({text:'Optioneel',/* enableToggle: true*/}),
						btn({text:t('Delete')}),
							hr(),
						btn({text:'Invite again'}),
						btn({text:t('Write email')})
					)})
				)
			}),
			autocomplete({
				label:t('Invite people'),

				//valueProperty: "id",
				listeners: {
					'autocomplete': async (field, input) => {
						field.list.store.queryParams = {filter: {text: input}};
						await field.list.store.load();
						if(field.list.store.count() == 0) {
							field.list.hide();
						}
					},
					// 'select': (me, record) => {
					// 	debugger;
					// 	this.addParticipant(record);
					// },
					'change': (me, newValue) => {
						var r= me.list.store.get(newValue);
						debugger;
						this.addParticipant(r);
						// debugger;
						// if(newValue === undefined || newValue === null) {
						// 	if(me.input && validateEmail(me.input.value)) {
						// 		this.addParticipant({email:me.input.value});
						// 	}
						// } else if(newValue?.email) { // todo: turn into valid participant record first because this is what is posted
						// 	if(newValue.displayName) {
						// 		newValue.name = newValue.displayName;
						// 		delete newValue.displayName;
						// 		delete newValue.id;
						// 		this.addParticipant(newValue);
						// 	}
						//
						// }
						me.value = null;
					}
				},
				list:table({
					style:{minWidth:'100%'},
					headers: false,
					store: datasourcestore({ // TODO: use Search/email mother but store doesn't support this yet
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
		)
	}

	addParticipant(user: any) {

		this.list.add({
			email:user.email,
			name: user.displayName || user.email,
			roles: {attendee:true},
			kind: 'individual'
		}, user.id);
	}
}