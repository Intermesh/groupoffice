/* global go, Ext */

GO.smime.CertificateDetailWindow = Ext.extend(Ext.Window, {

	title: t("Certificate"),
	cls: 'go-form-panel',
	autoScroll: true,
	width:680,
	height:360,

	initComponent: function () {

		this.tpl = new Ext.XTemplate('<tpl><tpl if="values.text"><span class="{cls}"><i class="icon {icon}"></i>{text}</span></tpl>'+
			'<table>'+
			'<tr><td width="100">' + t("Name") + ':</td><td>{name}</td></tr>'+
			'<tr><td width="100">'+t("E-mail", "smime")+':</td><td>{[values.emails.join(", ")]}</td></tr>'+
			'<tr><td>'+t("Hash", "smime")+':</td><td>{hash}</td></tr>'+
			'<tr><td>'+t("Serial number", "smime")+':</td><td>{serialNumber}</td></tr>'+
			'<tr><td>'+t("Version", "smime")+':</td><td>{version}</td></tr>'+
			'<tr><td>'+t("Issuer", "smime")+':</td><td>'+
			'<tpl if="values.issuer.C">C: {values.issuer.C}; </tpl>'+
			'<tpl if="values.issuer.CN">CN: {values.issuer.CN}; </tpl>'+
			'<tpl if="values.issuer.L">L: {values.issuer.L}; </tpl>'+
			'<tpl if="values.issuer.O">O: {values.issuer.O}; </tpl>'+
			'<tpl if="values.issuer.ST">ST: {values.issuer.ST};</tpl>'+
			'</td></tr>'+
			'<tr><td>'+t("Valid from", "smime")+':</td><td>{validFrom}</td></tr>'+
			'<tr><td>'+t("Valid to", "smime")+':</td><td>{validTo}</td></tr>'+
			'<tpl if="values.ocspFound"><tr><td>OCSP:</td><td class="{[!values.ocsp ? "danger" : "success"]}">{ocspMsg}</td></tr></tpl>'+
			'</table></div>');

		this.supr().initComponent.call(this);
	},

	load(data) {
		this.update(data);
	}
});