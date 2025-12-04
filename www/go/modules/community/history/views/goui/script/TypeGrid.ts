import {ArrayUtil, checkbox, comp, DataSourceStore, List, Store, store, t} from "@intermesh/goui";
import {entities, Entity, modules} from "@intermesh/groupoffice-core";

export class TypeGrid extends List {
	private typeStore: Store;
	private logEntryStore: DataSourceStore;
	private selectedTypes: String[];

	constructor(logEntryStore: DataSourceStore) {

		const typeStore = store({
			data: []
		});

		const renderer = (v: any) => {
			console.log(v);
			return [comp({}, checkbox(
				{
					itemId: v.name,
					label: v.title,
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
		let entities1: Entity[] = [];

		const e = await entities.getAll();

		ArrayUtil.multiSort(e, [{property: "title"}]);

		this.typeStore.add(...e);
		void this.typeStore.load();
	}
}