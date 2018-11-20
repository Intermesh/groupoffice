<?php


$l['already_accepted']='U heeft deze afspraak al geaccepteerd';
$l['name'] = 'Agenda';
$l['description'] = 'Agenda module; Iedere gebruiker kan afspraken toevoegen, bewerken of verwijderen. Ook kunnen afspraken van andere gebruikers worden ingezien en als het nodig is aangepast worden.';
$l['groupView'] = 'Groepsoverzicht';
$l['event']='Afspraak';
$l['exceptionNoCalendarID'] = 'FATAAL: Geen agenda ID!';
$l['allTogether'] = 'Samen';
$l['invited']='U bent uitgenodigd voor de volgende afspraak';
$l['acccept_question']='Accepteert u de uitnodiging?';
$l['accept']='Accepteren';
$l['decline']='Afwijzen';
$l['bad_event']='De afspraak bestaat niet meer';
$l['subject']='Onderwerp';
$l['statuses']['NEEDS-ACTION']= 'Heeft actie nodig';
$l['statuses']['ACCEPTED']= 'Geaccepteerd';
$l['statuses']['DECLINED']= 'Afgewezen';
$l['statuses']['TENTATIVE']= 'Voorlopig';
$l['statuses']['DELEGATED']= 'Gedelegeerd';
$l['statuses']['COMPLETED']= 'Afgerond';
$l['statuses']['IN-PROCESS']= 'Bezig';
$l['statuses']['CONFIRMED'] = 'Bevestigd';
$l['statuses']['CANCELLED'] = 'Geannuleerd';

$l['accept_mail_subject']= 'Uitnodiging voor \'%s\' geaccepteerd';
$l['accept_mail_body']= '%s heeft uw uitnodiging geaccepteerd voor:';
$l['decline_mail_subject']= 'Uitnodiging voor \'%s\' afgewezen';
$l['decline_mail_body']= '%s heeft uw uitnodiging afgewezen voor:';

$l['not_invited']='U bent niet uitgenodigd voor deze gebeurtenis. U moet wellicht inloggen als een andere gebruiker.';
$l['accept_title']='Geaccepteerd';
$l['accept_confirm']='De eigenaar zal op de hoogte gebracht worden van uw acceptatie voor deze gebeurtenis';
$l['decline_title']='Afgewezen';
$l['decline_confirm']='De eigenaar zal op de hoogte gebracht worden van uw afwijzing voor deze gebeurtenis';
$l['cumulative']='Ongeldige herhaling. De volgende herhaling mag niet plaatsvinden voor de vorige is geeindigd.';
$l['private']='Privé';
$l['import_success']='%s afspraken werden geïmporteerd';
$l['printTimeFormat']='Van %s tot %s';
$l['printLocationFormat']=' op locatie "%s"';
$l['printPage']='Pagina %s van %s';
$l['printList']='Afsprakenlijst';
$l['printAllDaySingle']='Hele dag';
$l['printAllDayMultiple']='Hele dag van %s tot %s';
$l['open_resource']='Reservering openen';
$l['resource_mail_subject']='Hulpmiddel \'%s\' gereserveerd voor \'%s\' op \'%s\'';//%s is resource name, %s is event name, %s is start date;
$l['resource_confirmed_mail_body']='%s heeft hulpmiddel \'%s\' gereserveerd en de boeking geaccordeerd. U beheert dit hulpmiddel. Gebruik de onderstaande link als u deze boeking wilt weigeren.';
$l['resource_mail_body']='%s heeft hulpmiddel \'%s\' gereserveerd. U beheert dit hulpmiddel. Open de reservering om deze te accepteren of weigeren.';
$l['resource_modified_mail_subject']='Hulpmiddel \'%s\' reservering voor \'%s\' op \'%s\' gewijzigd';//%s is resource name, %s is event name, %s is start date;
$l['resource_modified_mail_body']='%s heeft de reservering voor hulpmiddel \'%s\' gewijzigd. U beheert dit hulpmiddel. Open de reservering om deze te accepteren of weigeren.';
$l['your_resource_modified_mail_subject']='Reservering voor \'%s\' op \'%s\' in status \'%s\' is gewijzigd';//is resource name, %s is start date, %s is status;
$l['your_resource_modified_mail_body']='%s heeft uw reservering voor \'%s\' gewijzigd.';
$l['your_resource_accepted_mail_subject']='Reservering voor \'%s\' op \'%s\' is geaccepteerd';//%s is resource name, %s is start date;
$l['your_resource_accepted_mail_body'] = '%s heeft uw boeking voor hulpmiddel \'%s\' geaccepteerd.';
$l['your_resource_declined_mail_subject']='Reservering voor \'%s\' op \'%s\' is geweigerd';//%s is resource name, %s is start date;
$l['your_resource_declined_mail_body'] = '%s heeft uw boeking voor hulpmiddel \'%s\' geweigerd.';
$l['birthday_name']='Verjaardag: {NAME}';
$l['birthday_desc']='{NAME} is vandaag {AGE} geworden';
$l['unauthorized_participants_write']='U heeft onvoldoende toegangsrechten om afspraken te plannen voor de volgende gebruikers:<br /><br />{NAMES}<br /><br />Wellicht wilt u nog een uitnodiging versturen zodat zij de afspraak kunnen accepteren en inplannen.';
$l['noCalSelected'] = 'Er is geen agenda geselecteerd voor deze overzicht. Selecteer minstens een agenda bij Beheer.';

$l['rightClickToCopy']='Gebruik de rechtermuisknop om de koppelingslocatie te kopiëren';
$l['invitation']='Uitnodiging';
$l['invitation_update']='Bijgewerkte uitnodiging';
$l['cancellation']='Annulering';
$l['cancelMessage']='De volgende afspraak waar u voor uitgenodigd bent is geannuleerd';
$l['non_selected'] = 'in niet-geselecteerde agenda';
$l['linkIfCalendarNotSupported']='Gebruik onderstaande links alleen wanneer uw mail programma geen agenda functies ondersteunt.';
$l["appointment"]= 'Afspraak';
$l["appointments"]= 'Afspraken';
$l["recurrence"]= 'Herhaling';
$l["options"]= 'Opties';
$l["rangeRecurrence"]= 'Bereik van herhaling';
$l["repeatForever"]= 'Altijd herhalen';
$l["repeatUntilDate"]= 'Herhalen tot';
$l["repeatCount"]= 'Herhaal';
$l['times'] = 'keer';
$l["repeatEvery"]= 'Herhaal iedere';
$l["repeatUntil"]= 'Herhalen tot';
$l["busy"]= 'Toon als bezet';
$l["allDay"]= 'Tijd is niet van toepassing';
$l["navigation"]= 'Navigatie';
$l["oneDay"]= '1 Dag';
$l["fiveDays"]= '5 Dagen';
$l["sevenDays"]= '7 Dagen';$l['calNotDeletedDefault'] = "Not deleted!\nThis is the default calendar of user :username";
$l["month"]= 'Maand';
$l["recurringEvent"]= 'Herhalende afspraak';
$l["deleteRecurringEvent"]= 'Wilt u een enkele afspraak of alle afspraken van deze herhalende afspraak verwijderen?';
$l["singleOccurence"]= 'Enkele afspraak';
$l["entireSeries"]= 'Alle afspraken';
$l["calendar"]= 'Agenda';
$l["calendars"]= 'Agenda\'s';
$l["views"]= 'Overzichten';
$l["administration"]= 'Beheer';
$l["needsAction"]= 'Actie nodig';
$l["accepted"]= 'Geaccepteerd';
$l["declined"]= 'Afgewezen';
$l["tentative"]= 'Voorlopige';
$l["delegated"]= 'Gedelegeerde';
$l["noRecurrence"]= 'Geen herhaling';
$l["notRespondedYet"]= 'Nog niet gereageerd';
$l["days"]= 'dagen';
$l["weeks"]= 'weken';
$l["monthsByDate"]= 'maanden op datum';
$l["monthsByDay"]= 'maanden op dag';
$l["years"]= 'jaren';
$l["atDays"]= 'Op dagen';
$l["noReminder"]= 'Geen herinnering';
$l["reminder"]='Herinnering';
$l["participants"]= 'Deelnemers';
$l["checkAvailability"]= 'Controleer beschikbaarheid';
$l["sendInvitation"]= 'Uitnodiging verzenden';
$l["emailSendingNotConfigured"]= 'Het verzenden van e-mail is niet ingesteld of niet geïnstalleerd.';
$l["privateEvent"]= 'Privé';
$l["noInformationAvailable"]= 'Geen informatie beschikbaar';
$l["noParticipantsToDisplay"]= 'Geen deelnemers om te tonen';
$l["previousDay"]= 'Vorige dag';
$l["nextDay"]= 'Volgende dag';
$l["noAppointmentsToDisplay"]= 'Geen afspraken om te tonen';
$l["selectCalendar"]= 'Selecteer agenda';
$l["selectCalendarForAppointment"]= 'Selecteer de agenda om deze afspraak in te zetten';
$l["closeWindow"]= 'De afspraak is geaccepteerd en ingepland. U kunt dit venster nu sluiten.';
$l["list"]='Lijst';
$l["editRecurringEvent"]='Wilt u alleen dit exemplaar bewerken of alle afspraken van deze herhalende reeks?';
$l["selectIcalendarFile"]='Selecteer een icalendar (*.ics) bestand';
$l["location"]='Locatie';
$l["startsAt"]='Start op';
$l["endsAt"]='Eindigt op';
$l["eventDefaults"]='Standaard instellingen voor afspraken';
$l["importToCalendar"]='Voeg afspraak direct toe aan de agenda\'s';
$l["default_calendar"]='Standaard agenda';
$l["status"]='Status';
$l["resource_groups"]='Hulpmiddel groepen';
$l["resource_group"]='Hulpmiddel groep';
$l["resources"]='Hulpmiddelen';
$l["resource"]='Hulpmiddel';
$l["calendar_group"]='Agenda groep';
$l["admins"]='Beheerders';
$l["no_group_selected"]='U heeft het formulier onvolledig of foutief ingevuld. U dient een groep te selecteren voor dit hulpmiddel.';
$l["visibleCalendars"]='Zichtbare agenda\'s';
$l["visible"]='Zichtbaar';
$l["group"]='Groep';
$l["no_status"]='Nieuw';
$l["no_custom_fields"]='Er zijn geen extra opties beschikaar.';
$l["show_bdays"]='Toon verjaardagen uit adresboek';
$l["show_tasks"]='Toon taken uit takenlijsten';
$l["myCalendar"]='Mijn agenda';
$l["merge"]='Samenvoegen';
$l["ownColor"]= 'Geef elke agenda een unieke kleur';
$l["ignoreConflictsTitle"]= 'Conflict negeren?';
$l["ignoreConflictsMsg"]= 'Deze agenda-item conflicteert met een andere. Toch opslaan?';
$l["resourceConflictTitle"]= 'Hulpmiddelenconflict';
$l["resourceConflictMsg"]= 'Een of meer hulpmiddelen van deze agenda-item worden al elders gebruikt op hetzelfde moment:</br>';
$l["view"]= 'Overzicht';
$l["calendarsPermissions"]='Agenda\'s toegangsrechten';
$l["resourcesPermissions"]='Hulpmiddelen toegangsrechten';
$l["categories"]='Categorieën';
$l["category"]='Categorie';
$l["globalCategory"]='Globale categorie';
$l["globalCategories"]='Globale categorieen';
$l["selectCategory"]='Selecteer categorie';
$l["duration"]='Duur';
$l["move"]='Verplaatsen';
$l["showInfo"]='Informatie';
$l["copyEvent"]='Kopieer afspraak';
$l["moveEvent"]='Verplaats afspraak';
$l["eventInfo"]='Afspraak informatie';
$l["isOrganizer"]='Organisator';
$l["sendInvitationInitial"]='Wilt u uitnodigingen voor de bijeenkomst naar de deelnemers sturen?';
$l["sendInvitationUpdate"]='Wilt u de vernieuwde informatie over de bijeenkomst naar de deelnemers sturen?';
$l["sendCancellation"]='Wilt u alle deelnemers een annuleringsbericht sturen?';
$l["forthcomingAppointments"]='Toekomstige afspraken';
$l["pastAppointments"]='Oude afspraken';
$l["quarterShort"]= 'K';
$l["globalsettings_templatelabel"]= 'Naam template';
$l["globalsettings_allchangelabel"]= 'Bestaande agenda\'s hernoemen?';
$l["globalsettings_renameall"]= 'Weet u zeker dat u alles wilt hernoemen?';
$l["publishICS"]='Publiceer iCalendar bestand voor afgelopen maand en toekomstige afspraken. Let op! De agenda wordt voor iedereen leesbaar.';
$l["addTimeRegistration"]='Invoeren als tijdsregistratie';
$l["showNotBusy"]='Nieuwe boekingen niet als bezet tonen';
$l["addressbook"]='Adresboek';
$l["confirmed"]= 'Bevestigd';
$l['eventAccepted']='U heeft de afspraak geaccepteerd';
$l['eventScheduledIn']='De afspraak is opgeslagen in uw agenda %s met status %s.';
$l['eventDeclined']="U heeft de afspraak afgewezen";
$l['eventUpdatedIn']='De afspraak in uw agenda %s is bijgewerkt met status %s';
$l['updateReponses']["ACCEPTED"]='%s heeft de afspraak %s geaccepteerd';
$l['updateReponses']["DECLINED"]='%s heeft de afspraak %s geweigerd';
$l['updateReponses']["TENTATIVE"]='%s weet nog niet of hij/zij afspraak %s accepteert';
$l['directUrl']='Directe URL';
$l['cantRemoveOrganizer']="U kunt de organisator niet verwijderen";
$l['calendarColor']='Agendakleur';
$l["sendEmailParticipants"]= 'Email opstellen voor deelnemers';

$l['errorOrganizerOnly'] = 'Alleen de organisator mag deze afspraak bewerken.';
$l['errorOrganizerOnlyTitle'] = 'U bent niet de organisator';

$l['eventDeleted']="De afspraak is verwijderd uit uw agenda";

$l['attendance']='Deelname';
$l['organizer']='Organisator';
$l['notifyOrganizer']="Breng de organisator op de hoogte over mijn beslissing";

$l['iWillAttend']="Ik ben aanwezig";
$l['iMightAttend']="Misschien ben ik aanwezig";
$l['iWillNotAttend']="Ik ben niet aanwezig";
$l['iWillDecideLater']="Ik moet nog beslissen";

$l['eventUpdated']="De volgende afspraak is door de organisator bijgewerkt";

$l['notifyCancelParticipants']='Wilt u de deelnemers per e-mail op de hoogte brengen van de annulering?';
$l['notifyCancelOrganizer']='Wilt u de organisator op de hoogte brengen over dat u niet aanwezig zult zijn?';
$l['notifyParticipants']='Deelnemers op de hoogte stellen?';
$l['sendNotificationTitle']='Bericht versturen?';
$l['sendNotification']='Wilt u de deelnemers per e-mail op de hoogte brengen?';
$l['sendUpdateNotification']='Wilt u de deelnemers per e-mail op de hoogte brengen over de verandering(en)?';
$l['months']= 'maanden';
$l['appointment']= 'Afspraak';

$l['openCalendar']='Open agenda';
$l['createPermission']="Toegang aanmaken";

$l['show_holidays']="Feestdagen tonen";

$l['participant']='Deelnemer';
$l['clickForAttendance']='Geef aan of u deelneemt aan deze afspraak';


$l['viewDay']='Dag';
$l['viewMorning']='Ochtend';
$l['viewAfternoon']='Middag';
$l['viewEvening']='Avond';

$l['show_completed_tasks'] = "Toon afgeronde taken";

$l['eventNotSavedSubject'] = 'Afspraak "%event" niet opgeslagen in agenda "%cal"';
$l['eventNotSavedBody'] = 'Deze boodschap komt van uw %goname agenda. %goname heeft een poging gedaan om een afspraak genaamd "%event" met starttijd %starttime te importeren van een externe agenda naar agenda "%cal", maar kon dat niet omdat de afspraak fouten bevatte. De afspraak is wellicht nog terug te vinden in de externe agenda.'.
								"\r\n\r\n".'Het volgende is de foutmelding:'."\r\n".'%errormessage';

$l['allTogetherForParticipants'] = 'Alle deelnemers samen';
$l['allTogetherForResources'] = 'Alle hulpmiddelen samen';

$l['usedResources'] = 'Gebruikte hulpmiddelen';

$l['cmdPrintView'] = 'Print huidige weergave';
$l['cmdPrintCategoryCount'] = 'Print aantal per categorie';
$l['startDate'] = 'Start datum';
$l['endDate'] = 'Eind datum';
$l['eventsPerCategoryCount'] = 'Aantal afspraken per categorie';
$l['nextMonth'] = 'Volgende maand';
$l['previousMonth'] = 'Vorige maand';
$l['till'] = 'tot';
$l['total'] = 'Totaal';

$l['sendNotificationToNewParticipants']='Wilt u alleen de zojuist toegevoegde deelnemers per e-mail op de hoogte brengen van deze afspraak?';
$l['newParticipants']='Nieuwe deelnemers';
$l['allParticipants']='Alle deelnemers';
$l['noParticipants']='Geen deelnemers';
$l['cannotHandleInvitation']='De agenda die hoort bij het email account heet "%s" en u heeft daar geen schrijfrechten toe. Omdat de afspraak in die agenda zit, is haar status nu niet gewijzigd.';
$l['cannotHandleInvitation2']='Kon de afspraak niet aanpassen, omdat u te weinig toegangsrechten heeft tot de agenda die hoort bij het email account (agenda: "%s"). Omdat de afspraak in die agenda zit, is haar status nu niet gewijzigd.';

$l['tooltip'] = 'Tooltip tekst';

$l['moveEventResourceError'] = 'Kan afspraak niet verzetten omdat de volgende hulpmiddelen dan niet beschikbaar zijn:';

$l['resourceUsedIn'] = 'Hulpmiddel gebruikt in';

$l['exportAsIcs'] = 'Exporteren als ICS';

$l['noDefaultCalendar'] = "Je hebt geen standaard agenda ingesteld. Kies deze in het Instellingen paneel.";
$l['calNotDeletedDefault'] = "Niet verwijderd!\nDit is de standaard agenda van gebruiker :username";
$l['last'] = 'Laatste';
$l['thisAndFuture'] = 'Deze en toekomstige';