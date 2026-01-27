document.addEventListener('DOMContentLoaded', async () => {
	while (!window.PDFViewerApplication) {
		await new Promise(resolve => setTimeout(resolve, 100));
	}

	await window.PDFViewerApplication.initializedPromise;

	const saveToGroupOfficeButton = document.getElementById("saveToGroupOfficeButton");

	saveToGroupOfficeButton.addEventListener('click', async () => {
		try {
			saveToGroupOfficeButton.disabled = true;
			saveToGroupOfficeButton.textContent = 'Saving...';

			const data = await window.PDFViewerApplication.pdfDocument.saveDocument();
			const pdf = new Blob([data], {type: "application/pdf"});

			const urlParams = new URLSearchParams(window.location.search);
			const fileId = urlParams.get('fileId');
			const csrfToken = urlParams.get('CSRFToken');

			const formData = new FormData();
			formData.append('pdf', pdf);
			formData.append('fileId', fileId);

			const response = await fetch('/go/modules/community/pdfeditor/api/Upload.php', {
				method: 'POST',
				headers: {
					'X-CSRF-Token': csrfToken
				},
				body: formData
			});

			if (!response.ok) {
				const error = await response.json();
				throw new Error(error.error || 'Save failed');
			}

			showSuccessIndicator(saveToGroupOfficeButton);


			const app = PDFViewerApplication;

// 1. Exit editor mode FIRST
			app.eventBus.dispatch("switchannotationeditormode", {
				source: app,
				mode: 0, // NONE
			});


			app.pdfDocument.annotationStorage?.resetModified();
			app._hasAnnotationEditors = false;
			app.setTitle();

// 4. Force UI sync
			app.eventBus.dispatch("documentmodified", {
				source: app,
				modified: false,
			});



		} catch (error) {
			console.error('Save error:', error);
			showErrorIndicator(saveToGroupOfficeButton, error.message);
		} finally {
			saveToGroupOfficeButton.disabled = false;
			saveToGroupOfficeButton.textContent = 'Save to Group-Office';
		}
	});
});

function showSuccessIndicator(button) {
	const existing = button.parentElement.querySelector('.save-indicator');
	if (existing) existing.remove();

	const checkmark = document.createElement('div');
	checkmark.className = 'save-indicator';
	checkmark.innerHTML = '✓ Saved successfully';
	checkmark.style.cssText = `
          position: absolute;
          top: calc(100% + 8px);
          left: 50%;
          transform: translateX(-50%);
          background: #4CAF50;
          color: white;
          padding: 8px 16px;
          border-radius: 4px;
          font-size: 13px;
          font-weight: 500;
          box-shadow: 0 2px 8px rgba(0,0,0,0.2);
          animation: fadeInDown 0.3s ease-out;
          white-space: nowrap;
          z-index: 1000;
        `;

	button.parentElement.style.position = 'relative';
	button.parentElement.appendChild(checkmark);

	setTimeout(() => {
		checkmark.style.animation = 'fadeOutUp 0.3s ease-in';
		setTimeout(() => checkmark.remove(), 300);
	}, 3000);
}

function showErrorIndicator(button, message) {
	const existing = button.parentElement.querySelector('.save-indicator');
	if (existing) existing.remove();

	const errorMsg = document.createElement('div');
	errorMsg.className = 'save-indicator';
	errorMsg.innerHTML = '✗ ' + (message || 'Failed to save');
	errorMsg.style.cssText = `
          position: absolute;
          top: calc(100% + 8px);
          left: 50%;
          transform: translateX(-50%);
          background: #f44336;
          color: white;
          padding: 8px 16px;
          border-radius: 4px;
          font-size: 13px;
          font-weight: 500;
          box-shadow: 0 2px 8px rgba(0,0,0,0.2);
          animation: fadeInDown 0.3s ease-out;
          white-space: nowrap;
          text-align: center;
          z-index: 1000;
        `;

	button.parentElement.style.position = 'relative';
	button.parentElement.appendChild(errorMsg);


	setTimeout(() => {
		errorMsg.style.animation = 'fadeOutUp 0.3s ease-in';
		setTimeout(() => errorMsg.remove(), 300);
	}, 5000);
}

const style = document.createElement('style');
style.textContent = `
        @keyframes fadeInDown {
          from {
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
          }
          to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
          }
        }

        @keyframes fadeOutUp {
          from {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
          }
          to {
            opacity: 0;
            transform: translateX(-50%) translateY(-10px);
          }
        }
      `;
document.head.appendChild(style);