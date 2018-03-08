<?php
//Uncomment this line in new translations!

require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('common'));
$lang['common']['extjs_lang']='th'; 
$lang['common']['about']='เวอร์ชั่น: {version}<br />
<br />
Copyright (c) 2003-{current_year}, {company_name}<br />
All rights reserved.<br />
This program is protected by copyright law and the {product_name} license.<br />
';

$lang['common']['htmldirection']= 'ltr';

$lang['common']['quotaExceeded']='พื้นที่ฐานข้อมูลไม่เพียงพอ. กรุณาลบไฟล์ หรือ ติดต่อผู้ดูแลระบบเพื่อเพิ่มพื้นที่ฐานข้อมูล';
$lang['common']['errorsInForm'] = 'เกิดข้อผิดพลาดในการทำรายการ. กรุณาตรวจสอบความถูกต้องและลองอีกครั้ง.';

$lang['common']['moduleRequired']='เมนู %s มีการเรียกใช้ฟังก์ชันที่ทำรายการ';//The %s module is required for this function

$lang['common']['loadingCore']= 'กำลังตรวจสอบระบบ..';
$lang['common']['loadingLogin'] = 'กำลังตรวจสอบการเข้าใช้งาน..';
$lang['common']['renderInterface']='กำลังเข้าสู่ระบบ {product_name}..';//Rendering interface
$lang['common']['loadingModule'] = 'เข้าสู่ระบบ..';

$lang['common']['loggedInAs'] = 'เข้าสู่ระบบ ';
$lang['common']['search']='ค้นหา';
$lang['common']['settings']='การตั้งค่า';
$lang['common']['adminMenu']='ผู้ดูแลระบบ';
$lang['common']['help']='ช่วยเหลือ';
$lang['common']['logout']='ออกจากโปรแกรม';
$lang['common']['badLogin'] = 'ชื่อผู้ใช้ หรือ รหัสผ่าน ผิดพลาด';
$lang['common']['badPassword'] = 'รหัสผ่านไม่ถูกต้อง';

$lang['common']['passwordMatchError']='รหัสผ่านไม่ถูกต้อง';
$lang['common']['accessDenied']='เกิดข้อผิดพลาดในการใช้งาน';
$lang['common']['saveError']='เกิดข้อผิดพลาดขณะบันทึกข้อมูล';
$lang['common']['deleteError']='เกิดข้อผิดพลาดขณะลบข้อมูล';
$lang['common']['selectError']='เกิดข้อผิดพลาดขณะอ่านข้อมูล';
$lang['common']['missingField'] = 'เกิดข้อผิดพลาด.กรุณากรอกข้อมูลให้ครบทุกรายการ.';
$lang['common']['noFileUploaded']='เกิดข้อผิดพลาด.ไม่ได้รับไฟล์ที่ส่งถึง';

$lang['common']['salutation']='Salutation';
$lang['common']['firstName'] = 'ชื่อ';
$lang['common']['lastName'] = 'นามสกุล';
$lang['common']['middleName'] = 'ชื่อเล่น';
$lang['common']['sirMadam']['M'] = 'sir';
$lang['common']['sirMadam']['F'] = 'madam';
$lang['common']['initials'] = 'ชื่อย่อ';
$lang['common']['sex'] = 'เพศ';
$lang['common']['birthday'] = 'วันเดือนปีเกิด';
$lang['common']['sexes']['M'] = 'ชาย';
$lang['common']['sexes']['F'] = 'หญิง';
$lang['common']['title'] = 'หัวข้อ';//title
$lang['common']['addressNo'] = 'บ้านเลขที่';
$lang['common']['workAddressNo'] = 'ที่อยู่ที่ทำงาน';
$lang['common']['postAddress'] = 'ที่อย่';
$lang['common']['postAddressNo'] = 'บ้านเลขที่';
$lang['common']['postCity'] = 'อำเภอ/เขต';
$lang['common']['postState'] = 'จังหวัด';
$lang['common']['postCountry'] = 'ประเทศ';
$lang['common']['postZip'] = 'รหัสไปรษณีย์';
$lang['common']['visitAddress'] = 'ที่อยู่ที่สามารถติดต่อได้';
$lang['common']['postAddressHead'] = 'ที่อยู่ปัจจุบัน';
$lang['common']['name'] = 'ชื่อ';
$lang['common']['user'] = 'ผู้ใช้งาน';
$lang['common']['username'] = 'ผู้ใช้งาน';
$lang['common']['password'] = 'รหัสผ่าน';
$lang['common']['authcode'] = 'รหัสยืนยันสิทธิ์';//Authorization code
$lang['common']['country'] = 'ประเทศ';
$lang['common']['state'] = 'จังหวัด';
$lang['common']['city'] = 'อำเภอ/เขต';
$lang['common']['zip'] = 'รหัสไปรษณีย์';
$lang['common']['address'] = 'ที่อยู่';
$lang['common']['email'] = 'อีเมล';
$lang['common']['phone'] = 'หมายเลขโทรศัพท์';
$lang['common']['workphone'] = 'หมายเลขโทรศัพท์';// (ที่ทำงาน)
$lang['common']['cellular'] = 'โทรศัพท์มือถือ';
$lang['common']['company'] = 'หน่วยงาน';
$lang['common']['department'] = 'แผนก';
$lang['common']['function'] = 'ตำแหน่ง';
$lang['common']['question'] = 'คำถามลับ';//Secret question
$lang['common']['answer'] = 'คำตอบ';
$lang['common']['fax'] = 'หมายเลขแฟ็กซ์';
$lang['common']['workFax'] = 'หมายเลขแฟ็กซ์';// (ที่ทำงาน
$lang['common']['homepage'] = 'โฮมเพจ';
$lang['common']['workAddress'] = 'ที่อยู่';// (ที่ทำงาน
$lang['common']['workZip'] = 'รหัสไปรษณีย์';
$lang['common']['workCountry'] = 'ประเทศ';
$lang['common']['workState'] = 'จังหวัด';
$lang['common']['workCity'] = 'อำเภอ/เขต';
$lang['common']['today'] = 'วันปัจจุบัน';
$lang['common']['tomorrow'] = 'วันถัดไป';

$lang['common']['SearchAll'] = 'ทั้งหมด';
$lang['common']['total'] = 'รายการรวม';
$lang['common']['results'] = 'รายการที่ได้';


$lang['common']['months'][1]='มกราคม';
$lang['common']['months'][2]='กุมภาพันธ์';
$lang['common']['months'][3]='มีนาคม';
$lang['common']['months'][4]='เมษายน';
$lang['common']['months'][5]='พฤษภาคม';
$lang['common']['months'][6]='มิถุนายน';
$lang['common']['months'][7]='กรกฏาคม';
$lang['common']['months'][8]='สิงหาคม';
$lang['common']['months'][9]='กันยายน';
$lang['common']['months'][10]='ตุลาคม';
$lang['common']['months'][11]='พฤษภาคม';
$lang['common']['months'][12]='ธันวาคม';

$lang['common']['short_days'][0]='จ';
$lang['common']['short_days'][1]='อ';
$lang['common']['short_days'][2]='พ';
$lang['common']['short_days'][3]='พฤ';
$lang['common']['short_days'][4]='ศ';
$lang['common']['short_days'][5]='ส';
$lang['common']['short_days'][6]='อา';


$lang['common']['full_days'][1] = 'จันทร์';
$lang['common']['full_days'][2] = 'อังคาร';
$lang['common']['full_days'][3] = 'พุธ';
$lang['common']['full_days'][4] = 'พฤหัสบดี';
$lang['common']['full_days'][5] = 'ศุกร์';
$lang['common']['full_days'][6]= 'เสาร์';
$lang['common']['full_days'][0] = 'อาทิตย์';

$lang['common']['default']='ค่าเริ่มต้น';
$lang['common']['description']='คำอธิบาย';
$lang['common']['date']='วัน';

$lang['common']['default_salutation']['M']='Dear Mr';
$lang['common']['default_salutation']['F']='Dear Ms';
$lang['common']['default_salutation']['unknown']='Dear Mr / Ms';

$lang['common']['mins'] = 'นาที';
$lang['common']['hour'] = 'ชั่วโมง';
$lang['common']['hours'] = 'ชั่วโมง';
$lang['common']['day'] = 'วัน';
$lang['common']['days'] = 'วัน';
$lang['common']['week'] = 'สัปดาห์';
$lang['common']['weeks'] = 'สัปดาห์';

$lang['common']['group_everyone']='ทั้งหมด';
$lang['common']['group_admins']='ผู้ดูแลระบบ';
$lang['common']['group_internal']='เฉพาะในกลุ่ม';

$lang['common']['admin']='ผู้ดูแลระบบ';

$lang['common']['beginning']='Salutation';

$lang['common']['max_emails_reached']= 'จำนวนสูงสุดของอีเมลจากโฮสต์  SMTP  %s รายการ จาก %s ในหนึ่งวัน.';//The maximum number of e-mail for SMTP host %s of %s per day have been reached
$lang['common']['usage_stats']='พื้นที่การใช้งาน %s';
$lang['common']['usage_text']='การติดตั้งใช้งาน {product_name}';//This {product_name} installation is using

$lang['common']['database']='ฐานข้อมูล';
$lang['common']['files']='ไฟล์';
$lang['common']['email']='อีเมล';
$lang['common']['total']='รายการรวม';

$lang['common']['confirm_leave']='เมื่อออกจากระบบ {product_name} ข้อมูลอาจสูญหายหากไม่ทำการบันทึกการเปลี่ยนแปลง. ';
//* Top แก้ 22-07-2009
$lang['common']['totals']='รวม';
$lang['common']['printPage']='หน้า %s จาก %s';
$lang['common']['loadingModules']='กำลังโหลดโมดูล';
$lang['common']['invalidEmailError']='ที่อยู่อีเมล์ไม่ถูกต้อง';
$lang['common']['invalidDateError']='คุณระบุวันที่ผิด';
$lang['common']['error']='พบข้อผิดพลาด';
$lang['common']['dataSaved']='ข้อมูลถูกบันทึกเรียบร้อยแล้ว';
$lang['common']['uploadMultipleFiles']= 'คลิ๊กปุ่ม \'Browse\' เพื่อเลือกไฟล์หรือโพลเดอร์จากเครื่องคอมพิวเตอร์. คลิ๊กที่ \'อับโหลด\' เพื่อส่งไฟล์ไปยัง {product_name}. หน้าต่างนี้จะปิดอัตโนมัติเมื่อการทำงานเสร็จสิ้น';
$lang['common']['loginToGO']='คลิ๊กที่นี่เพื่อล็อคอินเข้าสู่ {product_name}';
$lang['common']['links']='เชื่อมโยง';
$lang['common']['GOwebsite']='{product_name} เว็บไซต์';
$lang['common']['GOisAProductOf']='<i>{product_name}</i> is a product of <a href="http://www.intermesh.nl/en/" target="_blank">Intermesh</a>';
$lang['common']['startMenu']='เมนูเริ่มต้น';
$lang['common']['address_format']='รูปแบบที่อยู่';
$lang['common']['dear']='ถึง';
$lang['common']['yes']='ตกลง';
$lang['common']['no']='ปฏิเสธ';
$lang['commmon']['logFiles']='ล็อคไฟล์';
//* Top แก้ 6-08-2010 
$lang['common']['fileCreateError']='ไม่สามารถสร้างไฟล์ได้';
$lang['common']['illegalCharsError']='ชื่อมีข้อความหรืออักขระที่ไม่สามารถใช้งานได้ %s';
$lang['common']['month']= 'เดือน';
$lang['common']['strMonths']= 'เดือน';
$lang['common']['system']='ระบบ';
$lang['common']['goAlreadyStarted']='{product_name} ทำงานอยู่แล้ว ต้องการโหลดการทำงานของ {product_name} สามารถปิดหน้าต่างหรือแท็ปนี้ ระบบจะทำงานต่อไปตามปกติ';
$lang['common']['reminder']='เตือนความจำ';
$lang['common']['unknown']='ไม่ทราบ';
$lang['common']['time']='เวลา';
$lang['common']['dontChangeAdminsPermissions']='ไม่สามารถเปลี่ยนสิทธิ์ของกลุ่ม admin';
$lang['common']['dontChangeOwnersPermissions']='ไม่สามารถเปลี่ยนสิทธิ์ของตัวเองได้';
$lang['common']['running_sys_upgrade']='ระบบต้องการอับเดท';
$lang['common']['sys_upgrade_text']='กรุณารอสักครู่ ระบบกำลังบันทึก Log';
$lang['common']['click_here_to_contine']='คลิ๊กที่นี่เพื่อทำงานต่อไป';
$lang['common']['parentheses_invalid_error']='ข้อมูลในวงเล็บไม่ถูกต้อง กรุณาแก้ไข';
$lang['common']['nReminders']='%s แจ้งเตือน';
$lang['common']['oneReminder']='1 แจ้งเตือน';
$lang['common']['youHaveReminders']='คุณมี %s ใน %s.';
$lang['common']['createdBy']='สร้างโดย';
$lang['common']['none']='ไม่';
$lang['common']['alert']='แจ้งเตือน';
$lang['common']['theFolderAlreadyExists']='โพล์เดอร์ชื่อนี้มีอยู่แล้ว';
