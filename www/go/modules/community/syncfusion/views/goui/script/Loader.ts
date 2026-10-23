import {t} from "@intermesh/goui";
import {modules} from "@intermesh/groupoffice-core";

/**
 * Syncfusion library loader.
 *
 * Loads the Syncfusion ej2 bundle once into the page, from either the CDN or the module's lib/ directory.
 */

export const DEFAULT_CDN_URL = "https://cdn.syncfusion.com/ej2/32.1.19/";

export interface SyncfusionSettings {
	licenseKey?: string,
	librarySource?: "cdn" | "local",
	cdnUrl?: string,
	documentServiceUrl?: string,
	spreadsheetServiceUrl?: string
}

export function getSettings(): SyncfusionSettings {
	return modules.get("community", "syncfusion")?.settings ?? {};
}

/**
 * CDN base URL with a trailing slash
 */
export function getCdnBase(): string {
	return (getSettings().cdnUrl || DEFAULT_CDN_URL).replace(/\/?$/, "/");
}

/**
 * Version from the CDN URL, used to fetch the matching locale package
 */
export function getCdnVersion(): string {
	const match = getCdnBase().match(/\/ej2\/([^\/]+)\//);
	return match ? match[1] : "32.1.19";
}

/**
 * URLs of the ej2 script and stylesheet for the configured library source
 */
export function getLibraryUrls(): {js: string, css: string} {
	if (getSettings().librarySource === "local") {
		const localBase = BaseHref + "go/modules/community/syncfusion/lib/";
		return {js: localBase + "ej2.min.js", css: localBase + "ej2.min.css"};
	}
	const cdnBase = getCdnBase();
	return {js: cdnBase + "dist/ej2.min.js", css: cdnBase + "material.css"};
}

/**
 * Register the license key with an ej2 instance (the page's or an iframe's)
 */
export function registerLicense(ej: any) {
	const key = getSettings().licenseKey;
	if (key && ej?.base?.registerLicense) {
		ej.base.registerLicense(key);
	}
}

let loading: Promise<any> | undefined;

/**
 * Load the Syncfusion library into the page
 *
 * Resolves with the global `ej` namespace. A failed load is not cached, so the next call tries again.
 */
export function loadLibrary(): Promise<any> {
	if (!loading) {
		loading = new Promise((resolve, reject) => {
			const urls = getLibraryUrls();

			const link = document.createElement("link");
			link.rel = "stylesheet";
			link.href = urls.css;
			document.head.appendChild(link);

			// A plain script tag, so a failed CDN load rejects instead of leaving callers waiting
			const script = document.createElement("script");
			script.src = urls.js;
			script.onload = () => {
				const ej = (window as any).ej;
				registerLicense(ej);
				resolve(ej);
			};
			script.onerror = () => {
				loading = undefined;
				link.remove();
				script.remove();
				console.error("Failed to load " + script.src);
				reject(new Error(t("Failed to load the Syncfusion library.", "community", "syncfusion")));
			};
			document.head.appendChild(script);
		});
	}
	return loading;
}
