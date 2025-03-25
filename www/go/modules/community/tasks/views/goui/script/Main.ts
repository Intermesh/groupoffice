import {MainThreeColumnPanel} from "@intermesh/groupoffice-core";
import {comp} from "@intermesh/goui";

export class Main extends MainThreeColumnPanel {
	constructor() {
		super("tasks");
	}

	protected createEast() {
		return comp();
	}

	protected createCenter() {
		return comp();
	}

	protected createWest() {
		return comp();
	}
}