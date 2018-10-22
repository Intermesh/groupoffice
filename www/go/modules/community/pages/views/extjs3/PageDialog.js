go.modules.community.pages.PageDialog = Ext.extend(go.form.Dialog, {
	title: t("Page"),
	entityStore: go.Stores.get("Page"),
	width: 600,
	height: 600,
	siteId: '',
	initFormItems: function () {
		var items = [{
				xtype: 'fieldset',
				autoHeight: true,
				items: [
					{
						xtype: 'textfield',
						name: 'pageName',
						fieldLabel: t("Page name"),
						anchor: '100%',
						allowBlank: false
					},
					{
						xtype: 'xhtmleditor',
						name: 'content',
						fieldLabel: "",
						hideLabel: true,
						anchor: '100%',
						height: 300,
						allowBlank: true
					}]
			}
		]
		return items;
	},
	
	generateSlug: function(form){
	    PageName = form.getForm().getFieldValues(true)['pageName'];
	    //regex
	    slug = PageName;
	    return slug;
	},
	
	submit: function(){
	    slug = this.generateSlug(this.formPanel);
	    console.log("current slug: " + slug);
	    console.log("current siteId: " + this.siteId);
	    console.log("sortOrder = pagecount for siteId: "+this.siteId+", +1");
	    
	    go.modules.community.pages.PageDialog.superclass.submit.call(this);
	}
});
