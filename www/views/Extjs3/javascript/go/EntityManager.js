(function () {
  
  var entities = {};
  var stores = {};
  
  go.Entities = {

    /**
     * Register an entity
     * 
     * this will create a global entity and store:
     * 
     * go.Stores.get("community", "name")]
     * go.entities[name]
     * 
     * @param {string} name
     * @param {object} jmapMethods
     * @returns {undefined}
     */
    register: function (package, module, name) {
      if(!entities[package]) {
        entities[package] = {};
        stores[package] = {};
      }
      
      if(!entities[package][module]) {
        entities[package][module] = {};
        stores[package][module] = {};
      }
      
      entities[package][module][name] = {
        name: name,
        module: module,
        package: package,
        goto: function (id) {
          go.Router.goto(this.package + "/" + this.module + "/" + this.name.toLowerCase() + "/" + id);
        }
      };     
    },

    get: function (package, module, name) {
      if(!entities[package]) {
        throw "Package " + package + " not registered";
      }
      
      if(!entities[package][module]) {
        throw "Module " + package + " not registered";
      }
      
      if(!entities[package][module][name]) {
        throw "Entity " + name + " not registered";
      }
      
      return entities[package][module][name];
      
    }
  };
  
  
  go.Stores = {
    get: function (package, module, name) {
      if(!stores[package]) {
        stores[package] = {};
      }
      
      if(!stores[package][module]) {
        stores[package][module] = {};
      }
      
      if(!entities[package][module][name]) {
        stores[package][module][name] = new go.data.EntityStore({
          entity: go.Entities.get(package, module, name)
        });
      }     
      
      return stores[package][module][name];
    }
  }

})();