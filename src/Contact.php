<?php
namespace Gustavguez;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Contact {

    public static $ALLOWED_METHOD = 'POST';

    protected $mailHost;
    protected $mailFrom;
    protected $mailFromPassword;
    protected $mailFromName;
    protected $mailTo;
    protected $mailSubject;
    protected $templatesDir;
    protected $templatesFile;

    protected $bodyHTML;
    protected $data;
    
    public function __construct(array $config) {
        $this->mailHost = $config['mailHost'];
        $this->mailFrom = $config['mailFrom'];
        $this->mailFromPassword = $config['mailFromPassword'];
        $this->mailFromName = $config['mailFromName'];
        $this->mailTo = $config['mailTo'];
        $this->mailSubject = $config['mailSubject'];
        $this->templatesDir = $config['templatesDir'];
        $this->templatesFile = $config['templatesFile'];

        $this->bodyHTML = '';
        $this->data = [];
    }

    public function checkMethod(){
        return $_SERVER['REQUEST_METHOD'] === self::$ALLOWED_METHOD;
    }

    public function processPayload() {
        //Load data
        $this->data = [
            'email' => strip_tags($_POST['email']),
            'message' => strip_tags($_POST['message'])
        ];
    }

    public function renderBody(){
        //Render html using TWIG
        $loader = new FilesystemLoader($this->templatesDir);
        $twig = new Environment($loader, []);
        $this->bodyHTML = $twig->render($this->templatesFile, $this->data);
    }

    public function send(){
        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer(true);
        $response = false;

        // @@TODO: use recaptcha v3 to prevent spam

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
            $mail->Host       = $this->mailHost;                    // Set the SMTP server to send through
            $mail->Username   = $this->mailFrom;                     // SMTP username
            $mail->Password   = $this->mailFromPassword;                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port       = 465;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom($this->mailFrom, $this->mailFromName);
            $mail->addAddress($this->mailTo);

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = $this->mailSubject;
            $mail->Body    = $this->bodyHTML;

            $response = $mail->send();
        } catch (\Exception $e) {
            //Do nothing
        }
        return $response;
    }
}