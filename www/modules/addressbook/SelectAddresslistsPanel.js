GO.addressbook.SelectAddresslistsPanel = Ext.extend(Ext.Panel, {
	
	addresslistElements : [],

		
	initComponent : function(){
		
		this.title=t("Address Lists", "addressbook");
		this.autoScroll=true;
		
		this.items=[{
			xtype: 'fieldset',
			items: [{
				xtype:'xcheckbox',
				boxLabel: t("Sending e-mails to this e-mail address is allowed", "addressbook"),
				name: 'email_allowed',
				hideLabel:true,
				checked: true
			}]
		},this.mailGroups = new Ext.form.FieldSet({
				title: t("Enabled address lists:", "addressbook"),
				defaults:{hideLabel: true},
				items: []
			})
		];		

		GO.addressbook.SelectAddresslistsPanel.superclass.initComponent.call(this);

	},
	
	removeComponents : function(){
			var f = this.ownerCt.ownerCt.form;
			for(var i=0;i<this.addresslistElements.length;i++)
			{
				f.remove(this.addresslistElements[i]);
				this.remove(this.addresslistElements[i], true);
			}
			this.addresslistElements=[];
		},
	
	loadComponents : function(){
		
		this.removeComponents();

		var f = this.ownerCt.ownerCt.form;
		
		for(var i=0;i<GO.addressbook.writableAddresslistsStore.data.items.length;i++)
		{
			var record = GO.addressbook.writableAddresslistsStore.data.items[i];
			
			this.addresslistElements.push(new Ext.form.Checkbox({
				boxLabel: record.data.name,
				labelSeparator: '',
				name: 'addresslist_'+record.data.id,
				autoCreate:  { tag: "input", type: "checkbox", autocomplete: "off", value: record.data.id },
				value:false
			}));
			
			this.mailGroups.add(this.addresslistElements[i]);
			f.add(this.addresslistElements[i]);
		}
		this.doLayout(false, true);
		
	},
	afterRender : function(){
		GO.addressbook.SelectAddresslistsPanel.superclass.afterRender.call(this);

		if(GO.addressbook.writableAddresslistsStore.loaded){
			this.loadComponents();
		}else
		{
			this.disabled=true;
		}

		GO.addressbook.writableAddresslistsStore.on('load', function(){
			this.loadComponents();
			this.setDisabled(false);
		}, this);
	}	
});

