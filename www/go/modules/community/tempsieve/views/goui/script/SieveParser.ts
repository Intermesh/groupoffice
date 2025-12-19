import {SieveRuleEntity, SieveScriptEntity} from "@intermesh/community/tempsieve";

/**
 * @see https://www.rfc-editor.org/rfc/rfc5228
 */
export class SieveParser {
	public script: SieveScriptEntity;
	public requirements: string[];
	public rules: SieveRuleEntity[];

	constructor(s: SieveScriptEntity) {
		this.script = s;
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
		const lines = this.script.script.split("\n");
		for (const line of lines) {
			// Require lines are always at the beginning of the script, so it is safe to stop parsing if the line does not start with require anymore
			if (!line.startsWith("require")) {
				break;
			}
			let l = line.substring(8).replaceAll(/[\[];"]/gi, "");
			ret = ret + l.split(", ");
		}
		return ret;
	}

	/**
	 * Parse the raw script into rules that are both listable in a table, but also have the individual rules in raw format
	 *
	 * @private
	 */
	private parseRules():SieveRuleEntity[] {
		let ret:SieveRuleEntity[] = [];
		let idx = 0;
		const lines = this.script.script.split("\n");
		for (let i=0,l = lines.length; i<l; i++) {
			const line = lines[i];
			if(line.startsWith("# rule")) {
				const r:any  = {};
				r.idx = idx;
				r.name = line.substring(8, line.length-1);
				r.scriptName = this.script.name;
				let active = false;
				let arRaw = [];
				arRaw.push(line);
				let j = i + 1;
				while(j < l) {
					const currLine = lines[j];
					arRaw.push(currLine);
					const words = currLine.split(" ");
					if(words[0] == "if") {
						active = words[1] !== "false";
					} else if (words[0] === "}") {
						i = j;
						break;
					}
					j++;
				}
				r.raw = arRaw.join("\n");
				r.active = active;

				ret.push(r);
				idx++;
				console.log(r);
			}
		}

		return ret;
	}


}