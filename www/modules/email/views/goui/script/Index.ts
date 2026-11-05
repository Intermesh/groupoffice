import {BaseEntity, t} from "@intermesh/goui";
import {UserSettingsPanel} from "./UserSettingsPanel.js";
import {JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {EmailTemplatesSettingsPanel} from "./EmailTemplatesSettingsPanel";

modules.register({
	package: "legacy",
	name: "email",
	mainPanel: "GO.email.EmailClient",
	title: t("E-mail"),
	userSettingsPanels:[UserSettingsPanel, EmailTemplatesSettingsPanel]
});

export interface Template extends BaseEntity {
	id: string,
	name: string,
	user_id: string
}

export const templateDS = new JmapDataSource<Template>("Template");