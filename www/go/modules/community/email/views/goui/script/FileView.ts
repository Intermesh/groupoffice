import {client} from "@intermesh/groupoffice-core";

interface ViewableFile {
	blobId: string
	type?: string
	name?: string
}

// Only types that can't run script: an object URL shares our origin, so text/html or image/svg+xml would be XSS
const VIEWABLE = new Set(['text/plain', 'image/png', 'image/jpeg', 'image/gif', 'image/webp', 'application/pdf']);
const isViewable = (type?: string) => !!type && VIEWABLE.has(type.toLowerCase().split(';')[0].trim());

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
				if (win) {
					win.opener = null;
				}
				const type = file.type!.toLowerCase().split(';')[0].trim();
				client.getBlobURL(file.blobId)
					.then(url => fetch(url))
					.then(r => r.blob())
					// rewrap: the served Content-Type could differ from the declared, allowed type
					.then(blob => URL.createObjectURL(new Blob([blob], {type})))
					.then(url => {
					if (win) {
						win.location.href = url;
					} else {
						window.open(url, '_blank', 'noopener');
					}
				});
			}
		};
	}
};
