import {comp, Component, containerfield, datasourceform, fieldset, select, t, textfield} from "@intermesh/goui";
import {JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {DEFAULT_CDN_URL} from "./Loader.js";

/**
 * System settings: license, library source and the Docker service URLs
 */
export class SystemSettings extends Component {
	private readonly form;

	constructor() {
		super();

		const cdnUrl = textfield({
				name: "cdnUrl",
				label: t("CDN URL"),
				placeholder: DEFAULT_CDN_URL
			}),
			localInfo = comp({
				cls: "pad",
				hidden: true,
				text: t("Place ej2.min.js and ej2.min.css in go/modules/community/syncfusion/lib/")
			});

		this.form = datasourceform({
				dataSource: new JmapDataSource("Module")
			},
			containerfield({name: "settings"},
				fieldset({legend: t("License")},
					textfield({
						name: "licenseKey",
						label: t("License Key")
					})
				),
				fieldset({legend: t("Library")},
					select({
						name: "librarySource",
						label: t("Library Source"),
						value: "cdn",
						options: [
							{value: "cdn", name: t("CDN (recommended)")},
							{value: "local", name: t("Local (lib/ directory)")}
						],
						listeners: {
							setvalue: ({newValue}) => {
								cdnUrl.hidden = newValue === "local";
								localInfo.hidden = newValue !== "local";
							}
						}
					}),
					cdnUrl,
					localInfo
				),
				fieldset({legend: t("Service URLs")},
					textfield({
						name: "documentServiceUrl",
						label: t("Document Service URL"),
						placeholder: "http://localhost:6002/api/documenteditor"
					}),
					textfield({
						name: "spreadsheetServiceUrl",
						label: t("Spreadsheet Service URL"),
						placeholder: "http://localhost:6003/api/spreadsheet"
					}),
					textfield({
						name: "serviceSecret",
						type: "password",
						autocomplete: "new-password",
						label: t("Service Secret"),
						placeholder: t("Shared JWT secret for Docker services")
					})
				)
			),
			fieldset({legend: t("Setup Instructions")},
				comp({
					cls: "pad",
					html: "<p><b>" + t("Docker services") + ":</b></p>" +
						"<code>docker run -d -p 6002:80 syncfusion/word-processor-server</code><br>" +
						"<code>docker run -d -p 6003:8080 syncfusion/spreadsheet-server</code>"
				})
			)
		);

		this.items.add(this.form);

		const mod = modules.get("community", "syncfusion");
		if (mod) {
			void this.form.load(mod.id);
		}
	}

	onSubmit() {
		return this.form.submit();
	}
}
