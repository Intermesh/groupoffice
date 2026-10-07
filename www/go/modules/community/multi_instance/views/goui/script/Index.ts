import { t } from "@intermesh/goui";
import {JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";

modules.register({
	package: "community",
	name: "multi_instance",
	panels: {
		multi_instance: {
			title: t("Multi instance"),
			cmp: Main
		}
	},
	entities: [
		"instance"
	]
});

export const instanceDS = new JmapDataSource("Instance");