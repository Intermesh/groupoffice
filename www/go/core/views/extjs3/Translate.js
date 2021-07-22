go.Translate = {
  module: "core",
  package: "core",
  
  setModule : function(package, module) {
    this.module = module;
    this.package = package;
  },

  runFrom : function (package, module, callback) {
    const currentModule = this.module;
    const currentPackage = this.package;

    this.setModule(package, module);

    callback();

    this.setModule(currentPackage, currentModule);
  }
}
