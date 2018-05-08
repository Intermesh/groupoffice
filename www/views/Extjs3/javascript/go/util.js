go.util.mergeObjects = function (a, b) {
	for (var item in b) {
		if (a[item]) {
			if (typeof b[item] === 'object' && !b[item].length) {
				GO.util.mergeObjects(a[item], b[item]);
			} else {
				if (typeof a[item] === 'object' || typeof b[item] === 'object') {
					a[item] = [].concat(a[item], b[item]);
				} else {
					a[item] = [a[item], b[item]];  // assumes that merged members that are common should become an array.
				}
			}
		} else {
			a[item] = b[item];
		}
	}
	return a;
};

/**
 * Convert bytes to a user readable format
 * 
 * @param int bytes
 * @param boolean conventionDecimal
 * @return {String}
 */
go.util.humanFileSize = function(bytes, conventionDecimal) {
    var thresh = conventionDecimal ? 1000 : 1024;
    if(Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
    var units = conventionDecimal
        ? ['kB','MB','GB','TB','PB','EB','ZB','YB']
        : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
    var u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while(Math.abs(bytes) >= thresh && u < units.length - 1);
    return bytes.toFixed(1)+' '+units[u];
};

/**
 * Get the class for the content type icon based on mimetype or extension
 * @param {string} contentType
 * @param {string} extension
 * @returns {string} class name used in _icons.scss
 */
go.util.contentTypeClass = function(contentType, filename) {
	var icon, extension = filename.split('.');
	if(extension.length > 1) {
		icon = extension[extension.length-1];
	} else {
		icon = 'folder';
	}
	if(contentType !== null) {
		// todo: map contentTypes
	}
	return icon;
}

/**
 * PRIVATE
 * Needed for go.util.getDiff function to compare differences between 2 objects
 * 
 * @param {object} a
 * @param {object} b
 * @param {type} node
 * @return {undefined}
 */
go.util.recursiveDiff = function(a, b, node) {
	var checked = [];

	for (var prop in a) {
		if (typeof b[prop] == 'undefined') {
			go.util.addNode(prop, '[[removed]]', node);
		} else if (JSON.stringify(a[prop]) != JSON.stringify(b[prop])) {
			// if value
			if (typeof b[prop] != 'object' || b[prop] == null) {
				go.util.addNode(prop, b[prop], node);
			} else {
				// if array
				if (Ext.isArray(b[prop])) {
					go.util.addNode(prop, [], node);
					go.util.recursiveDiff(a[prop], b[prop], node[prop]);
				}
				// if object
				else {
					go.util.addNode(prop, {}, node);
					go.util.recursiveDiff(a[prop], b[prop], node[prop]);
				}
			}
		}
	}
};

/**
 * PRIVATE
 * Needed for above recursiveDiff function
 * 
 * @param {type} prop
 * @param {type} value
 * @param {type} parent
 * @return {undefined}
 */
go.util.addNode = function(prop, value, parent){
	parent[prop] = value;
};

/**
 * Get the difference between 2 objects
 * 
 * @param {object} a
 * @param {object} b
 * @return {Array}
 */
go.util.getDiff = function(a, b){
	var diff = (Ext.isArray(a) ? [] : {});
	go.util.recursiveDiff(a, b, diff);
	return diff;
};

