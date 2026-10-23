import {client} from "@intermesh/groupoffice-core";

interface ViewableFile {
	blobId: string
	type?: string
	name?: string
}

const isViewable = (type?: string) => !!type && (/^(text|image)\//.test(type) || type === 'application/pdf');

/**
 * Minimal stand-in for the fileView of groupoffice-core, which is not published yet.
 * Same call signature: fileView.open(file).show(). Viewable types open in a new tab, others are downloaded.
 */
export const fileView = {
	open(file: ViewableFile) {
		return {
			show() {
				if (!isViewable(file.type)) {
					void client.downloadBlobId(file.blobId, file.name ?? 'download');
					return;
				}
				// open the tab synchronously so the popup blocker allows it
				const win = window.open('', '_blank');
				client.getBlobURL(file.blobId).then(url => {
					if (win) {
						win.location.href = url;
					} else {
						window.open(url, '_blank');
					}
				});
			}
		};
	}
};
