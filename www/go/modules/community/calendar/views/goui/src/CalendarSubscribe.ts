import {Window} from "@goui/component/Window.js";
import {table} from "@goui/component/table/Table.js";
import {column} from "@goui/component/table/TableColumns.js";
import {jmapstore} from "@goui/jmap/JmapStore.js";

export class CalendarSubscribe extends Window {
	constructor() {
		super();
		this.items.add(table({
			store: jmapstore({
				entity: 'Calendar',
				properties: ['id', 'name', 'color', 'isSubscribed']
			}),
			columns: [
				column({id:'id'}),
				column({id:'name'})
			]
		}))
	}

}