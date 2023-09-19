var GO = GO || {};
/*
This file is part of Ext JS 3.4

Copyright (c) 2011-2013 Sencha Inc

Contact:  http://www.sencha.com/contact

GNU General Public License Usage
This file may be used under the terms of the GNU General Public License version 3.0 as
published by the Free Software Foundation and appearing in the file LICENSE included in the
packaging of this file.

Please review the following information to ensure the GNU General Public License version 3.0
requirements will be met: http://www.gnu.org/copyleft/gpl.html.

If you are unsure which license is appropriate for your use, please contact the sales department
at http://www.sencha.com/contact.

Build date: 2013-04-03 15:07:25
*/
/**
 * List compiled by mystix on the extjs.com forums.
 * Thank you Mystix!
 *
 * English Translations
 * updated to 2.2 by Condor (8 Aug 2008)
 */

Ext.UpdateManager.defaults.indicatorText = '<div class="loading-indicator">Loading...</div>';

if(Ext.data.Types){
    Ext.data.Types.stripRe = /[\$,%]/g;
}

if(Ext.DataView){
  Ext.DataView.prototype.emptyText = "";
}

if(Ext.grid.GridPanel){
  Ext.grid.GridPanel.prototype.ddText = "{0} selected row{1}";
}

if(Ext.LoadMask){
  Ext.LoadMask.prototype.msg = "Loading...";
}

Date.monthNames = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December"
];

Date.getShortMonthName = function(month) {
  return Date.monthNames[month].substring(0, 3);
};

Date.monthNumbers = {
  Jan : 0,
  Feb : 1,
  Mar : 2,
  Apr : 3,
  May : 4,
  Jun : 5,
  Jul : 6,
  Aug : 7,
  Sep : 8,
  Oct : 9,
  Nov : 10,
  Dec : 11
};

Date.getMonthNumber = function(name) {
  return Date.monthNumbers[name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()];
};

Date.dayNames = [
  "Sunday",
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday"
];

Date.getShortDayName = function(day) {
  return Date.dayNames[day].substring(0, 3);
};

Date.parseCodes.S.s = "(?:st|nd|rd|th)";

if(Ext.MessageBox){
  Ext.MessageBox.buttonText = {
    ok     : "OK",
    cancel : "Cancel",
    yes    : "Yes",
    no     : "No"
  };
}

if(Ext.util.Format){
  Ext.util.Format.date = function(v, format){
    if(!v) return "";
    if(!(v instanceof Date)) v = new Date(Date.parse(v));
    return v.dateFormat(format || "m/d/Y");
  };
}

if(Ext.DatePicker){
  Ext.apply(Ext.DatePicker.prototype, {
    todayText         : "Today",
    minText           : "This date is before the minimum date",
    maxText           : "This date is after the maximum date",
    disabledDaysText  : "",
    disabledDatesText : "",
    monthNames        : Date.monthNames,
    dayNames          : Date.dayNames,
    nextText          : 'Next Month (Control+Right)',
    prevText          : 'Previous Month (Control+Left)',
    monthYearText     : 'Choose a month (Control+Up/Down to move years)',
    todayTip          : "{0} (Spacebar)",
    format            : "m/d/y",
    okText            : "&#160;OK&#160;",
    cancelText        : "Cancel",
    startDay          : 0
  });
}

if(Ext.PagingToolbar){
  Ext.apply(Ext.PagingToolbar.prototype, {
    beforePageText : "Page",
    afterPageText  : "of {0}",
    firstText      : "First Page",
    prevText       : "Previous Page",
    nextText       : "Next Page",
    lastText       : "Last Page",
    refreshText    : "Refresh",
    displayMsg     : "Displaying {0} - {1} of {2}",
    emptyMsg       : 'No data to display'
  });
}

if(Ext.form.BasicForm){
    Ext.form.BasicForm.prototype.waitTitle = "Please Wait..."
}

if(Ext.form.Field){
  Ext.form.Field.prototype.invalidText = "The value in this field is invalid";
}

if(Ext.form.TextField){
  Ext.apply(Ext.form.TextField.prototype, {
    minLengthText : "The minimum length for this field is {0}",
    maxLengthText : "The maximum length for this field is {0}",
    blankText     : "This field is required",
    regexText     : "",
    emptyText     : null
  });
}

if(Ext.form.NumberField){
  Ext.apply(Ext.form.NumberField.prototype, {
    decimalSeparator : ".",
    decimalPrecision : 2,
    minText : "The minimum value for this field is {0}",
    maxText : "The maximum value for this field is {0}",
    nanText : "{0} is not a valid number"
  });
}

if(Ext.form.DateField){
  Ext.apply(Ext.form.DateField.prototype, {
    disabledDaysText  : "Disabled",
    disabledDatesText : "Disabled",
    minText           : "The date in this field must be after {0}",
    maxText           : "The date in this field must be before {0}",
    invalidText       : "{0} is not a valid date - it must be in the format {1}",
    format            : "m/d/y",
    altFormats        : "m/d/Y|m-d-y|m-d-Y|m/d|m-d|md|mdy|mdY|d|Y-m-d",
    startDay          : 0
  });
}

if(Ext.form.ComboBox){
  Ext.apply(Ext.form.ComboBox.prototype, {
    loadingText       : "Loading...",
    valueNotFoundText : undefined
  });
}

if(Ext.form.VTypes){
  Ext.apply(Ext.form.VTypes, {
    emailText    : 'This field should be an e-mail address in the format "user@example.com"',
    urlText      : 'This field should be a URL in the format "http:/'+'/www.example.com"',
    alphaText    : 'This field should only contain letters and _',
    alphanumText : 'This field should only contain letters, numbers and _'
  });
}

if(Ext.form.HtmlEditor){
  Ext.apply(Ext.form.HtmlEditor.prototype, {
    createLinkText : 'Please enter the URL for the link:',
    buttonTips : {
      bold : {
        title: 'Bold (Ctrl+B)',
        text: 'Make the selected text bold.',
        cls: 'x-html-editor-tip'
      },
      italic : {
        title: 'Italic (Ctrl+I)',
        text: 'Make the selected text italic.',
        cls: 'x-html-editor-tip'
      },
      underline : {
        title: 'Underline (Ctrl+U)',
        text: 'Underline the selected text.',
        cls: 'x-html-editor-tip'
      },
      increasefontsize : {
        title: 'Grow Text',
        text: 'Increase the font size.',
        cls: 'x-html-editor-tip'
      },
      decreasefontsize : {
        title: 'Shrink Text',
        text: 'Decrease the font size.',
        cls: 'x-html-editor-tip'
      },
      backcolor : {
        title: 'Text Highlight Color',
        text: 'Change the background color of the selected text.',
        cls: 'x-html-editor-tip'
      },
      forecolor : {
        title: 'Font Color',
        text: 'Change the color of the selected text.',
        cls: 'x-html-editor-tip'
      },
      justifyleft : {
        title: 'Align Text Left',
        text: 'Align text to the left.',
        cls: 'x-html-editor-tip'
      },
      justifycenter : {
        title: 'Center Text',
        text: 'Center text in the editor.',
        cls: 'x-html-editor-tip'
      },
      justifyright : {
        title: 'Align Text Right',
        text: 'Align text to the right.',
        cls: 'x-html-editor-tip'
      },
      insertunorderedlist : {
        title: 'Bullet List',
        text: 'Start a bulleted list.',
        cls: 'x-html-editor-tip'
      },
      insertorderedlist : {
        title: 'Numbered List',
        text: 'Start a numbered list.',
        cls: 'x-html-editor-tip'
      },
      createlink : {
        title: 'Hyperlink',
        text: 'Make the selected text a hyperlink.',
        cls: 'x-html-editor-tip'
      },
      sourceedit : {
        title: 'Source Edit',
        text: 'Switch to source editing mode.',
        cls: 'x-html-editor-tip'
      }
    }
  });
}

if(Ext.grid.GridView){
  Ext.apply(Ext.grid.GridView.prototype, {
    sortAscText  : "Sort Ascending",
    sortDescText : "Sort Descending",
    columnsText  : "Columns"
  });
}

if(Ext.grid.GroupingView){
  Ext.apply(Ext.grid.GroupingView.prototype, {
    emptyGroupText : '(None)',
    groupByText    : 'Group By This Field',
    showGroupsText : 'Show in Groups'
  });
}

if(Ext.grid.PropertyColumnModel){
  Ext.apply(Ext.grid.PropertyColumnModel.prototype, {
    nameText   : "Name",
    valueText  : "Value",
    dateFormat : "m/j/Y",
    trueText: "true",
    falseText: "false"
  });
}

if(Ext.grid.BooleanColumn){
   Ext.apply(Ext.grid.BooleanColumn.prototype, {
      trueText  : "true",
      falseText : "false",
      undefinedText: '&#160;'
   });
}

if(Ext.grid.NumberColumn){
    Ext.apply(Ext.grid.NumberColumn.prototype, {
        format : '0,000.00'
    });
}

if(Ext.grid.DateColumn){
    Ext.apply(Ext.grid.DateColumn.prototype, {
        format : 'm/d/Y'
    });
}

if(Ext.layout.BorderLayout && Ext.layout.BorderLayout.SplitRegion){
  Ext.apply(Ext.layout.BorderLayout.SplitRegion.prototype, {
    splitTip            : "Drag to resize.",
    collapsibleSplitTip : "Drag to resize. Double click to hide."
  });
}

if(Ext.form.TimeField){
  Ext.apply(Ext.form.TimeField.prototype, {
    minText : "The time in this field must be equal to or after {0}",
    maxText : "The time in this field must be equal to or before {0}",
    invalidText : "{0} is not a valid time",
    format : "g:i A",
    altFormats : "g:ia|g:iA|g:i a|g:i A|h:i|g:i|H:i|ga|ha|gA|h a|g a|g A|gi|hi|gia|hia|g|H"
  });
}

if(Ext.form.CheckboxGroup){
  Ext.apply(Ext.form.CheckboxGroup.prototype, {
    blankText : "You must select at least one item in this group"
  });
}

if(Ext.form.RadioGroup){
  Ext.apply(Ext.form.RadioGroup.prototype, {
    blankText : "You must select one item in this group"
  });
}
GO.Languages=[];
GO.Languages.push(["ar","عربي (Arabic)"]);
GO.Languages.push(["bn_bd","বাংলা (Bangladesh)"]);
GO.Languages.push(["ko","한국어"]);
GO.Languages.push(["ca","Català"]);
GO.Languages.push(["cn","Chinese Simplified"]);
GO.Languages.push(["zh_tw","Chinese Traditional"]);
GO.Languages.push(["cs","Čeština"]);
GO.Languages.push(["da","Dansk"]);
GO.Languages.push(["de","Deutsch"]);
GO.Languages.push(["de_at","Deutsch / Österreich"]);
GO.Languages.push(["de_ch","Deutsch / Schweizerische Eidgenossenschaft"]);
GO.Languages.push(["en","English / America"]);
GO.Languages.push(["en_au","English / Australia"]);
GO.Languages.push(["en_ph","English / Philippines"]);
GO.Languages.push(["en_uk","English / United Kingdom"]);
GO.Languages.push(["es","Español"]);
GO.Languages.push(["et","Estonian"]);
GO.Languages.push(["el","Ελληνικά"]);
GO.Languages.push(["fr","Francais"]);
GO.Languages.push(["he","(Hebrew) עִבְרִית"]);
GO.Languages.push(["hr","Hrvatski"]);
GO.Languages.push(["it","Italiano"]);
GO.Languages.push(["id","Bahasa Indonesia"]);
GO.Languages.push(["ja","Japanese"]);
GO.Languages.push(["hu","Magyar"]);
GO.Languages.push(["mn","Монгол хэл"]);
GO.Languages.push(["nl","Nederlands"]);
GO.Languages.push(["nb","Norsk bokmål"]);
GO.Languages.push(["pl","Polski"]);
GO.Languages.push(["pt_pt","Português"]);
GO.Languages.push(["pt_br","Português - Brazil"]);
GO.Languages.push(["ru","Русский"]);
GO.Languages.push(["ro","Romanian"]);
GO.Languages.push(["fi","Suomi - Finland"]);
GO.Languages.push(["sv","Svenska"]);
GO.Languages.push(["tr","Türkçe"]);
GO.Languages.push(["th","ไทย"]);
GO.Languages.push(["vi","Tiếng Việt"]);
GO.Languages.push(["bg","Български"]);
GO.lang = {"core":{"core":{"name":"Group-Office Core","description":"Contains basic functions for Group-Office to function properly.","product_name":"Group-Office","mayManage":"Manage","mayChangeUsers":"Change users","mayChangeGroups":"Change groups","mayChangeCustomFields":"Change custom fields","mayChangeWelcomeMsg":"Change welcome message","shortDays":["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],"sirMadam":{"M":"sir","F":"madam"},"sexes":{"M":"Male","F":"Female"},"full_months":{"1":"January","2":"February","3":"March","4":"April","5":"May","6":"June","7":"July","8":"August","9":"September","10":"October","11":"November","12":"December"},"short_months":{"1":"Jan","2":"Feb","3":"Mar","4":"Apr","5":"May","6":"Jun","7":"Jul","8":"Aug","9":"Sep","10":"Oct","11":"Nov","12":"Dec"},"short_days":["Su","Mo","Tu","We","Th","Fr","Sa"],"full_days":["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],"month_times":{"1":"the first","2":"the second","3":"the third","4":"the fourth","5":"the fifth"},"recoveryMailBody":"Dear %s,\nYou requested a new password for %s from ip-address: {ip_address}. Your username is \"%s\".\nClick at the link below (or paste it in a browser) to change your password:\n\n%s\n\nIf you did not request a new password please delete this mail.","countries":{"AE":"United Arab Emirates","AF":"Afghanistan","AX":"\u00c5land Islands","AL":"Albania","DZ":"Algeria","AS":"American Samoa","AD":"Andorra","AO":"Angola","AI":"Anguilla","AQ":"Antarctica","AG":"Antigua and Barbuda","AR":"Argentina","AM":"Armenia","AW":"Aruba","AU":"Australia","AT":"Austria","AZ":"Azerbaijan","BS":"Bahamas","BH":"Bahrain","BD":"Bangladesh","BB":"Barbados","BY":"Belarus","BE":"Belgium","BZ":"Belize","BJ":"Benin","BM":"Bermuda","BT":"Bhutan","BO":"Bolivia","BA":"Bosnia and Herzegowina","BW":"Botswana","BV":"Bouvet Island","BR":"Brazil","IO":"British Indian Ocean Territory","BN":"Brunei Darussalam","BG":"Bulgaria","BF":"Burkina Faso","BI":"Burundi","BQ":"Bonaire, Sint Eustatius and Saba","KH":"Cambodia","CM":"Cameroon","CA":"Canada","CV":"Cape Verde","CW":"Cura\u00e7ao","KY":"Cayman Islands","CF":"Central African Republic","TD":"Chad","CL":"Chile","CN":"China","CX":"Christmas Island","CC":"Cocos (Keeling) Islands","CO":"Colombia","KM":"Comoros","CG":"Congo","CK":"Cook Islands","CR":"Costa Rica","CI":"Cote D'Ivoire","HR":"Croatia","CU":"Cuba","CY":"Cyprus","CZ":"Czech Republic","DK":"Denmark","DJ":"Djibouti","DM":"Dominica","DO":"Dominican Republic","TP":"East Timor","EC":"Ecuador","EG":"Egypt","SV":"El Salvador","GQ":"Equatorial Guinea","ER":"Eritrea","EE":"Estonia","ET":"Ethiopia","FK":"Falkland Islands (Malvinas)","FO":"Faroe Islands","FJ":"Fiji","FI":"Finland","FR":"France","FX":"France, Metropolitan","GF":"French Guiana","PF":"French Polynesia","TF":"French Southern Territories","GA":"Gabon","GM":"Gambia","GE":"Georgia","DE":"Germany","GH":"Ghana","GI":"Gibraltar","GR":"Greece","GL":"Greenland","GD":"Grenada","GP":"Guadeloupe","GU":"Guam","GT":"Guatemala","GG":"Guernsey","GN":"Guinea","GW":"Guinea-bissau","GY":"Guyana","HT":"Haiti","HM":"Heard and Mc Donald Islands","HN":"Honduras","HK":"Hong Kong","HU":"Hungary","IS":"Iceland","IN":"India","ID":"Indonesia","IE":"Ireland","IL":"Israel","IR":"Iran","IQ":"Iraq","IT":"Italy","JM":"Jamaica","JP":"Japan","JO":"Jordan","KZ":"Kazakhstan","KE":"Kenya","KI":"Kiribati","KW":"Kuwait","KG":"Kyrgyzstan","LA":"Lao People's Democratic Republic","LV":"Latvia","LB":"Lebanon","LS":"Lesotho","LR":"Liberia","LY":"Libya","LI":"Liechtenstein","LT":"Lithuania","LU":"Luxembourg","MO":"Macau","MK":"Macedonia, The Former Yugoslav Republic of","MG":"Madagascar","MY":"Malaysia","MW":"Malawi","MV":"Maldives","ML":"Mali","MT":"Malta","MH":"Marshall Islands","MQ":"Martinique","MR":"Mauritania","MU":"Mauritius","YT":"Mayotte","MX":"Mexico","FM":"Micronesia, Federated States of","MD":"Moldova, Republic of","MC":"Monaco","MN":"Mongolia","MS":"Montserrat","MA":"Morocco","MZ":"Mozambique","MM":"Myanmar","NA":"Namibia","NR":"Nauru","NP":"Nepal","NL":"Netherlands","AN":"Netherlands Antilles","NC":"New Caledonia","NZ":"New Zealand","NI":"Nicaragua","NE":"Niger","NG":"Nigeria","NU":"Niue","NF":"Norfolk Island","MF":"Saint Martin (French part)","MP":"Northern Mariana Islands","NO":"Norway","OM":"Oman","PW":"Palau","PA":"Panama","PG":"Papua New Guinea","PY":"Paraguay","PE":"Peru","PH":"Philippines","PK":"Pakistan","PN":"Pitcairn","PL":"Poland","PS":"Palestine","PT":"Portugal","PR":"Puerto Rico","QA":"Qatar","RE":"Reunion","RO":"Romania","RU":"Russian Federation","RW":"Rwanda","KN":"Saint Kitts and Nevis","LC":"Saint Lucia","VC":"Saint Vincent and the Grenadines","WS":"Samoa","SM":"San Marino","ST":"Sao Tome and Principe","SA":"Saudi Arabia","SN":"Senegal","SC":"Seychelles","SL":"Sierra Leone","SG":"Singapore","SK":"Slovakia (Slovak Republic)","SI":"Slovenia","SB":"Solomon Islands","SO":"Somalia","SX":"Sint Maarten (Dutch part)","ZA":"South Africa","GS":"South Georgia and the South Sandwich Islands","ES":"Spain","LK":"Sri Lanka","SH":"St. Helena","PM":"St. Pierre and Miquelon","SD":"Sudan","SR":"Suriname","SJ":"Svalbard and Jan Mayen Islands","SZ":"Swaziland","SE":"Sweden","CH":"Switzerland","SY":"Syrian Arab Republic","TW":"Taiwan","TJ":"Tajikistan","TZ":"Tanzania, United Republic of","TH":"Thailand","CD":"The Democratic Republic of the Congo","TG":"Togo","TK":"Tokelau","TO":"Tonga","TT":"Trinidad and Tobago","TN":"Tunisia","TR":"Turkey","TM":"Turkmenistan","TC":"Turks and Caicos Islands","TV":"Tuvalu","UG":"Uganda","UA":"Ukraine","GB":"United Kingdom","US":"United States of America","UM":"United States Minor Outlying Islands","UY":"Uruguay","UZ":"Uzbekistan","VU":"Vanuatu","VA":"Vatican City State (Holy See)","VE":"Venezuela","VG":"Virgin Islands (British)","VI":"Virgin Islands (U.S.)","VN":"Vietnam","WF":"Wallis and Futuna Islands","EH":"Western Sahara","YE":"Yemen","ZM":"Zambia","ZW":"Zimbabwe","KP":"Korea, Democratic people's republic of","KR":"Korea, Republic of","RS":"Republic of Serbia","ME":"Republic of Montenegro","IC":"Canary Islands","XK":"Kosovo"},"filetypes":{"unknown":"Unknown filetype","txt":"Textfile","sxw":"OpenOffice.Org text","ott":"OpenOffice.Org template","odc":"OpenOffice.Org chart","odb":"OpenOffice.Org database","odf":"OpenOffice.Org formula","odg":"OpenOffice.Org graphics","otg":"OpenOffice.Org graphics template","odi":"OpenOffice.Org image","odp":"OpenOffice.Org presentation","otp":"OpenOffice.Org presentation template","ods":"OpenOffice.Org spreadsheet","ots":"OpenOffice.Org spreadsheet template","odt":"OpenOffice.Org text","odm":"OpenOffice.Org text master","oth":"OpenOffice.Org web page","docx":"Microsoft Word document","xls":"Microsoft Excel spreadsheet","xlsx":"Microsoft Excel spreadsheet","mdb":"Microsoft Access database","ppt":"Microsoft Powerpoint presentation","pptx":"Microsoft Powerpoint presentation","pps":"Microsoft Powerpoint presentation","ppsx":"Microsoft Powerpoint presentation","sxc":"OpenOffice.Org Calc spreadsheet","tar":"Tar archive","zip":"ZIP compressed archive","rar":"RAR compressed archive","gz":"GZIP compressed archive","tgz":"GZIP compressed archive","bz2":"BZIP2 compressed archive","exe":"Windows executable file","ttf":"True type font","html":"HTML document","htm":"HTML document","jpg":"Image","jpeg":"Image","gif":"Image","bmp":"Image","tif":"Image","png":"Image","php":"PHP Script","ics":"Calendar information","vcf":"Contact information","wav":"Sound file","ogg":"Sound file","mp3":"Sound file","wma":"Sound file","mpg":"Video clip","mpeg":"Video clip","avi":"Video clip","wmv":"Video clip","wmf":"Video clip","pdf":"Adobe Acrobat PDF","doc":"Microsoft Word document","dot":"Microsoft Word document","psd":"Adobe Photoshop file","rtf":"Rich Text Format","swf":"Macromedia Flash Movie","fla":"Macromedia Flash Document (Source)","ai":"Adobe Illustrator file","eml":"E-mail message","csv":"Comma Separated Values","js":"Javascript","sql":"SQL database export","xmind":"XMind file"}}},"community":{"addressbook":{"name":"Address Book","description":"Store contacts and organizations","mayChangeAddressbooks":"Change address books","mayExportContacts":"Export contacts","emailTypes":{"work":"Work","home":"Home","billing":"Billing"},"phoneTypes":{"work":"Work","home":"Home","mobile":"Mobile","workmobile":"Work Mobile","fax":"Fax","workfax":"Work fax"},"addressTypes":{"visit":"Visit","postal":"Postal","work":"Work","home":"Home","delivery":"Delivery"},"dateTypes":{"birthday":"Birthday","anniversary":"Anniversary","action":"Action"},"urlTypes":{"homepage":"Homepage","facebook":"Facebook","twitter":"Twitter","linkedin":"LinkedIn","instagram":"Instagram","tiktok":"TikTok"},"salutationTemplate":"Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms.\/Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[\/if][\/if][\/if][if {{contact.middleName}}] {{contact.middleName}}[\/if] {{contact.lastName}}"},"bookmarks":{"name":"Bookmarks","description":"Website description."},"comments":{"name":"Comments","description":"Add comments to entities","category":"Category","comment":"Comment","comments":"Comments"},"history":{"name":"History","description":"Keep a history log of item mutations, show the changes in the detail panel."},"notes":{"name":"Notes","description":"A note taking module.","mayChangeNoteBooks":"Change note book"},"otp":{"name":"OTP Authenticator","description":"Improves security by adding two factor authentication options to the user account settings."},"tasks":{"name":"Tasks","description":"Create and manage tasks, place them in lists, plan for today and assign or share them with other users","mayChangeTasklists":"Change lists","mayChangeCategories":"Change categories"},"goui":{"name":"GOUI demo","description":"Demonstrating the Group-Office User Interface framework."},"multi_instance":{"name":"Multi instance","description":"Create multiple Group-Office instances on this server."},"oauth2client":{"name":"Oauth2 Client","description":"A module to configure Authentication using Oauth2."},"ldapauthenticator":{"name":"LDAP authenticator","description":"Use an LDAP directory to authenticate and autocreate users."}},"intermesh":{"demo":[]},"legacy":{"calendar":{"statuses":{"NEEDS-ACTION":"Needs action","ACCEPTED":"Accepted","DECLINED":"Declined","TENTATIVE":"Tentative","DELEGATED":"Delegated","COMPLETED":"Completed","IN-PROCESS":"In process","CONFIRMED":"Confirmed","CANCELLED":"Cancelled"},"updateReponses":{"ACCEPTED":"%s has accepted the event %s","DECLINED":"%s has declined the event %s","TENTATIVE":"%s has marked the event %s as tentative","NEEDS-ACTION":"%s has marked the event %s as not decided yet"},"name":"Calendar","description":"Calendar module; Every user can add, edit or delete appointments Also appointments from other users can be viewed and if necessary it can be changed."},"email":{"name":"Email","description":"Full featured e-mail client. Every user will be able to send and receive emails"},"files":{"name":"Files","description":"Files module; Module for sharing files.","mayAccessMainPanel":"Access main"},"jitsimeet":{"name":"Jitsi video meeting for calendar","description":"Specify a Jitsi url in settings to be able to add video links to calendar events"},"sieve":{"name":"Sieve","description":"Manage sieve e-mail filtering rules"},"summary":{"name":"Start page","description":"Show an overview of different actual items"},"sync":{"name":"Synchronization","description":"Synchronization server for mobile devices using CalDAV, CardDAV, ActiveSync and SyncML","2fa-body":"Hi,\n\nPlease login to Group-Office to complete your account setup at:\n\n{URL}\n\nBest regards,\n\n{TITLE}"},"zpushadmin":{"name":"ActiveSync","description":"Manage ActiveSync connections."},"assistant":{"name":"Group-Office assistant","description":"Automatically mounts a WebDAV network drive and launches your default desktop application."},"projects2":{"name":"Projects","description":"Project manager with time registration","mayFinance":"Finance","mayBudget":"Manage Budgets","Costs_per_month_no_vat":"Costs per Month ex. VAT","Vat_month":"VAT for Costs per Month","Total_costs_no_vat":"Total Costs ex. VAT","Vat_total_costs":"Total VAT","Total_budget":"Total Budget ex. VAT","Total_budget_vat":"VAT for Total Budget","Hours_spent_this_month":"Hours Spent in Month","Total_hours_spent":"Total Hours Spent","Total_budgeted_hours":"Total Budget Hours"},"savemailas":{"name":"E-mail save as menu","description":"Save e-mail messages as file, link, event, note or task.","LinkedEmail":"E-mail"},"gota":{"name":"GOTA","description":"Group-Office Transfer Agent. Can edit online files locally and transfers modifcations back to Group-Office"},"tickets":{"priority_level":["Low","Normal","High"],"ticket_action":{"responded_by":"Responded by"},"name":"Tickets","description":"Support ticket system"},"smime":{"name":"SMIME support","description":"Extend the mail module with SMIME signing and encryption."},"leavedays":{"name":"Holidays","description":"Module for holiday management of personnel."}},"business":{"business":{"name":"Employee\/Business management","description":"Manage businesses and employee agreements","mayManageEmployees":"Manage employees"},"catalog":{"name":"Article","description":"Your articles in a catalog","Catalog":"Catalog"},"finance":{"name":"Finance","description":"Create quotes, invoices and orders","FinanceDocument":"Financial","types":{"quote":"Quote","salesorder":"Order","salesinvoice":"Invoice","purchaseorder":"Purchase order","purchaseinvoice":"Purchase invoice"},"InvoiceVerb":"Invoice"},"contracts":{"name":"Contract","description":"Contracts module for finance","Contracts":"Contracts"},"support":{"name":"Support","description":"Use tasks as support tickets"},"supportclient":{"name":"Support Client","description":"Support module for creating support calls as the customer."},"studio":{"name":"Studio","description":"Easily create boilerplate code for simple Group-Office modules","Create a Module":"Create a Module","Start Code Generation":"Start Code Generation","Entity Name":"Entity Name","Group Office Studio":"Group-Office Studio","Unable to save module frontend settings":"Unable to save module frontend settings"},"automation":{"name":"Automation","description":"Automation module","Automation":"Automation"},"newsletters":{"name":"Newsletters","description":"Send out e-mail newsletters using templates."},"projects3":{"name":"Projects","description":"3rd generation projects module","Projects3":"Projects","Project3":"Project"}},"udo":{"forms":[]},"studio":{"fietsen":[]},"iso":"en"};
GO.lang.holidaySets = [{"iso":"de","label":"Deutsch"},{"iso":"de_ch","label":"Deutsch \/ Schweizerische Eidgenossenschaft"},{"iso":"de_at","label":"Deutsch \/ \u00d6sterreich"},{"iso":"en","label":"English \/ America"},{"iso":"en_au","label":"English \/ Australia"},{"iso":"en_ph","label":"English \/ Philippines"},{"iso":"en_uk","label":"English \/ United Kingdom"},{"iso":"es","label":"Espa\u00f1ol"},{"iso":"fr","label":"Francais"},{"iso":"hr","label":"Hrvatski"},{"iso":"it","label":"Italiano"},{"iso":"ja","label":"Japanese"},{"iso":"hu","label":"Magyar"},{"iso":"nl","label":"Nederlands"},{"iso":"nb","label":"Norsk bokm\u00e5l"},{"iso":"pt_pt","label":"Portugu\u00eas"},{"iso":"pt_br","label":"Portugu\u00eas - Brazil"},{"iso":"ro","label":"Romanian"},{"iso":"sv","label":"Svenska"},{"iso":"en_us","label":"en_us"},{"iso":"cs","label":"\u010ce\u0161tina"},{"iso":"ar","label":"\u0639\u0631\u0628\u064a (Arabic)"},{"iso":"bn_bd","label":"\u09ac\u09be\u0982\u09b2\u09be (Bangladesh)"},{"iso":"th","label":"\u0e44\u0e17\u0e22"}];
