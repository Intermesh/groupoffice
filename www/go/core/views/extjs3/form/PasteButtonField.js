go.form.PasteButtonField = Ext.extend(Ext.form.TriggerField, {
	triggerConfig: {
		tag: "button",
		type: "button",
		//tabindex: -1,
		cls: "x-form-trigger ic-content-paste",
		'ext:qtip': t("Paste")
	},
	onTriggerClick:  function () {

		if(!navigator.clipboard || !navigator.clipboard.readText) {
			Ext.MessageBox.alert(t("Sorry"), t("Reading from your clipboard isn't supported"));
			return;
		}

		navigator.clipboard.readText().then((clipText) => {
			this.setValue(clipText);
		}).catch((reason) => {
			console.error(reason);
			Ext.MessageBox.alert(t("Sorry"), t("Reading from your clipboard isn't allowed"));
		});
	}
});

