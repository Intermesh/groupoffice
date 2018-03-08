/*!
 * Ext JS Library 3.4.0
 * Copyright(c) 2006-2011 Sencha Inc.
 * licensing@sencha.com
 * http://www.sencha.com/license
 */
/**
 * List compiled by KillerNay on the extjs.com forums.
 * Thank you KillerNay!
 *
 * Thailand Translations
 */

Ext.UpdateManager.defaults.indicatorText = '<div class="loading-indicator">กำลังโหลด...</div>';

if(Ext.View){
  Ext.View.prototype.emptyText = "";
}

if(Ext.grid.GridPanel){
  Ext.grid.GridPanel.prototype.ddText = "{0} จำนวนที่ถูกเลือก";
}

if(Ext.TabPanelItem){
  Ext.TabPanelItem.prototype.closeText = "ปิดแท็บ";
}

if(Ext.form.Field){
  Ext.form.Field.prototype.invalidText = "ค่าของฟิลด์ที่ไม่ถูกต้อง";
}

if(Ext.LoadMask){
  Ext.LoadMask.prototype.msg = "กำลังโหลด...";
}

Date.monthNames = [
  'มกราคม',
  'กุมภาพันธ์',
  'มีนาคม',
  'เมษายน',
  'พฤษภาคม',
  'มิถุนายน',
  'กรกฏาคม',
  'สิงหาคม',
  'กันยายน',
  'ตุลาคม',
  'พฤจิกายน',
  'ธันวาคม'
];

Date.getShortMonthName = function(month) {
  return Date.monthNames[month].substring(0, 3);
};

Date.monthNumbers = {
  'มค' : 0,
  'กพ' : 1,
  'มีค' : 2,
  'เมย' : 3,
  'พค' : 4,
  'มิย' : 5,
  'กค' : 6,
  'สค' : 7,
  'กย' : 8,
  'ตค' : 9,
  'พย' : 10,
  'ธค' : 11
};

Date.getMonthNumber = function(name) {
  return Date.monthNumbers[name.substring(0, 1).toUpperCase() + name.substring(1, 3).toLowerCase()];
};

Date.dayNames = [
  'อา.',
  'จ.',
  'อ.',
  'พ.',
  'พฤ.',
  'ศ.',
  'ส.'
];

Date.getShortDayName = function(day) {
  return Date.dayNames[day].substring(0, 3);
};

if(Ext.MessageBox){
  Ext.MessageBox.buttonText = {
    ok     : 'ตกลง',
    cancel : 'ยกเลิก',
    yes    : 'ใช่',
    no     : 'ไม่ใช่'
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
    todayText         : 'วันนี้',
    minText           : "วันที่นี้ น้อยกว่าค่าต่ำสุดที่กำหนด",
    maxText           : "วันที่นี้ มากกว่าค่าสูงสุดที่กำหนด",
    disabledDaysText  : "",
    disabledDatesText : "",
    monthNames        : Date.monthNames,
    dayNames          : Date.dayNames,
    nextText          : 'เดือนถัดไป (Control+Right)',
    prevText          : 'เดือนที่แล้ว (Control+Left)',
    monthYearText     : 'ระบุเดือน (Control+Up/Down to move years)',
    todayTip          : "{0} (Spacebar)",
    format            : "d/m/y",
    okText            : 'ตกลง',
    cancelText        : 'ยกเลิก',
    startDay          : 0
  });
}

if(Ext.PagingToolbar){
  Ext.apply(Ext.PagingToolbar.prototype, {
    beforePageText : "หน้า",
    afterPageText  : "ของ {0}",
    firstText      : "หน้าแรก",
    prevText       : "หน้าที่แล้ว",
    nextText       : "หน้าถัดไป",
    lastText       : "หน้าสุดท้าย",
    refreshText    : "รีเฟรช",
    displayMsg     : "แสดงผลจาก {0} - {1} ของทังหมด {2}",
    emptyMsg       : 'ไม่พบรายการ'
  });
}

if(Ext.form.TextField){
  Ext.apply(Ext.form.TextField.prototype, {
    minLengthText : "ความยาวต่ำสุดที่กรอกได้คือ {0}",
    maxLengthText : "ความยาวสูงสุดที่กรอกได้คือ {0}",
    blankText     : "รายการนี้จำเป็นต้องกรอก",
    regexText     : "",
    emptyText     : null
  });
}

if(Ext.form.NumberField){
  Ext.apply(Ext.form.NumberField.prototype, {
    minText : "ค่าต่ำสุดที่กรอกได้คือ  {0}",
    maxText : "ค่าสูงสุดที่กรอกได้คือ {0}",
    nanText : "{0} ไม่ใช่ตัวเลข"
  });
}

if(Ext.form.DateField){
  Ext.apply(Ext.form.DateField.prototype, {
    disabledDaysText  : "ปิดการใช้งาน",
    disabledDatesText : "ปิดการใช้งาน",
    minText           : "ค่าวันในข่องนี้ต้องเป็นวันหลังจากวันที่ {0}",
    maxText           : "ค่าวันในช่องนี้ต้องเป็นวันก่อนวันที่ {0}",
    invalidText       : "{0} ไม่ใช่รูปแบบของวันที่ที่กำหนด - กรุณาใช้รูปแบบดังนี้ {1}",
    format            : "m/d/y",
    altFormats        : "m/d/Y|m-d-y|m-d-Y|m/d|m-d|md|mdy|mdY|d|Y-m-d",
    startDay          : 0
  });
}

if(Ext.form.ComboBox){
  Ext.apply(Ext.form.ComboBox.prototype, {
    loadingText       : "กำลังโหลด...",
    valueNotFoundText : undefined
  });
}

if(Ext.form.VTypes){
  Ext.apply(Ext.form.VTypes, {
    emailText    : 'ค่าในช่องนี้จะต้องเป็นอีเมลซึ่งมีรูปแบบคือ "user@example.com"',
    urlText      : 'ค่าในช่องนี้จะต้องเป็น URL ซึ่งมีรูปแบบคือ "http:/'+'/www.example.com"',
    alphaText    : 'ค่าในช่องนี้จะต้องเป็นตัวอักษรและเครื่องหมาย _ เท่านั้น',
    alphanumText : 'ค่าในช่องนี้จะต้องเป็นตัวอักษร ตัวเลข และเครื่องหมาย _ เท่านั้น'
  });
}

if(Ext.form.HtmlEditor){
  Ext.apply(Ext.form.HtmlEditor.prototype, {
    createLinkText : 'กรุณาระบุค่า URL ของ link:',
    buttonTips : {
      bold : {
        title: 'ตัวใหญ่ (Ctrl+B)',
        text: 'ทำตัวใหญ่ให้กับข้อความที่ถูกเลือก',
        cls: 'x-html-editor-tip'
      },
      italic : {
        title: 'ตัวเอียง (Ctrl+I)',
        text: 'ทำตัวเอียงให้กับข้อความที่ถูกเลือก',
        cls: 'x-html-editor-tip'
      },
      underline : {
        title: 'เส้นใต้ (Ctrl+U)',
        text: 'ขีดเส้นใต้ให้กับข้อความที่ถูกเลือก',
        cls: 'x-html-editor-tip'
      },
      increasefontsize : {
        title: 'เพิ่ม',
        text: 'เพิ่มขนาดฟอนต์',
        cls: 'x-html-editor-tip'
      },
      decreasefontsize : {
        title: 'ลด',
        text: 'ลดขนาดฟอนต์',
        cls: 'x-html-editor-tip'
      },
      backcolor : {
        title: 'สีด้านหลัง',
        text: 'เปลี่ยนสีด้านหลังให้กับข้อความที่ถูกเลือก',
        cls: 'x-html-editor-tip'
      },
      forecolor : {
        title: 'สีฟอนต์',
        text: 'เปลี่ยนสีให้กับข้อความทีถูกเลือก',
        cls: 'x-html-editor-tip'
      },
      justifyleft : {
        title: 'ชิดซ้าย',
        text: 'จัดเรียงโดยให้ชิดทางซ้าย',
        cls: 'x-html-editor-tip'
      },
      justifycenter : {
        title: 'ตรงกลาง',
        text: 'จัดเรียงข้อความให้อยู่ตรงกลาง',
        cls: 'x-html-editor-tip'
      },
      justifyright : {
        title: 'ชิดชวา',
        text: 'จัดเรียงโดยให้ชิดทางขวา',
        cls: 'x-html-editor-tip'
      },
      insertunorderedlist : {
        title: 'เครื่องหมายนำหน้า',
        text: 'แสดงเครื่องหมายหน้าข้อความ',
        cls: 'x-html-editor-tip'
      },
      insertorderedlist : {
        title: 'ตัวเลขนำหน้า',
        text: 'แสดงตัวเลขหน้าข้อความ',
        cls: 'x-html-editor-tip'
      },
      createlink : {
        title: 'ลิ้งค์',
        text: 'สร้างลิ้งค์ให้กับข้อความที่ถูกเลือก',
        cls: 'x-html-editor-tip'
      },
      sourceedit : {
        title: 'แก้ไขซอสโคด',
        text: 'เปลี่ยนโหมดเป็นรูปแบบซอสโคด',
        cls: 'x-html-editor-tip'
      }
    }
  });
}

if(Ext.grid.GridView){
  Ext.apply(Ext.grid.GridView.prototype, {
    sortAscText  : "จากน้อยไปหามาก",
    sortDescText : "จากมากไปหาน้อย",
    lockText     : "ล็อกช่อง",
    unlockText   : "ปลดล็อกช่อง",
    columnsText  : "ช่อง"
  });
}

if(Ext.grid.GroupingView){
  Ext.apply(Ext.grid.GroupingView.prototype, {
    emptyGroupText : '(ไม่มี)',
    groupByText    : 'จัดกลุ่มตามฟิลด์นี้',
    showGroupsText : 'แสดงแบบจัดกลุ่ม'
  });
}

if(Ext.grid.PropertyColumnModel){
  Ext.apply(Ext.grid.PropertyColumnModel.prototype, {
    nameText   : "ชื่อ",
    valueText  : "ค่า",
    dateFormat : "m/j/Y"
  });
}

if(Ext.layout.BorderLayout && Ext.layout.BorderLayout.SplitRegion){
  Ext.apply(Ext.layout.BorderLayout.SplitRegion.prototype, {
    splitTip            : "ลากเพื่อเปลี่ยนขนาด",
    collapsibleSplitTip : "ลากเพื่อเปลี่ยนขนาด ดับเบิลคลิกเพื่อซ่อน"
  });
}
