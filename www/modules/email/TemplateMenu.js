GO.email.TemplateMenu = Ext.extend(Ext.menu.Menu, {	
	selectedTemplateId: null,	
	initComponent: function () {
		this.store = new go.data.Store({
			entityStore: "EmailTemplate",
			fields: ["id", "name"]
		});
		GO.email.TemplateMenu.superclass.initComponent.call(this);
		
		this.addEvents({change: true, beforechange: true});

		this.store.on("load", this.updateMenu, this);
		this.on("render", function () {
			this.store.load();
		}, this);
	},

	updateMenu: function () {
		this.removeAll();
		this.el.sync();
		
		this.add({
			text: t("None"),
			group: "templates",
			templateId: null,
			listeners:{
				scope: this,
				checkchange: this.onCheckChange
			},
			checked: this.selectedTemplateId === null
		});
		
		this.add("-");
		var records = this.store.getRange(), len = records.length;
		
		for (var i = 0; i < len; i++) {
			this.add({
				text: records[i].data.name,
				templateId: records[i].data.id,
				listeners:{
					scope: this,
					beforecheckchange:  this.onBeforeCheckChange,
					checkchange: this.onCheckChange
				},
				group: "templates",
				checked: this.selectedTemplateId === records[i].data.id
			});
		}
	},
	
	onCheckChange : function(item, checked) {
		
		if(checked) {			
			this.selectedTemplateId = item.templateId;
			this.fireEvent("change", this, this.selectedTemplateId);
		}
	},
	
	onBeforeCheckChange : function(item, checked) {
		
		if(!checked) {
			return true;
		}
		
		return this.fireEvent("beforechange", this, item.templateId);		
	},
	
	setSelectedTemplateId : function(id) {
		var item = this.items.find(function(i) {
			return i.templateId === id;
		});
		
		item.setChecked(true);
		this.selectedTemplateId = id;
	}

});
