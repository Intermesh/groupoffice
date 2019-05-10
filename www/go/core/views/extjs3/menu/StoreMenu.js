go.menu.StoreMenu = Ext.extend(Ext.menu.Menu, {
  loadingText: t("Loading..."),

  loaded: false,

  displayField: "text",

  iconClsField: false,

  initComponent: function () {

    this.on('show', this.loadMenu, this);
    //this.store.on('beforeload', this.onBeforeLoad, this);
    this.store.on('load', this.onLoad, this);

    this.addEvents({load: true, itemclick: true});

    this.on("afterrender", function() {
      this.updateMenuItems();
    });

    go.menu.StoreMenu.superclass.initComponent.call(this);
  },

  loadMenu: function () {
    this.store.load();    
  },

  updateMenuItems: function () {
    if (this.rendered) {
      this.removeAll();
      this.el.sync();

      var records = this.store.getRange();

      for (var i = 0, len = records.length; i < len; i++) {     
        var item = {};
        item.text = records[i].json[this.displayField];
        item.record = records[i];
        item.iconCls = this.iconClsField ? records[i].json[this.iconClsField] : null;
        this.add(item);
      }

      this.fireEvent('load', this, records);
    }
  },

  onLoad: function (store, records) {
    this.updateMenuItems();
  }

});