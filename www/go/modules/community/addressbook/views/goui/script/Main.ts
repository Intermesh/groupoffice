import {MainThreeColumnPanel} from "@intermesh/groupoffice-core";
import {comp, Component} from "@intermesh/goui";

export class Main extends MainThreeColumnPanel {
	constructor() {
		super("addressbook");
	}

	protected createWest(): Component {
		return comp({});
	}

	protected createCenter(): Component {
		return comp({});
	}

	protected createEast(): Component {
		return comp({});
	}
}