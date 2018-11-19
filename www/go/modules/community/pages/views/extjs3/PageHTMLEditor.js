go.modules.community.pages.PageHTMLEditor = function (config) {
	config = config || {};

	Ext.applyIf(config, {
		border: false,
		enableFont: false,
		style: GO.settings.html_editor_font
	});

	config.plugins = config.plugins || [];

	if (!Ext.isArray(config.plugins))
		config.plugins = [config.plugins];

	var spellcheckInsertPlugin = new GO.plugins.HtmlEditorSpellCheck(this);
	var dividePlugin = new Ext.ux.form.HtmlEditor.Divider();
	var ioDentPlugin = new Ext.ux.form.HtmlEditor.IndentOutdent();
	var ssScriptPlugin = new Ext.ux.form.HtmlEditor.SubSuperScript();
	var HeaderPlugin = new Ext.ux.form.HtmlEditor.HeadingMenuEdited();
	var rmFormatPlugin = new Ext.ux.form.HtmlEditor.RemoveFormat();
	if (GO.settings.pspellSupport)
		config.plugins.unshift(spellcheckInsertPlugin);

	config.plugins.unshift(
					dividePlugin,
					HeaderPlugin,
					ioDentPlugin,
					ssScriptPlugin,
					rmFormatPlugin
					);

	go.modules.community.pages.PageHTMLEditor.superclass.constructor.call(this, config);
};

Ext.extend(go.modules.community.pages.PageHTMLEditor, GO.form.HtmlEditor,  {
});

Ext.reg('phtmleditor', go.modules.community.pages.PageHTMLEditor);