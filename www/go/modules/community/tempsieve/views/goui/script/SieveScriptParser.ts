import {
	SieveRuleEntity,
	SieveScriptEntity
} from "@intermesh/community/tempsieve";
import {client, jmapds} from "@intermesh/groupoffice-core";

/**
 * @see https://www.rfc-editor.org/rfc/rfc5228
 */
export class SieveScriptParser {
	public script: SieveScriptEntity;
	public requirements: string[];
	public rules: SieveRuleEntity[];
	public oooRule: SieveRuleEntity | undefined;
	private rawScript: string = "";

	public supported = [     // Sieve extensions supported by class
		'body',                     // RFC5173
		'copy',                     // RFC3894
		'date',                     // RFC5260
		'enotify',                  // RFC5435
		'envelope',                 // RFC5228
		'ereject',                  // RFC5429
		'fileinto',                 // RFC5228
		'imapflags',                // draft-melnikov-sieve-imapflags-06
		'imap4flags',               // RFC5232
		'include',                  // draft-ietf-sieve-include-12
		'index',                    // RFC5260
		'notify',                   // draft-martin-sieve-notify-01,
		'regex',                    // draft-ietf-sieve-regex-01
		'reject',                   // RFC5429
		'relational',               // RFC3431
		'subaddress',               // RFC5233
		'vacation',                 // RFC5230
		'vacation-seconds',         // RFC6131
		'variables',                // RFC5229
		'mailbox'                   // RFC5490
	];

	constructor(s: SieveScriptEntity) {
		this.script = s;
		if (s.script) {
			this.rawScript = s.script;
		} else {
			const blobId = s.blobId!;
			// TODO: retrieve script contents from blobId using the client
			client.downloadBlobId(blobId, s.name!).then((result) => {
				debugger;
			});
		}
		this.requirements = this.parseRequirements();
		this.rules = this.parseRules();
	}

	/**
	 * Return an array of required Sieve extensions
	 * @private
	 * @example require ["fileinto", "reject"];
	 *          require "fileinto";
	 *          require "vacation";
	 *  ["fileinto", "reject", "fileinto", "vacation"]
	 */
	private parseRequirements() {
		let ret: string[] = [];
		// const lines = this.script.script.split("\n");
		const lines = this.rawScript.split("\n");
		for (const line of lines) {
			// Require lines are always at the beginning of the script, so it is safe to stop parsing if the line does not start with require anymore
			if (!line.startsWith("require")) {
				break;
			}
			let l = line.substring(8).replace(/[\[];"]/gi, "");
			ret = ret.concat(l.split(", "));
		}
		return ret;
	}

	/**
	 * Parse the raw script into rules that are both listable in a table, but also have the individual rules in raw format
	 *
	 * @private
	 */
	private parseRules(): SieveRuleEntity[] {
		let ret: SieveRuleEntity[] = [];
		let idx = 0;
		const lines = this.rawScript.split("\n");
		for (let i = 0, l = lines.length; i < l; i++) {
			const line = lines[i];
			if (line.startsWith("# rule")) {
				const r: any = {};
				r.idx = idx;
				r.name = line.substring(8, line.length - 1);
				r.scriptName = this.script.name;
				let active = false;
				let arRaw = [];
				arRaw.push(line);
				let j = i + 1;
				while (j < l) {
					const currLine = lines[j];
					arRaw.push(currLine);
					const words = currLine.split(" ");
					if (words[0] == "if") {
						active = words[1] !== "false";
					} else if (words[0] === "}") {
						i = j;
						break;
					}
					j++;
				}
				r.raw = arRaw.join("\n");
				r.active = active;
				if(r.name !== "Out of office") {
					ret.push(r);
					idx++;
				} else {
					this.oooRule = r;
				}
			}
		}

		return ret;
	}

}