go.customfields.DetailPanel = Ext.extend(Ext.Panel, {

  bodyCssClass: 'icons',
  collapsible: true,
  fieldSet: null,
  hidden: true,

  initComponent: function() {

    this.stateId = "cf-detail-field-set-" + this.fieldSet.id;
    // this.fieldSetId = this.fieldSet.id;
    this.title = this.fieldSet.name;

    this.items = [];

    var me =  this;
    go.customfields.CustomFields.getFields(this.fieldSet.id).forEach(function (field) {
      var type = go.customfields.CustomFields.getType(field.type);
      if(!type) {
        console.error("Custom field type " + field.type + " not found");
        return;
      }
      var cmp = type.getDetailField(field);
      cmp.field = field;
      me.items.push(cmp);
    });

    this.supr().initComponent.call(this);
  },

  onLoad: function(dv) {

    if(!this.isVisibleByFilter(dv.data)) {
     this.setVisible(false);
     return''
    }

    var vis = false, panel = this;
    go.customfields.CustomFields.getFields(this.fieldSet.id).forEach(function (field) {
      if(!GO.util.empty(dv.data.customFields[field.databaseName])) {
        vis = true;
      }

      var cmp = panel.getComponent(field.databaseName), type = go.customfields.CustomFields.getType(field.type);

      if(cmp) {
        var v = type.renderDetailView(dv.data.customFields[field.databaseName], dv.data.customFields, field, cmp);

        if(typeof(v) !== "undefined") {
          cmp.setValue(v);
          cmp.setVisible(!!v);
        }
      }
    });

    this.setVisible(vis);
  },

  /**
   * Show this fieldset by filtering the entity values.
   *
   * @param {object} entity
   * @returns {boolean}
   */
  isVisibleByFilter: function (entity) {
    for (var name in this.fieldSet.filter) {
      var v = this.fieldSet.filter[name];

      if (Ext.isArray(v)) {
        if (v.indexOfLoose(entity[name]) === -1) {
          return false;
        }
      } else
      {
        if (v != entity[name]) {
          return false;
        }
      }
    }
    return true;
  }

});