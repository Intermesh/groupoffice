<?php
/* Translator for the Greek Language: Konstantinos Georgakopoulos (kgeorga@uom.gr)*/
require($GLOBALS['GO_LANGUAGE']->get_fallback_base_language_file('common'));

$lang['common']['about']='Έκδοση: {version}<br />
<br />
Copyright (c) 2003-{current_year}, {company_name}<br />
All rights reserved.<br />
Το πρόγραμμα αυτό προστατεύεται από το νόμο περί πνευματικών δικαιωμάτων και την άδεια λογισμικού {product_name}.<br />';

$lang['common']['htmldirection']= 'ltr';

$lang['common']['quotaExceeded']='Ο διαθέσιμος χώρος σας στο δίσκο εξαντλήθηκε. Διαγράψτε μερικά αρχεία ή επικοινωνήστε με τον πάροχο σας επέκταση του διαθέσιμου χώρου';
$lang['common']['errorsInForm'] = 'Παρουσιάστηκαν κάποια λάθη στη φόρμα. Διορθώστε τα και ξαναδοκιμάστε.';

$lang['common']['moduleRequired']='Για αυτή τη λειτουργία είναι απαραίτητο το άρθρωμα %s';

$lang['common']['loadingCore']= 'Φόρτωση βασικού συστήματος';
$lang['common']['loadingLogin'] = 'Φόρτωση παράθυρου εισόδου';
$lang['common']['renderInterface']='Φωτοαπόδοση διεπαφής';
$lang['common']['loadingModule'] = 'Φόρτωση αρθρώματος';

$lang['common']['loggedInAs'] = 'Έχετε συνδεθεί σαν';
$lang['common']['search']='Αναζήτηση';
$lang['common']['settings']='Ρυθμίσεις';
$lang['common']['adminMenu']='Επιλογές Διαχειριστή';
$lang['common']['help']='Βοήθεια';
$lang['common']['logout']='Αποσύνδεση';
$lang['common']['badLogin'] = 'Λάθος όνομα χρήστη ή συνθηματικό';
$lang['common']['badPassword'] = 'Εισάγατε το τρέχων συνθηματικό λανθασμένα';

$lang['common']['passwordMatchError']='Τα συνθηματικά δεν είναι τα ίδια';
$lang['common']['accessDenied']='Άρνηση πρόσβασης';
$lang['common']['saveError']='Σφάλμα κατά την αποθήκευση των δεδομένων';
$lang['common']['deleteError']='Σφάλμα κατά την διαγραφή των δεδομένων';
$lang['common']['selectError']='Σφάλμα κατά την ανάγνωση των δεδομένων';
$lang['common']['missingField'] = 'Δεν συμπληρώσατε όλα τα απαραίτητα πεδία';
$lang['common']['invalidEmailError']='Η διεύθυνση ηλεκτρονικού ταχυδρομείου δεν ήταν έγκυρη';
$lang['common']['noFileUploaded']='Κανένα αρχείο δεν λήφθηκε';
$lang['common']['error']='Σφάλμα';

$lang['common']['salutation']='Χαιρετισμός';
$lang['common']['firstName'] = 'Όνομα';
$lang['common']['lastName'] = 'Επώνυμο';
$lang['common']['middleName'] = 'Όνομα πατρός';
$lang['common']['sirMadam']['M'] = 'κύριος';
$lang['common']['sirMadam']['F'] = 'κυρία';
$lang['common']['initials'] = 'Αρχικά';
$lang['common']['sex'] = 'Φύλο';
$lang['common']['birthday'] = 'Γενέθλια';
$lang['common']['sexes']['M'] = 'Άρρεν';
$lang['common']['sexes']['F'] = 'Θύλη';
$lang['common']['title'] = 'Τίτλος';
$lang['common']['addressNo'] = 'Αριθμός οικίας';
$lang['common']['workAddressNo'] = 'Αριθμός οικίας (εργασίας)';
$lang['common']['postAddress'] = 'Διεύθυνση (αλληλογραφίας)';
$lang['common']['postAddressNo'] = 'Αριθμός οικίας (αλληλογραφίας)';
$lang['common']['postCity'] = 'Πόλη (αλληλογραφίας)';
$lang['common']['postState'] = 'Πολιτεία (αλληλογραφίας)';
$lang['common']['postCountry'] = 'Χώρα (άλληλογραφίας)';
$lang['common']['postZip'] = 'Ταχ. Κώδικας (αλληλογραφίας)';
$lang['common']['visitAddress'] = 'Διεύθυνση επίσκεψης';
$lang['common']['postAddressHead'] = 'Διεύθυνση Αλληλογραφίας';
$lang['common']['name'] = 'Όνομα';
$lang['common']['user'] = 'Χρήστης';
$lang['common']['username'] = 'Όνομα χρήστη';
$lang['common']['password'] = 'Συνθηματικό';
$lang['common']['authcode'] = 'Κωδικός πιστοποίησης';
$lang['common']['country'] = 'Χώρα';
$lang['common']['state'] = 'Πολιτεία';
$lang['common']['city'] = 'Πόλη';
$lang['common']['zip'] = 'Ταχ. Κώδικας';
$lang['common']['address'] = 'Διεύθυνση';
$lang['common']['email'] = 'Ηλεκτρονικό Ταχυδρομείο';
$lang['common']['phone'] = 'Τηλέφωνο';
$lang['common']['workphone'] = 'Τηλέφωνο (εργασίας)';
$lang['common']['cellular'] = 'Κινητό';
$lang['common']['company'] = 'Εταιρεία';
$lang['common']['department'] = 'Τμήμα';
$lang['common']['function'] = 'Λειτουργία';
$lang['common']['question'] = 'Μυστική ερώτηση';
$lang['common']['answer'] = 'Απάντηση';
$lang['common']['fax'] = 'Φαξ';
$lang['common']['workFax'] = 'Φαξ (εργασίας)';
$lang['common']['homepage'] = 'Προσωπική ιστοσελίδα';
$lang['common']['workAddress'] = 'Διεύθυνση (εργασίας)';
$lang['common']['workZip'] = 'Ταχ. Κώδικας (εργασίας)';
$lang['common']['workCountry'] = 'Χώρα (εργασίας)';
$lang['common']['workState'] = 'Πολιτεία (εργασίας)';
$lang['common']['workCity'] = 'Πόλη (εργασίας)';
$lang['common']['today'] = 'Σήμερα';
$lang['common']['tomorrow'] = 'Αύριο';

$lang['common']['SearchAll'] = 'Όλα τα πεδία';
$lang['common']['total'] = 'σύνολο';
$lang['common']['results'] = 'αποτελέσματα';


$lang['common']['months'][1]='Ιανουάριος';
$lang['common']['months'][2]='Φεβρουάριος';
$lang['common']['months'][3]='Μάρτιος';
$lang['common']['months'][4]='Απρίλιος';
$lang['common']['months'][5]='Μάιος';
$lang['common']['months'][6]='Ιούνιος';
$lang['common']['months'][7]='Ιούλιος';
$lang['common']['months'][8]='Αύγουστος';
$lang['common']['months'][9]='Σεπτέμβριος';
$lang['common']['months'][10]='Οκτώβριος';
$lang['common']['months'][11]='Νοέμβριος';
$lang['common']['months'][12]='Δεκέμβριος';

$lang['common']['short_days'][0]="Κυ";
$lang['common']['short_days'][1]="Δε";
$lang['common']['short_days'][2]="Τρ";
$lang['common']['short_days'][3]="Τε";
$lang['common']['short_days'][4]="Πε";
$lang['common']['short_days'][5]="Πα";
$lang['common']['short_days'][6]="Σα";


$lang['common']['full_days'][0] = "Κυριακή";
$lang['common']['full_days'][1] = "Δευτέρα";
$lang['common']['full_days'][2] = "Τρίτη";
$lang['common']['full_days'][3] = "Τετάρτη";
$lang['common']['full_days'][4] = "Πέμπτη";
$lang['common']['full_days'][5]= "Παρασκευή";
$lang['common']['full_days'][6] = "Σάββατο";

$lang['common']['default']='Προεπιλογή';
$lang['common']['description']='Περιγραφή';
$lang['common']['date']='Ημερομηνία';

$lang['common']['default_salutation']['M']='Αγαπητέ Κύριε';
$lang['common']['default_salutation']['F']='Αγαπητή Κυρία';
$lang['common']['default_salutation']['unknown']='Αγαπητέ Κύριε / Κυρία';

$lang['common']['mins'] = 'Λεπτά';
$lang['common']['hour'] = 'ώρα';
$lang['common']['hours'] = 'ώρες';
$lang['common']['day'] = 'ημέρα';
$lang['common']['days'] = 'ημέρες';
$lang['common']['week'] = 'εβδομάδα';
$lang['common']['weeks'] = 'εβδομάδες';

$lang['common']['group_everyone']='Όλοι';
$lang['common']['group_admins']='Διαχειριστές';
$lang['common']['group_internal']='Εσωτερικά';

$lang['common']['admin']='Διαχειριστής';

$lang['common']['beginning']='Χαιρετισμός';

$lang['common']['max_emails_reached']= "Φθάσατε τον μέγιστο αριθμό e-mail (%s ανά ημέρα) για τον διακομιστή SMTP %s.";
$lang['common']['usage_stats']='Χρήση δίσκου ανά %s';
$lang['common']['usage_text']='Αυτή η εγκατάσταση του {product_name} χρησιμοποιεί';

$lang['common']['database']='Βάση δεδομένων';
$lang['common']['files']='Αρχεία';
$lang['common']['email']='Ηλεκτρονικό Ταχυδρομείο';
$lang['common']['total']='Σύνολο';

$lang['common']['lost_password_subject']='Νέο συνθηματικό';
$lang['common']['lost_password_body']='%s,<br />
<br />
Ζητήσατε ένα νέο συνθηματικό για %s.<br />
<br />
Τα νέα στοιχεία εισόδου σας είναι:<br />
<br />
Όνομα χρήστη: %s<br />
Συνθηματικό:  %s';

$lang['common']['lost_password_error']='Η δοθείσα διεύθυνση ηλεκτρονικού ταχυδρομείου δεν ήταν δυνατόν να βρεθεί.';
$lang['common']['lost_password_success']='Ένα νέο συνθηματικό έχει σταλεί στην διεύθυνση ηλεκτρονικού ταχυδρομείου σας.';

$lang['common']['confirm_leave']='Εάν βγείτε από την εφαρμογή {product_name} θα χάσετε πιθανώς μη αποθηκευμένες αλλαγές';
$lang['common']['dataSaved']='Τα δεδομένα αποθηκεύθηκαν με επιτυχία';

$lang['common']['uploadMultipleFiles'] = 'Κάντε κλικ στο \'Περιήγηση\' για να επιλέξτε αρχεία ή/και καταλόγους από τον υπολογιστή σας. Κάντε κλίκ στο \'Ανέβασμα\' για να μεταφέρετε τα αρχεία στην εφαρμογή {product_name}. Το παράθυρο αυτό θα κλείσει αυτόματα όταν ολοκληρωθεί η μεταφορά.';