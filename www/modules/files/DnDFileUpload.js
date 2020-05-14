GO.files.DnDFileUpload = function(doneCallback, element) {
	function isRegularFile(file, cb) {
		if(file.size > 4096) {
			cb(file);
			return;
		}
		var reader = new FileReader();
		reader.onerror = function() {
			reader.onloadend = reader.onprogress = reader.onerror = null;
			cb(false); // is a folder
		};
		reader.onloadend = reader.onprogress = function(e) {
			reader.onloadend = reader.onprogress = reader.onerror = null;
			if (e.type != 'loadend') {
				reader.abort();
			}
			cb(file);
		};
		reader.readAsDataURL(file);
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
			var files = e.dataTransfer.files,
				uploadCount = files.length,
				blobs = [];

			for (var i = 0; i < files.length; i++) {
				isRegularFile(files[i], function (file) {
					if (!file) {
						uploadCount--;
						return;
					}
					go.Jmap.upload(file, {
						success: function (response) {
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
			}
		});
	};
};