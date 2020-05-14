go.modules.community.addressbook.DuplicateDialog = Ext.extend(go.Window, {

  title: t("Filter duplicates"),
  layout: "border",
  modal: true,
  width: dp(1000),
  height: dp(800),
  initComponent: function () {
    this.items = [
      this.createFilter(),
      this.createGrid()
    ];

    this.bbar = [
      '->',
      {
        text: t("Merge selected"),
        handler: function() {

          Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to merge the selected items? This can't be undone."), function(btn) {
            if(btn != 'yes') {
              return;
            }

            var ids = this.grid.getSelectionModel().getSelections().map(function(r) {return r.id;});

            go.Db.store("Contact").merge(ids).catch(function(result) {
              Ext.MessageBox.alert(t("Error"), result.message);
            });
          }, this);

          
        },
        scope: this
      }
    ];

    this.supr().initComponent.call(this);

    this.grid.on('viewready', function() {
      this.doFilter();
    }, this);
  },

  doFilter: function() {

    var dup = this.duplicate.getValue(), f = {permissionLevel: go.permissionLevels.writeAndDelete};
    var duparr = [];
    for(var key in dup) {
      if(dup[key]) {
        duparr.push(key);
      }
    }

    if(duparr.length) {
      f.duplicate = duparr;
    }
    this.grid.store.setFilter('filter', f);
    this.grid.store.load();
  },

  createFilter: function () {
    this.filter = new Ext.Panel({
      region: "north",
      autoHeight: true,
      layout: "fit",
      items: [this.duplicate = new go.form.FormContainer({
        region: 'north',        
        layout: "form",
        
        items: [{
          defaults : {
            listeners: {
              check: function() {
                this.doFilter();
              },
              scope: this
            },
            hideLabel: true,
            xtype: "checkbox"
          },
          xtype: "checkboxgroup",
          items: [{
            
            name: "name",
            boxLabel: t("Name"),
            checked: true
            
          }, {            
            name: "isOrganization",
            boxLabel: t("Organisation or contact"),
            checked: true            
          }, {
            name: "emailAddresses",
            boxLabel: t("E-mail addresses"),
            checked: false            
          }, {
            name: "phoneNumbers",
            boxLabel: t("Phone numbers")           
          }, {
            name: "addressBookId",
            boxLabel: t("Address book")
            
          }]
        }],
        
      })
      ]
    });

    return this.filter;
  },

  createGrid: function () {
    this.grid = new go.modules.community.addressbook.ContactGrid({
      stateId: 'contact-duplicate',
      region: 'center',
      tbar: [
        '->',
        {
          xtype: 'tbsearch'
        }
      ]
    });

    return this.grid;
  }
});