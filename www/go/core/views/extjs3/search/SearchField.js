
go.search.SearchField = Ext.extend(Ext.form.TriggerField,{  
  hideLabel: true,
  anchor: "100%",
  placeholder: t("Search"),
  validationEvent: false,
  validateOnBlur: false,
  triggerClass: 'x-form-clear-trigger',
  // enableKeyEvents: true,

  onTriggerClick: function () {
    this.setValue("");
    this.search();
  },
  search : function () {
    this.clearInvalid();
    this.syncPanelSize();
    this.panel.expand();  
    this.panel.search(this.getValue());
  },

  getListParent : function() {
    return Ext.getBody()
  },

  syncPanelSize : function() 
  {
    this.panel.setWidth(this.getEl().getWidth());
    this.panel.setHeight(dp(500));
    this.panel.getEl().alignTo(this.getEl(), "tl-bl");
  },

  initComponent: function() {
    go.search.SearchField.superclass.initComponent.call(this);



    this.on({
      scope: this,

      render: function() {
        var dqTask = new Ext.util.DelayedTask(this.search, this);
  
        this.panel = new go.search.Panel({
            searchField: this,
            listeners: {
              searchexception: function() {
                this.markInvalid(t("Invalid search query"));
              },
              scope: this
            }
          });
          this.panel.render(this.getListParent()); 
          this.syncPanelSize();  
          
          this.getEl().on("input", function () {
            dqTask.delay(500);
          });
      },

      destroy : function() {
        if(this.panel) {
          this.panel.destroy();
        }
      },
      
      focus : function() {
        if(this.getValue()) {
          this.syncPanelSize();
          this.panel.expand(); 
          this.el.dom.select();
        }
      },
      specialkey: function (field, e) {
        switch (e.getKey()) {
          case e.ESC:
            this.panel.collapse();
            break;
  
          case e.DOWN:
            if (this.panel.isVisible()) {
              this.panel.grid.getSelectionModel().selectRow(0);
              this.panel.grid.getView().focusRow(0);
            }
            break;
        }
      }
    });
  
    
  }
});