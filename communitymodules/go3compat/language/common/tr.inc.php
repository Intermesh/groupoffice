<?php
require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('common'));

$lang['common']['about']='Version: {version}<br />
<br />
Copyright (c) 2003-{current_year}, {company_name}<br />
All rights reserved.<br />
This program is protected by copyright law and the {product_name} license.<br />
';

$lang['common']['htmldirection']= 'ltr';

$lang['common']['quotaExceeded']='Disk içerinde yeterince boş yeriniz artık yok. Lütfen bazı dosyalarınızı siliniz veya Servis Sağlayıcınızdan kotanızı arttırmasını isteyiniz.';
$lang['common']['errorsInForm'] = 'Form içersinde hatalar vardı. Düzeltip tekrar deneyin lütfen.';

$lang['common']['moduleRequired']='Bu fonksiyon için %s modülü gereklidir';

$lang['common']['loadingCore']= 'Ana sistem yükleniyor';
$lang['common']['loadingLogin'] = 'Giriş ekranı yükleniyor';
$lang['common']['renderInterface']='Arayüz işleniyor';
$lang['common']['loadingModule'] = 'Modül yükleniyor';

$lang['common']['loggedInAs'] = "Bağlanılan kullanıcı ";
$lang['common']['search']='Ara';
$lang['common']['settings']='Ayarlar';
$lang['common']['adminMenu']='Admin menu';
$lang['common']['help']='Yardım';
$lang['common']['logout']='Çıkış';
$lang['common']['badLogin'] = 'Hatalı kullanıcı adı veya şifre';
$lang['common']['badPassword'] = 'Varolan şifreyi yanlış girdiniz';

$lang['common']['passwordMatchError']='Şifreler birbirine uymuyor';
$lang['common']['accessDenied']='Erişim engellendi';
$lang['common']['saveError']='Veriler kaydedilirken hata oluştu';
$lang['common']['deleteError']='Veriler silinirken hata oluştu';
$lang['common']['selectError']='Veriler okunmaya çalışırken hata oluştu';
$lang['common']['missingField'] = 'Tüm doldurulması zorunlu alanları doldurmadınız.';
$lang['common']['invalidEmailError']='E-posta adresi yanlış girildi';
$lang['common']['noFileUploaded']='Hiçbir dosya alınmadı';
$lang['common']['error']='Hata';

$lang['common']['salutation']='Selamlama';
$lang['common']['firstName'] = 'İsim';
$lang['common']['lastName'] = 'Soyisim';
$lang['common']['middleName'] = 'İkinci isim';
$lang['common']['sirMadam']['M'] = 'Bay';
$lang['common']['sirMadam']['F'] = 'Bayan';
$lang['common']['initials'] = 'BaşHarfler';
$lang['common']['sex'] = 'Cinsiyet';
$lang['common']['birthday'] = 'Doğum Tarihi';
$lang['common']['sexes']['M'] = 'Erkek';
$lang['common']['sexes']['F'] = 'Kadın';
$lang['common']['title'] = 'Başlık';
$lang['common']['addressNo'] = 'Ev No.';
$lang['common']['workAddressNo'] = 'Ev No.(iş)';
$lang['common']['postAddress'] = 'Adres (posta)';
$lang['common']['postAddressNo'] = 'Ev No. (posta)';
$lang['common']['postCity'] = 'City (posta)';
$lang['common']['postState'] = 'Bölge (posta)';
$lang['common']['postCountry'] = 'Ülke (posta)';
$lang['common']['postZip'] = 'Posta kodu (posta)';
$lang['common']['visitAddress'] = 'Ziyaret Adresi';
$lang['common']['postAddressHead'] = 'Posta Adresi';
$lang['common']['name'] = 'İsim';
$lang['common']['user'] = 'Kullanıcı';
$lang['common']['username'] = 'Kullanıcı Adı';
$lang['common']['password'] = 'Şifre';
$lang['common']['authcode'] = 'İzin kodu';
$lang['common']['country'] = 'Ülke';
$lang['common']['state'] = 'Bölge';
$lang['common']['city'] = 'Şehir';
$lang['common']['zip'] = 'Posta kodu';
$lang['common']['address'] = 'Address';
$lang['common']['email'] = 'E-posta';
$lang['common']['phone'] = 'Telefon';
$lang['common']['workphone'] = 'Telefon (iş)';
$lang['common']['cellular'] = 'Mobil Tel.';
$lang['common']['company'] = 'Şirket';
$lang['common']['department'] = 'Departman';
$lang['common']['function'] = 'Fonksiyon';
$lang['common']['question'] = 'Gizli soru';
$lang['common']['answer'] = 'Cevap';
$lang['common']['fax'] = 'Faks';
$lang['common']['workFax'] = 'Faks (iş)';
$lang['common']['homepage'] = 'Homepage';
$lang['common']['workAddress'] = 'Adres (iş)';
$lang['common']['workZip'] = 'Posta kodu (iş)';
$lang['common']['workCountry'] = 'Ülke (iş)';
$lang['common']['workState'] = 'Bölge (iş)';
$lang['common']['workCity'] = 'Şehir (iş)';
$lang['common']['today'] = 'Bugün';
$lang['common']['tomorrow'] = 'Yarın';

$lang['common']['SearchAll'] = 'Tüm alanlar';
$lang['common']['total'] = 'toplam';
$lang['common']['results'] = 'sonuçlar';


$lang['common']['months'][1]='Ocak';
$lang['common']['months'][2]='Şubat';
$lang['common']['months'][3]='Mart';
$lang['common']['months'][4]='Nisan';
$lang['common']['months'][5]='Mayıs';
$lang['common']['months'][6]='Haziran';
$lang['common']['months'][7]='Temmuz';
$lang['common']['months'][8]='Ağustos';
$lang['common']['months'][9]='Eylül';
$lang['common']['months'][10]='Ekim';
$lang['common']['months'][11]='Kasım';
$lang['common']['months'][12]='Aralık';

$lang['common']['short_days'][0]="Pz";
$lang['common']['short_days'][1]="Pt";
$lang['common']['short_days'][2]="Sl";
$lang['common']['short_days'][3]="Çr";
$lang['common']['short_days'][4]="Pr";
$lang['common']['short_days'][5]="Cu";
$lang['common']['short_days'][6]="Ct";


$lang['common']['full_days'][0] = "Pazar";
$lang['common']['full_days'][1] = "Pazartesi";
$lang['common']['full_days'][2] = "Salı";
$lang['common']['full_days'][3] = "Çarşamba";
$lang['common']['full_days'][4] = "Perşembe";
$lang['common']['full_days'][5] = "Cuma";
$lang['common']['full_days'][6] = "Cumartesi";

$lang['common']['default']='Varsayılan';
$lang['common']['description']='Açıklama';
$lang['common']['date']='Tarih';

$lang['common']['default_salutation']['M']='Sayın Bay';
$lang['common']['default_salutation']['F']='Sayın Bayan';
$lang['common']['default_salutation']['unknown']='Sayın Bay / Bayan';

$lang['common']['mins'] = 'Dakikalar';
$lang['common']['hour'] = 'saat';
$lang['common']['hours'] = 'saatler';
$lang['common']['day'] = 'gün';
$lang['common']['days'] = 'günler';
$lang['common']['week'] = 'hafta';
$lang['common']['weeks'] = 'haftalar';

$lang['common']['group_everyone']='Herkes';
$lang['common']['group_admins']='Yöneticiler';
$lang['common']['group_internal']='İçsel';

$lang['common']['admin']='Yönetici';

$lang['common']['beginning']='Selamlama';

$lang['common']['max_emails_reached']= "%s SMTP sunucusu için maksimum %s (günlük) E-posta sınırına ulaşılmıştır.";
$lang['common']['usage_stats']='DiskAlanı kullanımı %s başına';
$lang['common']['usage_text']='{product_name} kurulumu tarafından kullanılan';

$lang['common']['database']='Veritabanı';
$lang['common']['files']='Dosyalar';
$lang['common']['email']='E-posta';
$lang['common']['total']='Toplam';

$lang['common']['lost_password_subject']='Yeni şifre';
$lang['common']['lost_password_body']='%s,<br />
<br />
%s için yeni bir şifre istediniz.<br />
<br />
Yeni giriş detaylarınız:<br />
<br />
Kullanıcı Adı: %s<br />
Şifre: %s';

$lang['common']['lost_password_error']='Verilen E-posta adresi bulunamıyor.';
$lang['common']['lost_password_success']='E-posta adresinize yeni bir şifre gönderilmiştir.';

$lang['common']['confirm_leave']='{product_name} ten ayrılırsanız kaydetmediklerinizi kaybedeceksiniz';
$lang['common']['dataSaved']='Veri başarılı şekilde kaydedilmiştir';

$lang['common']['uploadMultipleFiles'] = 'Bilgisayarınızdan dosya ve/veya klasör seçmek için \'Göz At\' tıklayınız. Dosyaları {product_name} içersine yüklemek için \'Yükle\' tıklayınız. Veri transferi bittiğinde bu ekran otomatik olarak kapanacaktır.';
