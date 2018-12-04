(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('localforage')) :
    typeof define === 'function' && define.amd ? define(['exports', 'localforage'], factory) :
    (factory((global.localforageGetItems = global.localforageGetItems || {}),global.localforage));
}(this, (function (exports,localforage) { 'use strict';

localforage = 'default' in localforage ? localforage['default'] : localforage;

function getSerializerPromise(localForageInstance) {
    if (getSerializerPromise.result) {
        return getSerializerPromise.result;
    }
    if (!localForageInstance || typeof localForageInstance.getSerializer !== 'function') {
        return Promise.reject(new Error('localforage.getSerializer() was not available! ' + 'localforage v1.4+ is required!'));
    }
    getSerializerPromise.result = localForageInstance.getSerializer();
    return getSerializerPromise.result;
}



function executeCallback(promise, callback) {
    if (callback) {
        promise.then(function (result) {
            callback(null, result);
        }, function (error) {
            callback(error);
        });
    }
    return promise;
}

function getItemKeyValue(key, callback) {
    var localforageInstance = this;
    var promise = localforageInstance.getItem(key).then(function (value) {
        return {
            key: key,
            value: value
        };
    });
    executeCallback(promise, callback);
    return promise;
}

function getItemsGeneric(keys /*, callback*/) {
    var localforageInstance = this;
    var promise = new Promise(function (resolve, reject) {
        var itemPromises = [];

        for (var i = 0, len = keys.length; i < len; i++) {
            itemPromises.push(getItemKeyValue.call(localforageInstance, keys[i]));
        }

        Promise.all(itemPromises).then(function (keyValuePairs) {
            var result = {};
            for (var i = 0, len = keyValuePairs.length; i < len; i++) {
                var keyValuePair = keyValuePairs[i];

                result[keyValuePair.key] = keyValuePair.value;
            }
            resolve(result);
        }).catch(reject);
    });
    return promise;
}





function getAllItemsUsingIterate() {
    var localforageInstance = this;
    var accumulator = {};
    return localforageInstance.iterate(function (value, key /*, iterationNumber*/) {
        accumulator[key] = value;
    }).then(function () {
        return accumulator;
    });
}

function getIDBKeyRange() {
    /* global IDBKeyRange, webkitIDBKeyRange, mozIDBKeyRange */
    if (typeof IDBKeyRange !== 'undefined') {
        return IDBKeyRange;
    }
    if (typeof webkitIDBKeyRange !== 'undefined') {
        return webkitIDBKeyRange;
    }
    if (typeof mozIDBKeyRange !== 'undefined') {
        return mozIDBKeyRange;
    }
}

var idbKeyRange = getIDBKeyRange();

function getItemsIndexedDB(keys /*, callback*/) {
    keys = keys.slice();
    var localforageInstance = this;
    function comparer(a, b) {
        return a < b ? -1 : a > b ? 1 : 0;
    }

    var promise = new Promise(function (resolve, reject) {
        localforageInstance.ready().then(function () {
            // Thanks https://hacks.mozilla.org/2014/06/breaking-the-borders-of-indexeddb/
            var dbInfo = localforageInstance._dbInfo;
            var store = dbInfo.db.transaction(dbInfo.storeName, 'readonly').objectStore(dbInfo.storeName);

            var set = keys.sort(comparer);

            var keyRangeValue = idbKeyRange.bound(keys[0], keys[keys.length - 1], false, false);
            var req = store.openCursor(keyRangeValue);
            var result = {};
            var i = 0;

            req.onsuccess = function () /*event*/{
                var cursor = req.result; // event.target.result;

                if (!cursor) {
                    resolve(result);
                    return;
                }

                var key = cursor.key;

                while (key > set[i]) {

                    // The cursor has passed beyond this key. Check next.
                    i++;

                    if (i === set.length) {
                        // There is no next. Stop searching.
                        resolve(result);
                        return;
                    }
                }

                if (key === set[i]) {
                    // The current cursor value should be included and we should continue
                    // a single step in case next item has the same key or possibly our
                    // next key in set.
                    var value = cursor.value;
                    if (value === undefined) {
                        value = null;
                    }

                    result[key] = value;
                    // onfound(cursor.value);
                    cursor.continue();
                } else {
                    // cursor.key not yet at set[i]. Forward cursor to the next key to hunt for.
                    cursor.continue(set[i]);
                }
            };

            req.onerror = function () /*event*/{
                reject(req.error);
            };
        }).catch(reject);
    });
    return promise;
}

function getItemsWebsql(keys /*, callback*/) {
    var localforageInstance = this;
    var promise = new Promise(function (resolve, reject) {
        localforageInstance.ready().then(function () {
            return getSerializerPromise(localforageInstance);
        }).then(function (serializer) {
            var dbInfo = localforageInstance._dbInfo;
            dbInfo.db.transaction(function (t) {

                var queryParts = new Array(keys.length);
                for (var i = 0, len = keys.length; i < len; i++) {
                    queryParts[i] = '?';
                }

                t.executeSql('SELECT * FROM ' + dbInfo.storeName + ' WHERE (key IN (' + queryParts.join(',') + '))', keys, function (t, results) {

                    var result = {};

                    var rows = results.rows;
                    for (var i = 0, len = rows.length; i < len; i++) {
                        var item = rows.item(i);
                        var value = item.value;

                        // Check to see if this is serialized content we need to
                        // unpack.
                        if (value) {
                            value = serializer.deserialize(value);
                        }

                        result[item.key] = value;
                    }

                    resolve(result);
                }, function (t, error) {
                    reject(error);
                });
            });
        }).catch(reject);
    });
    return promise;
}

function localforageGetItems(keys, callback) {
    var localforageInstance = this;

    var promise;
    if (!arguments.length || keys === null) {
        promise = getAllItemsUsingIterate.apply(localforageInstance);
    } else {
        var currentDriver = localforageInstance.driver();
        if (currentDriver === localforageInstance.INDEXEDDB) {
            promise = getItemsIndexedDB.apply(localforageInstance, arguments);
        } else if (currentDriver === localforageInstance.WEBSQL) {
            promise = getItemsWebsql.apply(localforageInstance, arguments);
        } else {
            promise = getItemsGeneric.apply(localforageInstance, arguments);
        }
    }

    executeCallback(promise, callback);
    return promise;
}

function extendPrototype(localforage$$1) {
    var localforagePrototype = Object.getPrototypeOf(localforage$$1);
    if (localforagePrototype) {
        localforagePrototype.getItems = localforageGetItems;
        localforagePrototype.getItems.indexedDB = function () {
            return getItemsIndexedDB.apply(this, arguments);
        };
        localforagePrototype.getItems.websql = function () {
            return getItemsWebsql.apply(this, arguments);
        };
        localforagePrototype.getItems.generic = function () {
            return getItemsGeneric.apply(this, arguments);
        };
    }
}

var extendPrototypeResult = extendPrototype(localforage);

exports.localforageGetItems = localforageGetItems;
exports.extendPrototype = extendPrototype;
exports.extendPrototypeResult = extendPrototypeResult;
exports.getItemsGeneric = getItemsGeneric;

Object.defineProperty(exports, '__esModule', { value: true });

})));
