import {comp, Component} from "@goui/component/Component.js";
import {t} from "@goui/Translate.js";
import {MapField, mapfield} from "@goui/component/form/MapField.js";
import {btn} from "@goui/component/Button.js";
import {autocomplete} from "@goui/component/form/AutocompleteField.js";
import {table} from "@goui/component/table/Table.js";
import {column} from "@goui/component/table/TableColumns.js";
import {JmapStore, jmapstore} from "@goui/jmap/JmapStore.js";
import {validateEmail} from "@go-core/Validators.js";
import {displayfield} from "@goui/component/form/DisplayField.js";

interface Participant {
	email:string
	name?:string
	picture?:string
}

export class ParticipantField extends Component {

	list: MapField

	constructor() {
		super();
		this.items.add(
			this.list = mapfield({name: 'participants',
				buildField: v => displayfield({cls: "hbox"},
					comp({tagName:'i',cls:'icon',html:v.name?'person':'email', style:{marginRight:'8px'}}),
					comp({
						flex: '1 0 80%',
						html: v.name ? v.name + '<br>' + v.email : v.email
					})
				)
			}),
			autocomplete({
				label:t('Invite people'),
				name: "autocomplete",
				//valueProperty: "id",
				listeners: {
					autocomplete: async (field, input) => {
						(field.table.store as JmapStore).queryParams = {filter: {text: input}};
						await field.table.store.load();
						if(field.table.store.count() == 0) {
							field.table.hide();
						}
					},
					change: (me, newValue) => {
						if(newValue === undefined || newValue === null) {
							if(validateEmail(me.input.value)) {
								this.addParticipant({email:me.input.value});
							}
						} else if(newValue?.email) { // todo: turn into valid participant record first because this is what is posted
							if(newValue.displayName) {
								newValue.name = newValue.displayName;
								delete newValue.displayName;
								delete newValue.id;
								this.addParticipant(newValue);
							}

						}
						me.value = null;
					}
				},
				table:table({
					headers: false,
					store: jmapstore({ // TODO: use Search/email mother but store doesn't support this yet
						entity: "UserDisplay",
						properties: ['id', 'displayName', 'email']
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

	addParticipant(participant: Participant) {
		this.list.add(participant);
	}
}