const GOUILinkDetail = Ext.extend(go.GOUIWrapper, {
	load: function (id) {
		this.comp.load(id);
	}
})

go.links.LinkDetailWindow = Ext.extend(go.Window, {
  entity: "",
  layout: "fit",
  initComponent: function() {

    var str = this.entity;
    this.entity = go.Entities.get(this.entity);
    if (!this.entity) {
      throw str + " is not a registered entity";
    }

    this.stateId = "go-link-detail-" + this.entity.name;

		const dtl = this.entity.links[0].linkDetail();

		if(dtl.getItemId) {
			//native extjs comp
			this.detailView = dtl;
		} else {
			//GOUI comp
			this.detailView = new GOUILinkDetail({comp: dtl});
		}


    this.tools = [{
      id: 'home',
      handler: function() {
        this.entity.goto(this.currentId);
        this.close();
      },
      scope: this
    }];

    this.title = this.entity.title;

    this.width = this.detailView.width || dp("1000");
    this.height = this.detailView.height || dp("700");

    this.items = [
      this.detailView
    ];

    go.links.LinkDetailWindow.superclass.initComponent.call(this);

  },

  currentId: null,

  load: function(id) {
    
    if(!this.isVisible()) {
      this.show();
    }
    this.currentId = id;
    this.detailView.load(id);

    return this;
  }
});


