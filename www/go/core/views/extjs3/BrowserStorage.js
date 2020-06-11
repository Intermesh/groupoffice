go.browserStorage = {
	dbName: "go",
	enabled: true,
	connect : function(version) {
		var me = this;
		if(!me.conn) {
			 me.conn = new Promise(function(resolve, reject) {		

					var	openreq = version ? indexedDB.open(me.dbName, version) : indexedDB.open(me.dbName); //IE11 required the if/else
					openreq.onerror = function() {
						me.enabled = false;
						console.warn("Disabling browser storage in indexedDB because browser doesn't support it.")
						reject(openreq.error);
					};
					openreq.onsuccess = function() {
						
						if(me.upgradeNeeded(openreq.result)) {
							var newVersion = openreq.result.version + 1;
							console.warn("IndexedDB Upgrade needed. Bumping version to " + (newVersion));
							openreq.result.close();
							me.conn = null;

					 		me.connect(newVersion).then(function() {
                resolve(openreq.result); 
              }).catch(function(error) {
              	console.error("Upgrade failed. Deleting database and disabling storage.");
								me.enabled = true;
								me.deleteDatabase();
								me.enabled = false;
								reject(error);
							});
						}
												
						openreq.result.onversionchange = function(e) {
							console.warn("Version change");
							openreq.result.close();
							me.conn = null;
						}
						resolve(openreq.result); 
					}
		
				openreq.onblocked = function() {
					console.warn("IndexedDB upgrade blocked");
		
					reject("blocked");
				}
		
				openreq.onupgradeneeded = function(e) {
					var upgradeDb = e.target.result;

					var e = go.Entities.getAllInstalled();
					for(var n in  e) {
						var name = e[n].name;
						
						if(!upgradeDb.objectStoreNames.contains(name)) {							
							upgradeDb.createObjectStore(name);					
						}

						if(!upgradeDb.objectStoreNames.contains(name + "-meta")) {							
							upgradeDb.createObjectStore(name + "-meta");			
						}
          }
          
          // upgradeDb.createObjectStore("test_6");
				};
      });			
    } 
    
    return me.conn;
		
	},

	upgradeNeeded : function(db) {

    // if(!db.objectStoreNames.contains("test_6")) {
    //   return true;
    // }

		var e = go.Entities.getAllInstalled();
		for(var n in  e) {
			var name = e[n].name;

			if(!db.objectStoreNames.contains(name)) {
				return true;
			}

			if(!db.objectStoreNames.contains(name + "-meta")) {
				return true;
			}
		}

		return false;
	},

	deleteDatabase : function () {

		if(!this.enabled) {
			return Promise.resolve(null);
		}

		var me = this;
		return new Promise(function(resolve, reject) {
				var openreq = indexedDB.deleteDatabase(me.dbName);
				openreq.onerror = function() { reject(openreq.error);};
				openreq.onsuccess = function() { resolve(openreq.result); };
		});
	}
};

go.browserStorage.Store = function(storeName) {
	this.storeName = storeName;
};

go.browserStorage.Store.prototype._withIDBStore = function (type, callback) {
	var me = this;
	
	return go.browserStorage.connect().then(function(db) {  
			return me.createTransaction(db, type, callback);			 
	});
}

go.browserStorage.Store.prototype.createTransaction = function(db, type, callback) {
	var me = this;
	return new Promise( function(resolve, reject) {
		var transaction = db.transaction(me.storeName, type);
		transaction.oncomplete = function() {
				resolve();
		}
		transaction.onabort = transaction.onerror = function() {
				reject(transaction.error);
		} 

		callback(transaction.objectStore(me.storeName));

	});
}

go.browserStorage.Store.prototype.getItem = function(key) {

	if(!go.browserStorage.enabled) {
		return Promise.resolve(null);
	}

	var req;
	return this._withIDBStore('readonly', function(store) {
		req = store.get(key);
	}).then(function() { 
			return req.result;
	});
}

go.browserStorage.Store.prototype.setItem = function(key, value) {
	if(!go.browserStorage.enabled) {
		return Promise.resolve(null);
	}

	return this._withIDBStore('readwrite',function(store) { 
			store.put(value, key);
	});
}

go.browserStorage.Store.prototype.removeItem = function(key) {
	if(!go.browserStorage.enabled) {
		return Promise.resolve(null);
	}

	return this._withIDBStore('readwrite', function(store) { 
			return store.delete(key);
	});
}

go.browserStorage.Store.prototype.clear = function() {
	if(!go.browserStorage.enabled) {
		return Promise.resolve(null);
	}
	return this._withIDBStore('readwrite', function(store) { 
			return store.clear();
	});
}

go.browserStorage.Store.prototype.keys = function() {
	if(!go.browserStorage.enabled) {
		return Promise.resolve([]);
	}
	var keys = [];
	return this._withIDBStore('readonly',function(store) {
			// This would be store.getAllKeys(), but it isn't supported by Edge or Safari.
			// And openKeyCursor isn't supported by Safari.
			(store.openKeyCursor || store.openCursor).call(store).onsuccess = function () {
					if (!this.result)
							return;
					keys.push(this.result.key);
					this.result.continue();
			};
	}).then(function() { return keys;});
}