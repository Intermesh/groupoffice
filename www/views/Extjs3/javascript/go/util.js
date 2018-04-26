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