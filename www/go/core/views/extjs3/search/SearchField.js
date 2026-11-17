
go.search.SearchField = Ext.extend(Ext.form.TriggerField,{  
  hideLabel: true,
  anchor: "100%",
  placeholder: t("Search") + "...",
  validationEvent: false,
  validateOnBlur: false,
  triggerClass: 'x-form-clear-trigger',
  spellCheck: false,
  // enableKeyEvents: true,

  /**
   * Search automatically while typing from this number of characters. Shorter queries are only sent on enter.
   */
  minChars: 3,

  onTriggerClick: function () {
    this.setValue("");
    if(this.searchTask) {
      this.searchTask.cancel();
    }
    if(this.panel) {
      this.panel.collapse();
    }
  },
  search : function () {
    if(this.searchTask) {
      this.searchTask.cancel();
    }
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
        this.searchTask = new Ext.util.DelayedTask(this.search, this);

        this.panel = new go.search.Panel({
            searchField: this,
            listeners: {
              searchexception: function(panel, response) {
                this.markInvalid(response && response.type === "queryTimeout" ? response.message : t("Invalid search query"));
              },
              scope: this
            }
          });
          this.panel.render(this.getListParent()); 
          this.syncPanelSize();  
          
          this.getEl().on("input", function () {
            if(this.getValue().trim().length >= this.minChars) {
              this.searchTask.delay(500);
            } else {
              this.searchTask.cancel();
            }
          }, this);
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

          case e.ENTER:
            // search right away, also when shorter than minChars
            e.stopEvent();
            this.search();
            break;
  
          case e.DOWN:
            console.warn(e);
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