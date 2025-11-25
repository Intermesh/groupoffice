import {checkbox, comp, DataSourceStore, List, Store, store, t} from "@intermesh/goui";
import {Entity, modules} from "@intermesh/groupoffice-core";

export class TypeGrid extends List {
	private typeStore: Store;
	private logEntryStore: DataSourceStore;
	private selectedTypes: String[];

	constructor(logEntryStore: DataSourceStore) {

		const typeStore = store({
			data: []
		});

		const renderer = (v: any) => {
			return [comp({}, checkbox(
				{
					itemId: v.name,
					label: t(v.name),
					listeners: {
						change: ({target, newValue}) => {
							const record = this.typeStore.find((t) => t.name === target.itemId);

							if (newValue) {
								this.selectedTypes.push(record!.name);
							} else {
								this.selectedTypes = this.selectedTypes.filter(type => type !== record!.name);
							}

							this.logEntryStore.setFilter("entities", {entities: this.selectedTypes});

							void this.logEntryStore.load();
						}
					}
				})
			)];
		}

		super(typeStore, renderer);

		this.typeStore = typeStore;
		this.logEntryStore = logEntryStore;
		this.selectedTypes = [];
	}

	public async load() {
		let entities: Entity[] = [];

		const mods = await modules.getAll();

		mods.forEach(m => {
			Object.values(m.entities).forEach(entity => {
				entities.push(entity);
			});
		});

		this.typeStore.add(...entities);
		void this.typeStore.load();
	}
}