GO.files.DnDFileUpload = function(doneCallback, element) {


	function upload(nodes, subfolder) {
		var uploadCount = 0,
			blobs = [];

		uploadCount += nodes.length;
		Array.prototype.forEach.call(nodes, function(node, i) {
			if(node && node.isDirectory) {
				node.createReader().readEntries(function(subnodes) {
					upload(subnodes, node.fullPath.replace(/^\//,"").split('/'));
				});
				uploadCount--; // dont upload folders
				return;
			}
			node.file(function (file) {
				if (!file) {
					uploadCount--; //skip file if not found?
					return;
				}
				go.Jmap.upload(file, {
					success: function (response) {
						if(subfolder) {
							response.subfolder = subfolder;
						}
						blobs.push(response);
					},
					callback: function (response) {
						uploadCount--;
						if (uploadCount === 0) {
							doneCallback(blobs);
						}
					}
				});
			});
		});
	}

	return function() {
		var childCount = 0;
		element.dom.addEventListener('dragenter', function (e) {
			e.preventDefault();
			e.stopPropagation();
			childCount++;
			element.addClass('x-dd-over');
		});

		element.dom.addEventListener('dragleave', function (e) {
			e.preventDefault();
			childCount--;
			if (childCount === 0) {
				element.removeClass('x-dd-over');
			}
		});

		element.dom.addEventListener('dragover', function (e) {
			e.preventDefault(); // THIS IS NEEDED
			e.stopPropagation();
		});

		element.dom.addEventListener('drop', function (e) {
			e.stopPropagation();
			e.preventDefault();
			element.removeClass('x-dd-over');
			var entries = [];
			// convert files[] to entries[]
			for (var i = 0; i < e.dataTransfer.items.length; i++) {
				if (e.dataTransfer.items[i].webkitGetAsEntry) {
					entries.push(e.dataTransfer.items[i].webkitGetAsEntry());
				} else if (e.dataTransfer.items[i].getAsEntry) {
					entries.push(e.dataTransfer.items[i].getAsEntry());
				}
			}
			upload(entries);
		});
	};
};