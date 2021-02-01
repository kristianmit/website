<?php
    require_once("../../include/functions.inc.php");

    require_once("../../include/PHPMailer.php");
    require_once("../../include/SMTP.php");
    require_once("../../include/Exception.php");
    
    /*
    * Name
    * Klasse
    * Mail
    * Telefon
    * Nachhilfe
    * Fächer
    * Zeit
    * Einzelnachhilfe
    * Bemerkung
    */

    $message = [];
    // prüfen, ob alle kritischen Parameter übergeben wurden
    if (isset($_POST['name']) === false
        or
        isset($_POST['klasse']) === false
        or
        isset($_POST['mail']) === false
        or
        isset($_POST['telefon']) === false
        or
        isset($_POST['nachhilfe']) === false
        or
        isset($_POST['fach']) === false
        or
        isset($_POST['zeit']) === false
        or
        isset($_POST['einzelnachhilfe']) === false) {
            $message = array(
                'success' => 'false',
                'message' => 'Nicht alle benötigten Daten wurden angegeben.'
            );
            die(json_encode($message));
    }

    $name = htmlspecialchars($_POST['name'], ENT_QUOTES);
    $klasse = htmlspecialchars($_POST['klasse'], ENT_QUOTES);
    $mailAddr = htmlspecialchars($_POST['mail'], ENT_QUOTES);
    $telefon = htmlspecialchars($_POST['telefon'], ENT_QUOTES);
    $nachhilfe = htmlspecialchars($_POST['nachhilfe'], ENT_QUOTES);
    $fach = htmlspecialchars($_POST['fach'], ENT_QUOTES);
    $zeit = htmlspecialchars($_POST['zeit'], ENT_QUOTES);
    $einzelnachhilfe = htmlspecialchars($_POST['einzelnachhilfe'], ENT_QUOTES);

    // optionale Parameter überprüfen und zuweisen
    $bemerkung = "";
    if (isset($_POST['bemerkung'])) {
        $bemerkung = htmlspecialchars($_POST['bemerkung'], ENT_QUOTES);
    }

    $ziel = 0;
    if (isset($_POST['ziel'])) {
        $ziel = intval(htmlspecialchars($_POST['ziel'], ENT_QUOTES));
    }

    if (explode("@", $mailAddr)[0] == $mailAddr) {
        $message = array(
            'success' => 'false',
            'message' => 'Es wurde keine valide Emailadresse angegeben.'
        );

        die(json_encode($message));
    }

    $bemerkung = htmlspecialchars($bemerkung, ENT_QUOTES);

    // Daten eintragen, bei Erfolg
    $result = SQL("INSERT INTO shsAnmeldung (name, klasse, mail, telefon, nachhilfe, fach, zeit, einzelnachhilfe, bemerkung, zielKlasse) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$name, $klasse, $mailAddr, $telefon, $nachhilfe, $fach, $zeit, $einzelnachhilfe, $bemerkung, $ziel]);
    if ($result === false) {
        $message = array(
            'success' => 'false',
            'message' => 'Deine Daten konnte nicht gespeichert werden. Bitte kontaktiere uns über das Kontaktformular.'
        );
        die(json_encode($message));
    }

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exceptions;

    $mailer = new PHPMailer(true);
    // $mail -> SMTPDebug = SMTP::DEBUG_SERVER;
    $mailer -> isSMTP();
    $mailer -> Host = "mail.gmx.net";
    $mailer -> SMTPAuth = true;
    $mailer -> Username = "kantiges-schulleben@gmx.de";
    $mailer -> Password = "PASSWORD";
    $mailer -> SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mailer -> Port = 587;
    $mailer -> setFrom("kantiges-schulleben@gmx.de", "Schüler helfen Schülern");
    $mailer -> isHTML(false);
    $mailer -> Subject = "Anmeldebestätigung";

    try {
        $mailer -> addAddress($mailAddr);
        
        $mailer -> Body = "Hallo $name
        Hiermit bist du offiziell ein Teil des Projekts „Schüler helfen Schüler“. Du bist in unserer Datenbank vermerkt und wirst von uns zeitnah mit weiteren Informationen versorgt. 
        Deine Daten werden gemäß unseren Datenschutzregelung behandelt. 
        Bei weiteren Fragen oder Problemen kannst du dich jeder Zeit an unser Team wenden. 
        Viel Spaß beim gemeinsamen Lernen. 
        Dein „Schüler helfen Schülern“ – Team ";
    
        $mailer -> send();
    } catch (Exception $e) {
        $message = array(
            'success' => 'false',
            'message' => 'Anmeldebestätigung konnte nicht versandt werden.'
        );
        die(json_encode($message));
    } finally {
        $mailer -> ClearAddresses();
    }

    echo json_encode(array(
        'success' => 'true'
    ));
?>