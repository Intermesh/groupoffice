
/*
 * This will add the module to the main tabpanel filled with all the modules
 */

go.Modules.register("core", 'customfields', {
  mainPanel: GO.customfields.MainPanel,
  title: t("Custom fields", "customfields"),
  iconCls: 'go-tab-icon-customfields',
  admin: true,
  requiredPermissionLevel: GO.permissionLevels.write,
  entities: ["FieldSet", "Field"],
  initModule: function () {
    go.Stores.get("Field").getUpdates(function (store) {
      go.CustomFields.fieldsLoaded = true;
      go.CustomFields.fireReady();
//		console.log(go.Stores.get("Field"));
    });

    go.Stores.get("FieldSet").getUpdates(function (store) {
//		console.log(go.Stores.get("FieldSet"));
      go.CustomFields.fieldSetsLoaded = true;
      go.CustomFields.fireReady();
    });
  }
});
