import {
	checkbox,
	comp,
	Component,
	daterangefield,
	h3,
	hr,
	List,
	list,
	fieldset,
	searchbtn,
	splitter,
	store,
	t,
	tbar,
	DateRangeField
} from "@intermesh/goui";
import {principalcombo} from "@intermesh/groupoffice-core";
import {LogEntryGrid} from "./LogEntryGrid.js";
import {TypeGrid} from "./TypeGrid.js";

export class Main extends Component {
	private west: Component;
	private center: Component;

	private logEntryGrid!: LogEntryGrid;
	private typeGrid!: TypeGrid;

	private selectedActions!: String[];
	private dateRangeField!: DateRangeField;

	constructor() {
		super();

		this.cls = "hbox fit";

		this.logEntryGrid = new LogEntryGrid();

		this.items.add(
			this.west = this.createWest(),
			splitter({
				resizeComponent: this.west
			}),
			this.center = this.createCenter()
		)

		this.on("render", () => {
			void this.typeGrid.load();
			this.dateRangeField.setThisWeek();
		})
	}

	private createWest() {
		this.typeGrid = new TypeGrid(this.logEntryGrid.store);

		this.selectedActions = [];

		return comp({
				itemId: "west",
				cls: "scroll ",
				width: 340
			},
			fieldset({},

				this.dateRangeField = daterangefield({
					label: t("Date"),
					listeners: {
						setvalue: ({newValue}) => {
							void this.logEntryGrid.store.setFilter("createdAt", {createdAt: newValue}).load();
						}
					}
				}),
				principalcombo({
					entity: "user",
					label: t("Users"),
					placeholder: t("All users"),
					required: false,
					clearable: true,
					listeners: {
						select: ({target, record}) => {
							this.logEntryGrid.store.setFilter("user", {createdBy: target.value});
							void this.logEntryGrid.store.load();
						},
						change: ({newValue}) => {
							if (!newValue) {
								this.logEntryGrid.store.clearFilter("user");

								void this.logEntryGrid.store.load();
							}
						}
					}
				})
			),

			tbar({},
				h3({
					text: t("Actions")
				})
			),
			list({
				store: store({
					data: [
						{id: "create", label: t("Create")},
						{id: "update", label: t("Update")},
						{id: "delete", label: t("Delete")},
						{id: "login", label: t("Login")},
						{id: "logout", label: t("Logout")},
						{id: "badlogin", label: t("Bad login")},
						{id: "download", label: t("Download")},
						{id: "email", label: t("E-mail")},
					]
				}),
				renderer: (v, el, list: List) => {
					return [checkbox({
							label: v.label,
							listeners: {
								change: ({target, newValue}) => {
									const record = list.store.find((i) => i.label == target.label);

									if (newValue) {
										this.selectedActions.push(record!.id);
									} else {
										this.selectedActions = this.selectedActions.filter(action => action !== record!.id);
									}

									this.logEntryGrid.store.setFilter("actions", {actions: this.selectedActions});

									void this.logEntryGrid.store.load();
								}
							}
						})]
				},
				rowSelectionConfig: {
					multiSelect: true
				}
			}),

			tbar({} ,
				h3({
					text: t("Types")
				})
			),
			this.typeGrid
		)
	}

	private createCenter() {
		return comp({
				itemId: "center",
				cls: "vbox",
				flex: 1
			},
			tbar({
					cls: "border-bottom"
				},
				"->",
				searchbtn({
					listeners: {
						input: ({text}) => {
							this.logEntryGrid.store.setFilter("search", {text});
							void this.logEntryGrid.store.load();
						}
					}
				})
			),
			comp({
					cls: "scroll bg-lowest",
					flex: 1
				},
				this.logEntryGrid
			)
		)
	}
}