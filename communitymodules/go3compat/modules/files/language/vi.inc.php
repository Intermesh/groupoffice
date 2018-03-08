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
 
require($GO_LANGUAGE->get_fallback_language_file('files'));
$lang['files']['name'] = 'File';
$lang['files']['description'] = 'Module quản lý fie, dùng chia sẻ các file trong đơn vị.';

$lang['link_type'][6]='File';
$lang['link_type'][17]='Thư mục';

$lang['files']['fileNotFound'] = 'Không tìm thấy file';
$lang['files']['folderExists'] = 'Thư mục đã có';
$lang['files']['filenameExists'] = 'Tên file đã có';
$lang['files']['uploadedSucces'] = 'Tải file thành công';

$lang['files']['ootextdoc']='Văn bản Open Office';
$lang['files']['wordtextdoc']='Văn bản Word';
$lang['files']['personal']='Cá nhân';
$lang['files']['shared']='Chia sẻ';

$lang['files']['general']='Chung';


$lang['files']['folder_modified_subject']='Thay đổi tới thư mục';
$lang['files']['folder_modified_body']='Bạn yêu cầu cảnh báo khi thư mục có thay đổi:

%s

Thay đổi dưới đây do %s:

%s
';

$lang['files']['modified']='Thay đổi';
$lang['files']['new']='Tạo mới';
$lang['files']['deleted']='Xóa';

$lang['files']['file']='File';
$lang['files']['folder']='Thư mục';
$lang['files']['files']='Thư mục';


$lang['files']['emptyFile']='Xóa trắng file';

$lang['files']['downloadLink'] = 'Tải đường đẫn';
$lang['files']['clickHereToDownload'] = 'Nhấn vào đây tải file theo đường dẫn';
$lang['files']['copyPasteToDownload'] = 'Nhấn vào liên kết dưới đây hoặc sao chép và dán vào trình duyệt để tải file.';
$lang['files']['possibleUntil'] = 'Có hiệu lực đến';

$lang['files']['fileNotFound']='Xin lỗi, file bạn tìm để tải không có.';