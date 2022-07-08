import {Form, form} from "../../../../../../../views/Extjs3/goui/script/component/form/Form.js";
import {textfield} from "../../../../../../../views/Extjs3/goui/script/component/form/TextField.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {Window} from "../../../../../../../views/Extjs3/goui/script/component/Window.js";
import {htmlfield} from "../../../../../../../views/Extjs3/goui/script/component/form/HtmlField.js";
import {EntityStore} from "../../../../../../../views/Extjs3/goui/script/api/EntityStore.js";
import {client} from "../../../../../../../views/Extjs3/goui/script/api/Client.js";
import {tbar} from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import {btn} from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import {fieldset} from "../../../../../../../views/Extjs3/goui/script/component/form/Fieldset.js";

export class NoteDialog extends Window {
	private form: Form;
	private entityStore: EntityStore;
	private currentId?:number;
	constructor() {
		super();

		this.entityStore = new EntityStore("Note", client);

		this.cls = "hbox";
		this.title = t("Note");

		this.items.add(
			this.form = form(
				{
					cls: "scroll",
					flex: 1,
					handler: (form) => {
						this.entityStore.save(form.value);
					}
				},
				fieldset({},
					textfield({name: "name", label: t("Name"), required: true}),
					htmlfield({name: "content"})
				)
			),
			tbar({},
				"->",
				btn({
					type:"submit",
					text: t("Save")
				})
			)
		)
	}

	public async load(id:number) {
		this.currentId = id;

		const entity = await this.entityStore.single(id);

		this.form.value = entity;

		return this;
	}
}