import {Component, list, List, store, Store} from "@intermesh/goui";
import {EmailView} from "./EmailView";
import {jmapds} from "@intermesh/groupoffice-core";

export class ThreadView extends Component {

	mailList : List

	constructor() {
		super();
		this.cls = 'vbox';
		this.flex = '1';

		this.items.add(this.mailList = list({
			store: store({}),
			renderer: item => {
				const mailView = new EmailView();
				mailView.value = item;
				return [mailView];
			}
		}));
	}

	load(thread: any) {
		this.mailList.store.clear();
		if(!thread.emailIds.length) debugger;
		jmapds('Email').get(thread.emailIds).then(r => {

			this.mailList.store.loadData(r.list);
		});
	}
}