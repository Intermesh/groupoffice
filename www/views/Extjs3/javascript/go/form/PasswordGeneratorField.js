go.form.PasswordGeneratorField = Ext.extend(Ext.form.TriggerField, {
	inputType: 'password',
	fieldLabel: t("Password"),
	name: 'password',
	triggerConfig: {
		tag: "button",
		type: "button",
		cls: "x-form-trigger ic-refresh",
		'ext:qtip': t("Generate password")
	},
	initComponent: function() {
		this.defaultAutoCreate.autocomplete = "new-password"; //prevent autocomplete in chrome
		go.form.PasswordGeneratorField.superclass.initComponent.call(this);
		
		this.on("afterrender", function(field) {
			//clear browser autofill from firefox
			setTimeout(function(){
				field.reset();
			}, 500);							
		});
	},
	onTriggerClick: function () {
		var pass = this.generatePassword(8);
		this.setValue(pass);
		
		go.util.copyTextToClipboard(pass);
		
		Ext.MessageBox.alert(t("Password", "users"), t("The generated password has been copied to your clipboard.")); // + ": " + Ext.util.Format.htmlEncode(pass)

		this.fireEvent('generated', this, pass);
	},

	generatePassword: function (length) {

		var charsets = [
			"abcdefghijklmnopqrstuvwxyz",
			"ABCDEFGHIJKLMNOPQRSTUVWXYZ",
			"1234567890",
			"!@#$%^&*()<>,."];

		var pass = "";
		var i;

		//take one from each
		for (var x = 0; x < charsets.length; x++) {
			i = Math.floor(Math.random() * charsets[x].length);
			pass += charsets[x].charAt(i);
		}

		var combined = charsets.join("");

		length -= charsets.length;

		for (var x = 0; x < length; x++)
		{
			i = Math.floor(Math.random() * combined.length);
			pass += combined.charAt(i);
		}
		return pass;

	}
});

