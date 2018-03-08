
<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of {product_name}. You should have received a copy of the
 * {product_name} license along with {product_name}. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: vi.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Dat Pham <datpx@fab.vn> +84907382345
 */
 
require($GO_LANGUAGE->get_fallback_base_language_file('common'));

$lang['common']['about']='Phiên bản: {version}<br />
<br />
Copyright (c) 2003-{current_year}, {company_name}<br />
All rights reserved.<br />
This program is protected by copyright law and the {product_name} license.<br />
';

$lang['common']['totals']='Tổng';
$lang['common']['printPage']='Trang %s / %s';

$lang['common']['htmldirection']= 'ltr';

$lang['common']['quotaExceeded']='Ổ cứng bạn đã hết, xin dọn bớt các file hoặc nâng cấp thêm';
$lang['common']['errorsInForm'] = 'Có lỗi trên form, liên hệ nhà phát triển để sửa';

$lang['common']['moduleRequired']='Cần module %s cho chức năng này';

$lang['common']['loadingCore']= 'Tải hệ thống';
$lang['common']['loadingLogin'] = 'Tải form đăng nhập';
$lang['common']['renderInterface']='Dựng giao diện';
$lang['common']['loadingModules']='Tải các module';
$lang['common']['loadingModule'] = 'Tải module';

$lang['common']['loggedInAs'] = "Đăng nhập: ";
$lang['common']['search']='Tìm kiếm';
$lang['common']['settings']='Thiết lập';
$lang['common']['adminMenu']='menu quản trị';
$lang['common']['startMenu']='Menu chương trình';
$lang['common']['help']='Trợ giúp';
$lang['common']['logout']='Thoát';
$lang['common']['badLogin'] = 'Sai tên người dùng hoặc mật khẩu';
$lang['common']['badPassword'] = 'Bạn nhập người dùng hoặc mật khẩu không đúng';

$lang['common']['passwordMatchError']='Mật khẩu không hợp lệ';
$lang['common']['accessDenied']='Không được truy cập';
$lang['common']['saveError']='Lỗi khi lưu dữ liệu';
$lang['common']['deleteError']='Lỗi khi xóa dữ liệu';
$lang['common']['selectError']='Lỗi khi đọc dữ liệu';
$lang['common']['missingField'] = 'Bạn chưa điền đầy đủ các trường yêu cầu';
$lang['common']['invalidEmailError']='Địa chỉ email không hợp lệ';
$lang['common']['invalidDateError']='Bạn nhập sai ngày';
$lang['common']['noFileUploaded']='File không được tải';
$lang['common']['error']='Lỗi';
$lang['common']['fileCreateError']='Không tạo được file';
$lang['common']['illegalCharsError']='Tên chứa các ký tự không hợp lệ: %s';

$lang['common']['salutation']='Xin chào';
$lang['common']['firstName'] = 'Tên';
$lang['common']['lastName'] = 'Họ';
$lang['common']['middleName'] = 'Đệm';
$lang['common']['sirMadam']['M'] = 'ông';
$lang['common']['sirMadam']['F'] = 'bà';
$lang['common']['initials'] = 'Khởi tạo';
$lang['common']['sex'] = 'Giới tính';
$lang['common']['birthday'] = 'Ngày sinh';
$lang['common']['sexes']['M'] = 'Nam';
$lang['common']['sexes']['F'] = 'Nữ';
$lang['common']['title'] = 'tiêu đề';
$lang['common']['addressNo'] = 'Địa chỉ 2';
$lang['common']['workAddressNo'] = 'Địa chỉ 2 (làm việc)';
$lang['common']['postAddress'] = 'Địa chỉ (bưu điện)';
$lang['common']['postAddressNo'] = 'Địa chỉ 2 (Bưu điện)';
$lang['common']['postCity'] = 'Thành phố (dấu bưu điện)';
$lang['common']['postState'] = 'Miền (bưu điện)';
$lang['common']['postCountry'] = 'Country (bưu điện)';
$lang['common']['postZip'] = 'ZIP/Postal (bưu điện)';
$lang['common']['visitAddress'] = 'Địa chỉ thường trú';
$lang['common']['postAddressHead'] = 'Địa chỉ theo dấu bưu điện';
$lang['common']['name'] = 'Tên';
$lang['common']['name2'] = 'Tên 2';
$lang['common']['user'] = 'Người dùng';
$lang['common']['username'] = 'Người dùng';
$lang['common']['password'] = 'Mật khẩu';
$lang['common']['authcode'] = 'Mã xác nhận';
$lang['common']['country'] = 'Nước';
$lang['common']['address_format']='Định dạng địa chỉ';
$lang['common']['state'] = 'Miền';
$lang['common']['city'] = 'Thành phố/tỉnh';
$lang['common']['zip'] = 'ZIP/Postal';
$lang['common']['address'] = 'Địa chỉ';
$lang['common']['email'] = 'E-mail';
$lang['common']['phone'] = 'Điện thoại';
$lang['common']['workphone'] = 'Điện thoại (làm việc)';
$lang['common']['cellular'] = 'Di động';
$lang['common']['company'] = 'Công ty';
$lang['common']['department'] = 'Phòng ban';
$lang['common']['function'] = 'Chức vụ';
$lang['common']['question'] = 'Câu hỏi bí mật';
$lang['common']['answer'] = 'Trả lời';
$lang['common']['fax'] = 'Fax';
$lang['common']['workFax'] = 'Fax (work)';
$lang['common']['homepage'] = 'Trang chủ';
$lang['common']['workAddress'] = 'Địa chỉ (cơ quan)';
$lang['common']['workZip'] = 'ZIP/Postal (cơ quan)';
$lang['common']['workCountry'] = 'Nước (làm việc)';
$lang['common']['workState'] = 'Miền (work)';
$lang['common']['workCity'] = 'Thành phố/tỉnh (làm việc)';
$lang['common']['today'] = 'Hôm nay';
$lang['common']['tomorrow'] = 'Ngày mai';

$lang['common']['SearchAll'] = 'Các trường';
$lang['common']['total'] = 'tổng';
$lang['common']['results'] = 'kết quả';


$lang['common']['months'][1]='Tháng 1';
$lang['common']['months'][2]='Tháng 2';
$lang['common']['months'][3]='Tháng 3';
$lang['common']['months'][4]='Tháng 4';
$lang['common']['months'][5]='Tháng 5';
$lang['common']['months'][6]='Tháng 6';
$lang['common']['months'][7]='Tháng 7';
$lang['common']['months'][8]='Tháng 8';
$lang['common']['months'][9]='Tháng 9';
$lang['common']['months'][10]='Tháng 10';
$lang['common']['months'][11]='Tháng 11';
$lang['common']['months'][12]='Tháng 12';

$lang['common']['short_days'][0]="CN";
$lang['common']['short_days'][1]="Hai";
$lang['common']['short_days'][2]="Ba";
$lang['common']['short_days'][3]="Tư";
$lang['common']['short_days'][4]="Năm";
$lang['common']['short_days'][5]="Sáu";
$lang['common']['short_days'][6]="Bảy";


$lang['common']['full_days'][0] = "Chủ nhật";
$lang['common']['full_days'][1] = "Thứ 2";
$lang['common']['full_days'][2] = "Thứ 3";
$lang['common']['full_days'][3] = "Thứ 4";
$lang['common']['full_days'][4] = "Thứ 5";
$lang['common']['full_days'][5] = "Thứ 6";
$lang['common']['full_days'][6] = "Thứ 7";

$lang['common']['default']='Mặc định';
$lang['common']['description']='Mô tả';
$lang['common']['date']='Ngày';

$lang['common']['default_salutation']['M']='Thưa quý ông';
$lang['common']['default_salutation']['F']='Thưa quý bà';
$lang['common']['default_salutation']['unknown']='Thưa quý ông bà';
$lang['common']['dear']='Thưa';

$lang['common']['mins'] = 'phút';
$lang['common']['hour'] = 'giờ';
$lang['common']['hours'] = 'giờ';
$lang['common']['day'] = 'ngày';
$lang['common']['days'] = 'ngày';
$lang['common']['week'] = 'tuần';
$lang['common']['weeks'] = 'tuần';
$lang['common']['month'] = 'tháng';
$lang['common']['strMonths'] = 'tháng';

$lang['common']['group_everyone']='Tất cả';
$lang['common']['group_admins']='Quản trị';
$lang['common']['group_internal']='Nội bộ';

$lang['common']['admin']='Quản trị hệ thống';

$lang['common']['beginning']='Xin chào';

$lang['common']['max_emails_reached']= "Số lượng tối đa email  %s / %s trong một ngày đã đầy";
$lang['common']['usage_stats']='Dung lượng sử dụng/ %s';
$lang['common']['usage_text']='Cài đặt này đang sử dụng';

$lang['common']['database']='Cơ sở dữ liệu';
$lang['common']['files']='Files';
$lang['common']['email']='E-mail';
$lang['common']['total']='Tổng';


$lang['common']['confirm_leave']='Nếu bạn thoát một số dữ liệu sẽ chưa được lưu';
$lang['common']['dataSaved']='Dữ liệu đã được lưu';

$lang['common']['uploadMultipleFiles'] = 'Nhấn nút \'Duyệt\' để chọn files hoặc thư mục từ máy tính, nhấn  \'Tải\' để tải dữ liệu. Cửa sổ này tự đóng khi xong';


$lang['common']['loginToGO']='Nhấn vào đây để đăng nhập';
$lang['common']['links']='Liên kết';
$lang['common']['GOwebsite']='{product_name} website';
$lang['common']['GOisAProductOf']='<i>{product_name}</i> là sản phẩm của <a href="http://www.intermesh.nl/en/" target="_blank">Intermesh</a>';

$lang['common']['yes']='Có';
$lang['common']['no']='Không';

$lang['common']['system']='Hệ thống';

$lang['common']['goAlreadyStarted']='{product_name} đã chạy. Màn hình yêu cầu đã nạp vào {product_name}. Bạn có thể đóng cửa sổ hoặc bảng này để tiếp tục làm việc';
$lang['common']['no']='Không';

$lang['commmon']['logFiles']='File nhật ký';

$lang['common']['reminder']='Nhắc nhở';
$lang['common']['unknown']='Không biết';
$lang['common']['time']='Thời gian';

$lang['common']['dontChangeAdminsPermissions']='Bạn không thể thay quyền của nhóm quản trị';
$lang['common']['dontChangeOwnersPermissions']='Bạn không thể thay quyền của người sở hữu';


$lang['common']['running_sys_upgrade']='Chạy cập nhật hệ thống cần thiết';
$lang['common']['sys_upgrade_text']='Xin đợi một lát. Tất cả thông tin sẽ được ghi nhận';
$lang['common']['click_here_to_contine']='Nhấn vào đây để tiếp tục';
$lang['common']['parentheses_invalid_error']='Câu truy vấn không đúng, xin hãy liên hệ người phát triển';


$lang['common']['nReminders']='%s nhắc nhở';
$lang['common']['oneReminder']='1 nhắc nhở';

//Example: you have 1 reminders in {product_name}.
$lang['common']['youHaveReminders']='Bạn có %s / %s.';

$lang['common']['createdBy']='Người tạo';
$lang['common']['none']='Không';
$lang['common']['alert']='Cảnh báo';
$lang['common']['theFolderAlreadyExists']='Thư mục đã có';

$lang['common']['other']='khác';
$lang['common']['copy']='Sao chép';

$lang['common']['upload_file_to_big']='File bạn tải lớn hơn dung lượng cho phép là %s.';