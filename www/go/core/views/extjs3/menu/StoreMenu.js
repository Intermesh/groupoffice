/**
 * @example
 * 	menu: this.templatesMenu = new go.menu.StoreMenu({
  // cls: "x-menu-no-icons",
  displayField: "name",
  store: new go.data.Store({
    fields: ['id', 'name', 'body', 'subject', 'attachments'],
    entityStore: "EmailTemplate",
    filter: {
      module: {module: {name: 'newsletters', package: 'business'}}
    }
  }),
  listeners: {
    scope: this,
    
    createitem: function(item, record, index) {
      item.group = "template";
      item.checked = index === 0;

      if(item.checked) {
        this.setValues({
          subject: first.data.subject,
          attachments: first.data.attachments,
          body: first.data.body
        });
      }

      item.listeners = {
        checkchange: function(item, checked) {
          this.setValues({
            subject: item.record.data.subject,
            attachments: item.record.data.attachments,
            body: item.record.data.body
          });
        },
        scope: this
      };
    }
  
    // itemclick: function(item, e) {
    // 	this.setValues({
    // 		subject: item.record.data.subject,
    // 		attachments: item.record.data.attachments,
    // 		body: item.record.data.body
    // 	});
    // }
  }
})
 */
go.menu.StoreMenu = Ext.extend(Ext.menu.Menu, {
  loadingText: t("Loading..."),

  loaded: false,

  displayField: "text",

  iconClsField: false,

  initComponent: function () {

    this.on('show', this.loadMenu, this);
    this.store.on('load', this.onLoad, this);

    this.addEvents({load: true, itemclick: true, createitem: true});

    // this.on("afterrender", function() {
    //   this.updateMenuItems();
    // });

    go.menu.StoreMenu.superclass.initComponent.call(this);

    this.add({
      text: "<small>" + this.loadingText + "</small>"
    });
  },

  loadMenu: function () {
    if(!this.store.loaded && !this.store.loading) {

      this.store.load();    
    }
  },

  updateMenuItems: function () {
    if (this.rendered) {
      this.removeAll();
      this.el.sync();

      var records = this.store.getRange();

      for (var i = 0, len = records.length; i < len; i++) {     
        var item = this.createItem(records[i], i);
        this.add(item);
      }

      this.fireEvent('load', this, records);
    }
  },

  createItem : function(record, index) {
    var item = {};
    item.text = record.json[this.displayField];
    item.record = record;
    item.iconCls = this.iconClsField ? record.json[this.iconClsField] : null;

    this.fireEvent('createitem', item, record, index);

    return item;
  },

  onLoad: function (store, records) {
    this.updateMenuItems();
  }

});