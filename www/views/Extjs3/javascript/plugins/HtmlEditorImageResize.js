GO.plugins.HtmlEditorImageResize = function(config) {

	config = config || {};

	Ext.apply(this, config);

	this.init = function (htmlEditor) {
		this.editor = htmlEditor;
		// this.editor.on('render', this.onRender, this);
		this.editor.on('initialize', this.addImageHandlers, this);
	};

};


Ext.extend(GO.plugins.HtmlEditorImageResize, Ext.util.Observable, {
	addImageHandlers: function () {

		const editorDoc = this.editor.getDoc();
		let activeWrapper = null;
		let isResizing = false;
		let lastRange = null; // Store the last cursor po

		const style = editorDoc.createElement('style');
		style.innerHTML = `
            .img-wrapper {
                position: relative;
                display: inline-block;
                padding: 5px;
                margin: 5px;
                vertical-align: middle;
                outline: 2px solid #4285f4;
                user-select: none;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
            }
            .img-wrapper * {
                user-select: none;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
            }
            .img-resize-handle {
                width: 8px;
                height: 8px;
                background-color: #4285f4;
                position: absolute;
                z-index: 1000;
            }
            .img-resize-handle.tl { top: -4px; left: -4px; cursor: nwse-resize; }
            .img-resize-handle.tr { top: -4px; right: -4px; cursor: nesw-resize; }
            .img-resize-handle.bl { bottom: -4px; left: -4px; cursor: nesw-resize; }
            .img-resize-handle.br { bottom: -4px; right: -4px; cursor: nwse-resize; }
            .image-size {
                position: absolute;
                top: -20px;
                right: 0;
                background: rgba(0, 0, 0, 0.7);
                color: white;
                font-size: 12px;
                padding: 2px 5px;
                border-radius: 3px;
            }
            /* Only hide cursor when image-editing is active */
            body.image-editing {
                caret-color: transparent !important;
            }

.img-wrapper {
    position: relative;
    display: inline-block;
    padding: 10px;
    margin: 10px;
    vertical-align: middle;
    outline: 2px solid #4285f4;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}
.size-menu {
    position: absolute;
    bottom: -20px;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 12px;
    padding: 2px;
    border-radius: 3px;
    display: flex;
    gap: 2px;
}
.size-menu-option {
    padding: 2px 5px;
    cursor: pointer;
    border-radius: 2px;
    background: rgba(100, 100, 100, 0.8) !important;
    color: white;
}
.size-menu-option:hover {
    background: rgba(150, 150, 150, 0.8) !important;
}
        `;
		editorDoc.head.appendChild(style);
		const removeWrapper = () => {
			if (activeWrapper && !isResizing && activeWrapper.parentNode) {
				const img = activeWrapper.querySelector('img');
				if (img) {
					const width = img.style.width || img.getAttribute('width');
					const height = img.style.height || img.getAttribute('height');

					activeWrapper.parentNode.insertBefore(img, activeWrapper);

					if (width) img.style.width = width.includes('px') ? width : width + 'px';
					if (height) img.style.height = height.includes('px') ? height : height + 'px';

					// Create a range and select the image
					const range = editorDoc.createRange();
					range.selectNode(img.parentNode && img.parentNode.tagName == "A" ? img.parentNode : img);
					const selection = editorDoc.getSelection();

					// Clear current selection
					selection.removeAllRanges();

					// Add the new range to the selection
					selection.addRange(range);
				} else {
					// Restore cursor position
					if (lastRange) {
						const selection = editorDoc.getSelection();
						selection.removeAllRanges();
						selection.addRange(lastRange);
						lastRange = null;
					}
				}
				activeWrapper.parentNode.removeChild(activeWrapper);
				activeWrapper = null;

				// Re-enable editing and remove image-editing class
				editorDoc.body.contentEditable = 'true';
				editorDoc.body.classList.remove('image-editing');
			}
		};

		const startResize = (event, handle) => {
			event.preventDefault();
			event.stopPropagation();

			if (!activeWrapper) {
				console.error('No active wrapper during resize start');
				return;
			}

			isResizing = true;
			const startX = event.clientX;
			const startWidth = activeWrapper.offsetWidth;
			const startHeight = activeWrapper.offsetHeight;
			const aspectRatio = startWidth / startHeight;
			const img = activeWrapper.querySelector('img');
			const imageSizeLabel = activeWrapper.querySelector('.image-size');

			imageSizeLabel.style.display = 'block';

			function onMouseMove(moveEvent) {
				if (!isResizing) return;

				moveEvent.preventDefault();
				moveEvent.stopPropagation();

				const deltaX = moveEvent.clientX - startX;
				let newWidth;

				if (handle.classList.contains('br') || handle.classList.contains('tr')) {
					newWidth = Math.max(50, startWidth + deltaX);
				} else {
					newWidth = Math.max(50, startWidth - deltaX);
				}

				const newHeight = newWidth / aspectRatio;

				requestAnimationFrame(() => {
					img.style.width = `${newWidth}px`;
					img.style.height = `${newHeight}px`;
					activeWrapper.style.width = `${newWidth}px`;
					imageSizeLabel.textContent = `${Math.round(newWidth)} x ${Math.round(newHeight)}`;
				});
			}

			function onMouseUp(upEvent) {
				isResizing = false;
				imageSizeLabel.style.display = 'none';

				editorDoc.removeEventListener('mousemove', onMouseMove, true);
				document.removeEventListener('mousemove', onMouseMove, true);
				editorDoc.removeEventListener('mouseup', onMouseUp, true);
				document.removeEventListener('mouseup', onMouseUp, true);

				upEvent.preventDefault();
				upEvent.stopPropagation();
			}

			editorDoc.addEventListener('mousemove', onMouseMove, true);
			document.addEventListener('mousemove', onMouseMove, true);
			editorDoc.addEventListener('mouseup', onMouseUp, true);
			document.addEventListener('mouseup', onMouseUp, true);
		};

		const wrapImage = (img) => {
			// Store current cursor position before wrapping
			const selection = editorDoc.getSelection();
			if (selection.rangeCount > 0) {
				lastRange = selection.getRangeAt(0).cloneRange();
			}

			removeWrapper();

			const width = img.offsetWidth || img.width;
			const height = img.offsetHeight || img.height;

			const wrapper = editorDoc.createElement('div');
			wrapper.className = 'img-wrapper';
			wrapper.style.width = width + 'px';

			const sizeLabel = editorDoc.createElement('div');
			sizeLabel.className = 'image-size';
			sizeLabel.textContent = `${Math.round(width)} x ${Math.round(height)}`;

			img.parentNode.insertBefore(wrapper, img);
			wrapper.appendChild(img);
			wrapper.appendChild(sizeLabel);

			['tl', 'tr', 'bl', 'br'].forEach(pos => {
				const handle = editorDoc.createElement('div');
				handle.className = `img-resize-handle ${pos}`;
				handle.addEventListener('mousedown', function (e) {
					startResize(e, handle);
				}, true);
				wrapper.appendChild(handle);
			});

			const sizeMenu = editorDoc.createElement('div');
			sizeMenu.className = 'size-menu';

			const options = [
				{text: 'Small', handler: () => resizeImage(img, 30)},
				{text: 'Best Fit', handler: () => resizeImage(img, 80)},
				{text: 'Original', handler: () => restoreOriginalSize(img)}
			];

			options.forEach(option => {
				const optionEl = editorDoc.createElement('div');
				optionEl.className = 'size-menu-option';
				optionEl.textContent = option.text;
				optionEl.addEventListener('mousedown', (e) => {
					e.stopPropagation();
					option.handler();
				}, true);
				sizeMenu.appendChild(optionEl);
			});

			wrapper.appendChild(sizeMenu);


			activeWrapper = wrapper;

			// Disable editing completely while wrapper is active
			editorDoc.body.contentEditable = 'false';
			editorDoc.body.classList.add('image-editing');

			// Clear current selection
			const sel = editorDoc.getSelection();
			sel.removeAllRanges();
		};

		// Add these functions:
		const resizeImage = (img, percentage) => {
			const wrapper = img.closest('.img-wrapper');
			if (!wrapper) return;

			const containerWidth = wrapper.parentElement.offsetWidth;
			const newWidth = (containerWidth * percentage) / 100;

			img.style.width = `${newWidth}px`;
			img.style.height = 'auto';
			wrapper.style.width = `${newWidth}px`;

			updateSizeLabel(wrapper);
		};

		const restoreOriginalSize = (img) => {
			const wrapper = img.closest('.img-wrapper');
			if (!wrapper) return;

			img.style.width = '';
			img.style.height = '';
			wrapper.style.width = `${img.naturalWidth}px`;

			updateSizeLabel(wrapper);
		};

		const updateSizeLabel = (wrapper) => {
			const img = wrapper.querySelector('img');
			const sizeLabel = wrapper.querySelector('.image-size');
			if (sizeLabel && img) {
				sizeLabel.textContent = `${Math.round(img.offsetWidth)} x ${Math.round(img.offsetHeight)}`;
			}
		};
		const handleClick = (event) => {
			if (GO.util.isMobileOrTablet()) return;
			// Prevent default browser handling first
			event.preventDefault();
			event.stopPropagation();

			// Don't handle click events during resize
			if (isResizing) {
				return;
			}

			if (event.target.closest('.img-resize-handle')) {
				return; // Let the event propagate to the handle's mousedown listener
			}

			if (event.target.closest('.size-menu-option')) {
				return;
			}

			if (event.target.tagName === 'IMG' && !event.target.closest('.img-wrapper')) {
				// Use setTimeout to ensure the click event is fully processed
				setTimeout(() => {
					wrapImage(event.target);
				}, 0);
				return;
			}

			if (!event.target.closest('.img-wrapper') && activeWrapper) {
				removeWrapper();
			}
		};

		// Use both mousedown and click handlers for Firefox
		editorDoc.addEventListener('mousedown', (event) => {
			if (event.target.tagName === 'IMG' && !event.target.closest('.img-wrapper')) {
				event.preventDefault();
				event.stopPropagation();
			}
		}, true);

		editorDoc.addEventListener('click', handleClick, true);

		// Add mouseup handler to prevent Firefox from removing selection
		editorDoc.addEventListener('mouseup', (event) => {
			if (activeWrapper && !isResizing) {
				event.preventDefault();
				event.stopPropagation();
				const selection = editorDoc.getSelection();
				selection.removeAllRanges();
			}
		}, true);

		// Additional Firefox-specific selection prevention
		editorDoc.addEventListener('selectstart', (event) => {
			if (activeWrapper) {
				event.preventDefault();
				event.stopPropagation();
				return false;
			}
		}, true);

		// Prevent Firefox from handling the image as draggable
		editorDoc.addEventListener('dragstart', (event) => {
			if (event.target.tagName === 'IMG' || event.target.closest('.img-wrapper')) {
				event.preventDefault();
				event.stopPropagation();
				return false;
			}
		}, true);

		// Prevent all keyboard input while wrapper is active
		editorDoc.addEventListener('keydown', (event) => {
			if (activeWrapper) {
				event.preventDefault();
				event.stopPropagation();

				if (event.key === 'Delete' || event.key === 'Backspace') {
					activeWrapper.parentNode.removeChild(activeWrapper);
					activeWrapper = null;

					// Re-enable editing after deletion
					editorDoc.body.contentEditable = 'true';
					editorDoc.body.classList.remove('image-editing');

					// Restore cursor position after deletion
					if (lastRange) {
						const selection = editorDoc.getSelection();
						selection.removeAllRanges();
						selection.addRange(lastRange);
						lastRange = null;
					}
				}
				return false;
			}
		}, true);

		// Handle cases where we might lose the stored range
		this.editor.on('sync', () => {
			lastRange = null;
			removeWrapper();
			activeWrapper = null;

			this.editor.getDoc().body.contentEditable = 'true';
			this.editor.getDoc().body.classList.remove('image-editing');
		});

		this.editor.on('beforesync', () => {
			lastRange = null;
			removeWrapper();
			activeWrapper = null;

			this.editor.getDoc().body.contentEditable = 'true';
			this.editor.getDoc().body.classList.remove('image-editing');
		});

		// Prevent focus/selection events while wrapper is active
		editorDoc.addEventListener('selectionchange', (event) => {
			if (activeWrapper) {
				const selection = editorDoc.getSelection();
				selection.removeAllRanges();
			}
		}, true);

		// Prevent any focus events on the editor while wrapper is active
		editorDoc.addEventListener('focus', (event) => {
			if (activeWrapper) {
				event.preventDefault();
				event.stopPropagation();
			}
		}, true);

	}
});

