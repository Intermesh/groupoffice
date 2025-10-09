import {AutocompleteField, btn, Button, column, Config, createComponent, List, store, table} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class LocationField extends AutocompleteField {
	private openInBrowserBtn: Button;
	constructor() {

		super(table({
			headers: false,
			columns: [
				column({
					htmlEncode: false,
					id: "address",
					renderer: (columnValue, record) => {
						return `<strong>${record.name.htmlEncode()}</strong><div style="white-space: pre">${record.address.htmlEncode()}</div>`
					}
				})
			],
			store: store({
				async onLoad(store) {
					const ds = jmapds("Contact");

					const query = await ds.query({
						filter: {text: store.queryParams.text},
						limit: 50
					})

					const get = await ds.get(query.ids)
					get.list.forEach(c => {
						c.addresses.forEach((a:any) => {
							if(!a.formatted) {
								return;
							}
							store.add({name: c.name, address: a.formatted.replace("\n", ", ")});
						})
					})
				}
			}),
		}));

		this.baseCls = "goui-form-field textarea autocomplete";

		this.on("autocomplete", ev => {
			this.list.store.queryParams.text = ev.input;
			void this.list.store.load();
		})

		this.on("setvalue", ({newValue}) => {
			this.openInBrowserBtn.hidden = !/^https?:\/\//.test(newValue);
		});

		this.buttons = [
			this.openInBrowserBtn = btn({
				hidden:true,
				icon: 'open_in_browser',
				handler:(_b)=>{
					window.open(this.value as string)
				}
			})
		];

	}

	protected createInput() : HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement{
		const control = document.createElement("textarea");

		// select the text so users can type right away
		control.addEventListener("focus", function() {
			this.select();
		})

		control.on("change", ()=> {
			this.fireChange();
		});

		control.rows = 1;
		control.style.overflowY = 'hidden';
		control.on('input',(ev) => {
			this.resize(control);
		});
		this.on("render", ()=>{this.resize(control);});
		this.on("show", ()=>{this.resize(control);});
		this.on('setvalue', ()=>{this.resize(control);});


		if (this.invalidMsg) {
			this.applyInvalidMsg();
		}
		return control;
	}

	public pickerRecordToValue (field: this, record:any) : any {
		return record.address;
	}




	private resize(input: HTMLTextAreaElement) {
		input.style.height = "0";
		input.style.height = (input.scrollHeight) + "px";
	}
}


export const locationfield = <T extends List> (config: Config<LocationField>) => createComponent(new LocationField(), config);