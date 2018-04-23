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