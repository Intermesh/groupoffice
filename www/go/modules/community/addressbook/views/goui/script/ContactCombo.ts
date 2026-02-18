import {
	AutocompleteField,
	Config,
	createComponent,
	listStoreType,
	storeRecordType,
	t
} from "@intermesh/goui";
import {ContactTable} from "./ContactTable";
import {contactDS} from "@intermesh/community/addressbook"
export class ContactCombo extends AutocompleteField<ContactTable> {
	public isOrganization: boolean | undefined;

	pickerRecordToValue(field: this, record: storeRecordType<listStoreType<ContactTable>>) {
		return record.id;
	}

	async valueToTextField(field: this, value: string): Promise<string> {
		const nb = await contactDS.single(value);
		return nb ? nb.name! : "?";
	}

	constructor(isOrganization: boolean | null | undefined) {
		super(new ContactTable());

		if (!this.label) {
			this.label = isOrganization ? t("Organization", "community", "addressbook"): t("Contact", "community", "addressbook");
		}
		this.name = this.name || "coantactId";

		if(typeof isOrganization === "boolean") {
			this.list.store.setFilter("isOrganization", {isOrganization: isOrganization});
		}

		this.on("autocomplete", async ( {input}) => {
			this.list.store.setFilter("search", {text: input});
			await this.list.store.load();
		});

	}
}
export const contactcombo = (config?: Config<ContactCombo>) => createComponent(new ContactCombo(config?.isOrganization), config);