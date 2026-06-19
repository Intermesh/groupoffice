import {client, main, modules, moduleSettings, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {UserSettingsPanel} from "./UserSettingsPanel.js";
import {t} from "@intermesh/goui";

modules.register({
	package: "community",
	name: "tasks",
	panels: {
		tasks: {
			cmp: Main,
			title: t("Tasks"),
			routes: {
				"^tasks\/(\d+)$"(taskId) {
					this.showTask(taskId);
				}
			}
		}
	},
	settingsPanels: [UserSettingsPanel]
});