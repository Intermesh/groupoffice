import {
	btn,
	comp,
	fieldset,
	Form,
	form,
	h3,
	Notifier,
	numberfield,
	QueryParams,
	select,
	t,
	tbar,
	textarea,
	Window
} from "@intermesh/goui";
import {contactDS} from "./Index.js";
import {client} from "@intermesh/groupoffice-core";

export class LabelsDialog extends Window {
	private form: Form;

	constructor(private queryParams: QueryParams) {
		super();

		this.title = t("Labels");
		this.width = 800;
		this.height = 700;

		this.resizable = true;
		this.maximizable = true;

		this.items.add(
			comp({cls: "scroll", flex: 1},
				this.form = form({cls: "hbox gap"},
					fieldset({cls: "vbox gap", flex: 1},
						h3({text: t("Page")}),
						select({
							name: "pageFormat",
							label: t("Page format"),
							options: [
								{value: "A4", name: "A4"},
								{value: "Letter", name: "Letter"}
							],
							value: "A4"
						}),
						numberfield({
							name: "rows",
							label: t("Rows"),
							value: 8,
							decimals: 0
						}),
						numberfield({
							name: "columns",
							label: t("Columns"),
							value: 2,
							decimals: 0
						}),
						h3({text: t("Page margins")}),
						numberfield({name: "pageTopMargin", label: t("Top"), value: 10, decimals: 0}),
						numberfield({name: "pageRightMargin", label: t("Right"), value: 10, decimals: 0}),
						numberfield({name: "pageBottomMargin", label: t("Bottom"), value: 10, decimals: 0}),
						numberfield({name: "pageLeftMargin", label: t("Left"), value: 10, decimals: 0}),
						h3({text: t("Font")}),
						select({
							name: "font",
							label: t("Family"),
							options: [
								{value: "dejavusans", name: "Deja vu Sans"},
								{value: "helvetica", name: "Helvetica"},
								{value: "courier", name: "Courier"}
							],
							value: "dejavusans"
						}),
						numberfield({
							name: "fontSize",
							label: t("Size"),
							value: 9,
							decimals: 0
						})
					),
					fieldset({cls: "vbox gap", flex: 1},
						h3({text: t("Label margins")}),
						numberfield({name: "labelTopMargin", label: t("Top"), value: 10, decimals: 0}),
						numberfield({name: "labelRightMargin", label: t("Right"), value: 10, decimals: 0}),
						numberfield({name: "labelBottomMargin", label: t("Bottom"), value: 10, decimals: 0}),
						numberfield({name: "labelLeftMargin", label: t("Left"), value: 10, decimals: 0}),
						h3({text: t("Template")}),
						textarea({
							name: "tpl",
							autoHeight: true,
							value: "{{contact.name}}\n" +
								"[assign address = contact.addresses | filter:type:\"postal\" | first]\n" +
								"[if !{{address}}]\n" +
								"[assign address = contact.addresses | first]\n" +
								"[/if]\n" +
								"{{address.formatted}}"
						})
					)
				)
			),
			tbar({cls: "border-top"},
				"->",
				btn({
					cls: "filled primary",
					text: t("Download"),
					handler: async () => {
						const response = await contactDS.query(this.queryParams);
						const formValues = this.form.value;

						localStorage.setItem("addressBookLabelValues", JSON.stringify(formValues));

						client.jmap("Contact/labels", {
							formValues,
							ids: response.ids
						}).then(async (value) => {
							await client.downloadBlobId(value.blobId, t("Labels"));
						}).catch((reason) => {
							Notifier.notify({text: reason, category: "error"});
						}).finally(() => {
							this.unmask();
						});
					}
				})
			)
		);

		this.on("render", () => {
			const values = localStorage.getItem("addressBookLabelValues");

			if (values) {
				try {
					this.form.value = JSON.parse(values);
				} catch (e) {
					console.warn("Failed to restore label settings from localStorage", e);
				}
			}
		});
	}
}