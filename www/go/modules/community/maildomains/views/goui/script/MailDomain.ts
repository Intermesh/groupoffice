import {BaseEntity, DefaultEntity} from "@intermesh/goui";

export interface MailDomain extends BaseEntity {
	domain:string
	description: string
	active:boolean
	mxStatus:boolean
	dmarcStatus:boolean
	spfStatus:boolean
	dkim:any,
	defaultQuota:number
	maxMailboxes:number,
	maxAliases:number
}


export function mailDomainStatus(record:MailDomain) {
	if(!record.active) {
		return `<i class="icon disabled">unpublished</i>`;
	}

	let allIsWell = record.mxStatus && record.dmarcStatus && record.spfStatus;

	for (const selector in record.dkim) {
		if(!record.dkim[selector].enabled) {
			continue;
		}
		allIsWell = allIsWell && record.dkim[selector].status;
	}
	let iconCls = "icon";
	if (!allIsWell) {
		iconCls += " danger";
	} else {
		iconCls += " success";
	}

	return `<i class="${iconCls}">${(allIsWell ? "check_circle" : "warning")}</i>`;
}