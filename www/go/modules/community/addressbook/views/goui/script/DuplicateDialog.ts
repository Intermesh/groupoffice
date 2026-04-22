import {btn, checkbox, comp, Component, t, tbar, Window} from "@intermesh/goui";
import {contactgrid} from "./ContactGrid.js";
import {AclLevel} from "@intermesh/groupoffice-core";
import {contactDS} from "./Index.js";

export class DuplicateDialog extends Window {
	private checkboxContainer: Component;
	private grid: ReturnType<typeof contactgrid>;

	constructor() {
		super();

		this.title = t("Filter duplicates");
		this.modal = true;
		this.width = 1000;
		this.height = 800;

		this.items.add(
			comp({cls: "scroll", flex: 1},
				comp({cls: "vbox fit"},
					this.checkboxContainer = comp({cls: "hbox gap", style: {padding: "16px"}},
						checkbox({
							itemId: "name",
							label: t("Name"),
							value: true,
							listeners: {change: () => this.applyFilter()}
						}),
						checkbox({
							itemId: "isOrganization",
							label: t("Organisation or contact"),
							value: true,
							listeners: {change: () => this.applyFilter()}
						}),
						checkbox({
							itemId: "emailAddresses",
							label: t("E-mail addresses"),
							listeners: {change: () => this.applyFilter()}
						}),
						checkbox({
							itemId: "phoneNumbers",
							label: t("Phone numbers"),
							listeners: {change: () => this.applyFilter()}
						}),
						checkbox({
							itemId: "addressBookId",
							label: t("Address book"),
							listeners: {change: () => this.applyFilter()}
						})
					),
					this.grid = contactgrid({
						flex: 1,
						stateId: "contact-duplicate",
						rowSelectionConfig: {
							multiSelect: true
						}
					})
				)
			),
			tbar({cls: "border-top"},
				"->",
				btn({
					cls: "filled primary",
					text: t("Merge selected"),
					handler: async () => {
						const confirm = await Window.confirm(t("Are you sure you want to merge the selected items? This can't be undone."), t("Confirm"));

						if (!confirm) {
							return
						}

						const ids = this.grid.rowSelection!.getSelected().map((row) => row.record.id);

						void contactDS.merge(ids);
					}
				})
			)
		);

		void this.grid.store.load();
	}

	private applyFilter() {
		const filters: Record<string, any> = {
			permissionLevel: AclLevel.DELETE
		};

		const duplicates: string[] = [];

		(this.checkboxContainer.findChild("name") as any)?.value && duplicates.push("name");
		(this.checkboxContainer.findChild("isOrganization") as any)?.value && duplicates.push("isOrganization");
		(this.checkboxContainer.findChild("emailAddresses") as any)?.value && duplicates.push("emailAddresses");
		(this.checkboxContainer.findChild("phoneNumbers") as any)?.value && duplicates.push("phoneNumbers");
		(this.checkboxContainer.findChild("addressBookId") as any)?.value && duplicates.push("addressBookId");

		if (duplicates.length) {
			filters.duplicate = duplicates;
		}

		this.grid.store.setFilter("filter", filters);
		void this.grid.store.load();
	}
}