go.modules.community.pages.PageHTMLEditor = Ext.extend(GO.form.HtmlEditor, {
//    config: {
//	border: false,
//	enableFont: false,
//	style: GO.settings.html_editor_font
//    },
    initComponent : function(){
        go.modules.community.pages.PageHTMLEditor.superclass.initComponent.call(this);
	//go.modules.community.pages.PageHTMLEditor.superclass.constructor.call(this, config);
	
    }
});

Ext.reg('phtmleditor', go.modules.community.pages.PageHTMLEditor);