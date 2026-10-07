import {checkbox, column, comp, Component, store, Store, t, table, Table} from "@intermesh/goui";
import {client} from "@intermesh/groupoffice-core";

export interface AllowedModule {
	id: string,
	package: string | null,
	module: string,
	title: string,
	allowed: boolean,
	localizedPackage: string
}

export class AllowedModulesPanel extends Component {
	public readonly table: Table<Store<AllowedModule>>;

	constructor() {
		super();

		this.title = t("Modules");
		this.cls = "fit";

		this.table = table<Store<AllowedModule>>({
			fit: true,
			cls: "bg-lowest",
			headers: false,
			groupBy: "localizedPackage",
			store: store<AllowedModule>(),
			columns: [
				column({
					id: "allowed",
					width: 60,
					renderer: (v, record: AllowedModule) => {
						return checkbox({
							value: !!v,
							listeners: {
								change: ({newValue}) => {
									record.allowed = newValue;
								}
							}
						});
					}
				}),
				column({
					id: "title",
					renderer: (v, record: AllowedModule) => {
						const url = client.downloadUrl("core/moduleIcon/" + (record.package || "legacy") + "/" + record.module) + "&mtime=" + go.User.session.cacheClearedAt;

						return comp({
							html: `<div class="mo-title" style="background-image:url(${url})">${record.title}</div>`
						});
					}
				})
			]
		});

		this.table.enableCheckboxColumnListeners();

		this.items.add(
			comp({cls: "scroll fit"},
				this.table
			)
		);
	}

	public setModules(modules: AllowedModule[]) {
		const sorted = [...modules].sort((a, b) => a.localizedPackage.localeCompare(b.localizedPackage) || a.title.localeCompare(b.title));
		this.table.store.loadData(sorted, false);
	}

	public getAllowed() {
		return this.table.store.data.filter(m => m.allowed).map(m => m.package + "/" + m.module);
	}
}