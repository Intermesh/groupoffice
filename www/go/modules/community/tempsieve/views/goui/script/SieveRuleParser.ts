import {SieveActionEntity, SieveCriteriumEntity, SieveRuleEntity} from "@intermesh/community/tempsieve";
import {t} from "@intermesh/goui";

export class SieveRuleParser {

	private _record: SieveRuleEntity;
	private _tests: SieveCriteriumEntity[] = [];
	private _actions: SieveActionEntity[] = [];
	private _rawScript: string;

	constructor(record: SieveRuleEntity) {
		this._record = record;
		this._rawScript = record.raw;
	}

	/**
	 * Parse all criteria / tests from the currently loaded sieve script
	 */
	public parseTests(): void {
		this._record.join = "anyof";
		const lines = this._record.raw.split("\n");
		for (const line of lines) {
			if (line.startsWith("if")) {
				this.parseCriterium(line);
			}
		}
		if (this._tests.length === 0) {
			this._record.join = "any";
		}
	}

	/**
	 * Parse individual criterium lines.
	 * @param line
	 */
	private parseCriterium(line: string): SieveCriteriumEntity {
		const words = line.split(" ");
		const test = words[0] === "if" ? words[1] : words[0];
		let startIdx = words[0] === "if" ? 2 : 1;
		let crit: SieveCriteriumEntity = {not: false, test: "true"};
		let newLine;
		switch (test) {
			case "address":
				// TODO? Not supported in old sieve module?
				break;
			case "allof":
			case "anyof":
				this._record.join = test;
				const rawSubCrits = words.slice(startIdx).join(" ").replace(/[()]/g, "");
				for (const subCritLine of rawSubCrits.split(", ")) {
					this.parseCriterium(subCritLine);
				}

				break;
			case "envelope":
				// TODO? Not supported in old sieve module?
				break;
			case "date":
				// TODO? Not supported in old sieve module. Should it be supported as per RFC-5260?
				break;
			case "currentdate":
				/**
				 * @see https://www.rfc-editor.org/rfc/rfc5260
				 */
				crit.test = test;
				const comparator = words[startIdx].replace(":", "");
				if (comparator === "is") {
					crit.type = comparator;
				} else {
					startIdx++;
					crit.type = (comparator + "-" + words[startIdx]).replace(/"/g, '');
				}
				startIdx++;
				crit.part = words[startIdx].replace(/"/g, '');
				startIdx++;
				crit.arg = words[startIdx].replace(/"/g, '');

				this._tests.push(crit);
				break;
			case "exists":
				crit.test = test;
				crit.arg = words[startIdx].replace(/"/g, "");
				this._tests.push(crit);
				break;
			case "false":
				// The rule is NOT active. The original criteria are parsed from word number three
				startIdx++;
				newLine = words.slice(startIdx);
				this.parseCriterium(newLine.join(" "));
				break;
			case "not":
				newLine = words.slice(startIdx);
				crit = this.parseCriterium(newLine.join(" "));
				crit.not = true;
				break;
			case "header":
				crit.test = test;
				crit.type = words[startIdx].replace(":", ""); // TODO: support multiple comparators
				startIdx++;
				crit.arg1 = words[startIdx].replace(/"/g, "");
				startIdx++;
				crit.arg2 = words.slice(startIdx).join(" ").replace(/"/g, "");
				this._tests.push(crit);
				break;
			case "body":
				/**
				 * @see RFC5173
				 */
				crit.test = test;
				crit.part = words[startIdx].replace(":", ""); // CH 5: Body transform row|content|text
				startIdx++
				crit.type = words[startIdx].replace(":", "");
				startIdx++;
				crit.arg = words.slice(startIdx).join(" ").replace(/"/g, "");
				this._tests.push(crit);
				break;
			case "size":
				crit.test = test;
				crit.type = words[startIdx].replace(":", "");
				startIdx++;
				crit.arg = words[startIdx];
				this._tests.push(crit);
				break;
			case "true":
			default:
				this._tests.push(crit);
		}
		return crit;
	}

	/**
	 * Parse all action for current script
	 */
	public parseActions(): void {
		this._actions = [];
		const paramsPattern = /[^{}]+(?=})/g;
		const rawActions = this._record.raw.match(paramsPattern);
		for (const m of rawActions || []) {
			const lines = m.trim().split(";\n");
			for (let line of lines) {
				if (line.endsWith(";")) {
					line = line.slice(0, -1);
				}
				this.parseAction(line.trim());
			}
		}
	}

	/**
	 * Parse an individual action line
	 *
	 * @param line
	 * @private
	 */
	private parseAction(line: string) {
		const words = line.split(" "), type = words[0];
		const action: SieveActionEntity = {
			type: type,
			text: t(type, "community", "tempsieve")
		}

		switch (type) {
			case "fileinto":
				action.copy = false;
				let folderIdx = 1;
				if (words[1] === ":copy") {
					action.type = "fileinto_copy";
					action.copy = true;
					folderIdx = 2;
				}
				const folderName = words[folderIdx].replace(/"/g, "");
				action.target = folderName;
				const fileintoType = action.copy ? "Copy" : "Move";
				action.text = t(`${fileintoType} email to the folder ${folderName}`, "community", "sieve");

				break;
			case "redirect":
				action.copy = false;
				let tgtIndex = 1;
				let actionTxt = "Redirect to"
				if (words[1] === ":copy") {
					actionTxt = "Send a copy to"
					action.type = "redirect_copy";
					action.copy = true;
					tgtIndex++;

				}
				action.target = words[tgtIndex].replace(/"/g, "");

				action.text = t(`${actionTxt} "${action.target}"`, "community", "tempsieve");
				break;
			case "addflag":
				const tgt = words[1].replace(/"/g, "");
				action.target = tgt;
				// Wake me up if there are any other addflag use cases...
				// if (tgt.endsWith("Seen")) {
				// action.type = "set_read";
				action.text = t("Mark message as read", "community", "tempsieve");
				// } else {
				// ???
				// }
				break;
			case "vacation":
				action.text = t("Reply to message");
				action.days = words[2];
				action.addresses = words[4].replace(/[\[\]"]/g, '');
				let rest = words.slice(6).join(" ");
				const quotesPattern = /"(\\.|[^"\\])*"/g;
				const matches = rest.match(quotesPattern);
				let subject = "", reason = "";
				if (matches) {
					subject = matches[0].replace(/"/g, "");
					reason = matches[1].replace(/"/g, "");
				}
				action.subject = subject;
				action.reason = reason;
				action.text = `Reply every ${action.days} days. Autoreply is active for: ${action.addresses}. Message: "${action.reason}"`;
				break;
			case "reject":
				action.target = words.slice(1).join(" ").replace(/"/g, "");
				action.text = `${t("Reject with message")} "${action.target}"`;
				break;
			default:
				break;
		}
		this._actions.push(action);

	}

	/**
	 * Generate a raw sieve script from the sieve rule window, its tests and in- actions
	 *
	 * @return void
	 * @param tests
	 * @param actions
	 */
	public convert(tests: SieveCriteriumEntity[], actions: SieveActionEntity[]): void {
		const oldRaw = this._rawScript;
		let lines = [`# rule:[${this._record.name}]`];

		let ifStr = "if " + (!this._record.active ? "false # " : "");
		if (tests.length > 0) {
			const arCond = [];
			ifStr += this._record.join + " ";
			for (const crit of tests) {
				arCond.push(this.convertCriterium(crit));
			}
			ifStr += "(" + arCond.join(", ") + ")";
		} else {
			ifStr += "true"
		}
		lines.push(ifStr);
		lines.push("{");
		for (const actn of actions) {
			lines.push(this.convertAction(actn));
		}
		lines.push("}");
		this._rawScript = lines.join("\n");
	}

	/**
	 * Convert a criterium to a raw snippet of Sieve script
	 *
	 * @param crit
	 * @private
	 * @todo: custom rules?
	 */

	private convertCriterium(crit: SieveCriteriumEntity): string {
		let r = "";
		switch (crit.test) {
			case "body":
				// not body :text :contains "meow"
				if (crit.not) {
					r += "not ";
				}
				r += crit.test;
				if (crit.part) {
					r += ` :${crit.part}`;
				}
				if (crit.type) {
					r += ` :${crit.type}`;
				}
				r += ` "${crit.arg}"`;
				break;
			case "currentdate":
				// currentdate :value "ge" "date" "2025-12-01"
				const arType = crit.type!.split("-");
				r += `${crit.test} :${arType[0]} "${arType[1]}" "${crit.part}" "${crit.arg}"`;
				break;
			case "exists":
				// exists "List-Unsubscribe"
				r += `${crit.test} "${crit.arg}"`;
				break;
			case "header":
				// header :contains "X-Spam-Flag" "YES"
				r += `${crit.test} :${crit.type} "${crit.arg1}" "${crit.arg2}"`;
				break;
			case "size":
				// size :over 25M
				r += `${crit.test} :${crit.type} ${crit.arg}`;
				break;
			default:
				r += "TODO";
				break;
		}
		return r;
	}

	/**
	 * Convert a Sieve action into a raw snippet of Sieve script
	 * @param actn
	 * @private
	 */
	private convertAction(actn: SieveActionEntity): string {
		let r = "\t";
		switch (actn.type) {
			case "addflag":
				// addflag "\\Seen"
				r += `${actn.type} "\\${actn.target}"`;
				break;
			case "fileinto":
			case "fileinto_copy":
				// fileinto :copy "Spam"
				r += "fileinto";
				if (actn.copy) {
					r += " :copy";
				}
				r += ` "${actn.target}"`;
				break;
			case "redirect":
			case "redirect_copy":
				// redirect :copy "info@examplo.com"
				r += "redirect";
				if (actn.copy) {
					r += " :copy";
				}
				r += ` "${actn.target}"`;
				break;
			case "reject":
				// reject "Piss off"
				r += `${actn.type} "${actn.target}"`;
				break;
			case "vacation":
				// vacation :days 3 :addresses "admin@intermesh.localhost" :subject "Sayonara, bitches!" "I am on vacation";
				r += `${actn.type} :days ${actn.days} :addresses "${actn.addresses}" :subject "${actn.subject}" "${actn.reason}"`;
				break;
			default:
				// stop
				// discard
				r += actn.type;
				break;
		}
		r += ";"
		return r;
	}

	/**
	 * public getter for all criteria
	 */
	get tests(): SieveCriteriumEntity[] {
		return this._tests;
	}

	/**
	 * Public getter for all actions
	 */
	get actions(): SieveActionEntity[] {
		return this._actions;
	}

	get raw(): string {
		return this._rawScript;
	}
}