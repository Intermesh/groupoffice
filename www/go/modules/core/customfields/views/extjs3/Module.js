
/*
 * This will add the module to the main tabpanel filled with all the modules
 */

go.Modules.register("core", 'customfields', {
  mainPanel: GO.customfields.MainPanel,
  title: t("Custom fields"),
  iconCls: 'go-tab-icon-customfields',
  admin: true,
  requiredPermissionLevel: GO.permissionLevels.write,
  entities: ["FieldSet", "Field"],
  initModule: function () {
    
  }
});
