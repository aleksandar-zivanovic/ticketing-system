<?php

    //Import PHPMailer classes into the global namespace
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

class User
{
    public string $email;
    public string $password;
    public string $repeatedPassword;
    public string $name;
    public string $surname;
    public string $phone;
    public string $departmentId;
    public string $role;
    protected object $db;
    private ?string $verificationCode;

    private function dbConn(): object
    {
        require_once('Database.php');
        $connection = new Database;
        return $this->db = $connection->connect();
    }

    public function register()
    {
        $this->dbConn();

        if (!empty($_POST['email'])  
            && !empty($_POST['password']) 
            && !empty($_POST['rpassword']) 
            && !empty($_POST['name']) 
            && !empty($_POST['surname']) 
            && !empty($_POST['phone'])) 
        {
            $this->email = htmlspecialchars(trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)), ENT_QUOTES, 'UTF-8');
            $this->password = htmlspecialchars(trim(filter_input(INPUT_POST, 'password', FILTER_DEFAULT)), ENT_QUOTES, 'UTF-8');
            $this->repeatedPassword = htmlspecialchars(trim(filter_input(INPUT_POST, 'rpassword', FILTER_DEFAULT)), ENT_QUOTES, 'UTF-8');
            $this->name = htmlspecialchars(trim(filter_input(INPUT_POST, 'name', FILTER_DEFAULT)), ENT_QUOTES, 'UTF-8');
            $this->surname = htmlspecialchars(trim(filter_input(INPUT_POST, 'surname', FILTER_DEFAULT)), ENT_QUOTES, 'UTF-8');
            $this->phone = htmlspecialchars(trim(filter_input(INPUT_POST, 'phone', FILTER_DEFAULT)), ENT_QUOTES, 'UTF-8');
            
            // checking email data
            $this->emailRegexCheck();
            $this->isEmailOccupied();

            // checking password
            if (strlen($this->password) >= 6) {
                if ($this->password != $this->repeatedPassword) {
                    $this->registrationErrorHandling("Passwords don't match.");
                }
            } else {
                $this->registrationErrorHandling("Password must be at least 6 characters long.");
            }
            
            // hashing password
            $this->passwordHashing();

            // creating email verificaton code
            $this->createVerificationCode();

            // inserting a new user to the database and sending verification email
            $this->addUser();

        } else {
            $this->registrationErrorHandling("Fill all fields, please.");
        }
        
    }

    // regex checking if entered email is a valid email address
    public function emailRegexCheck(): void
    {
        $emailCheck = preg_match('/^[a-z0-9._-]{2,}+@[a-z0-9.-]+\.[a-z]{2,}$/', $this->email);

        if ($emailCheck == 0) {
            $this->registrationErrorHandling("Email address is not valid!");
        }
    }

    // checking if there is a use with the entered email
    public function isEmailOccupied(): void
    {
        $queryLookForEmail = "SELECT email FROM users WHERE email = :email";
        $query = $this->db->prepare($queryLookForEmail);
        $query->bindValue(':email', $this->email, PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount() >= 1) {
            $this->registrationErrorHandling("Email is already in use!");
        }
    }

    // hashing password
    public function passwordHashing(): string
    {
        return $this->password = password_hash($this->password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    // handling invalid data errors during registration process
    public function registrationErrorHandling(string $errorMessage): void
    {
        $_SESSION['error_message'] = $errorMessage;
            die(header("Location: ../forms/register.php"));
    }

    // saving new user data to the database
    public function addUser(): void
    {
        $addUserQuery = "INSERT INTO users (email, password, name, surname, phone, role_id, department_id, verification_code, verified) VALUES(:em, :pw, :nm, :sn, :pn, 1, NULL, :vc, 0)";
        $query = $this->db->prepare($addUserQuery);
        $query->bindValue(':em', $this->email, PDO::PARAM_STR);
        $query->bindValue(':pw', $this->password, PDO::PARAM_STR);
        $query->bindValue(':nm', $this->name, PDO::PARAM_STR);
        $query->bindValue(':sn', $this->surname, PDO::PARAM_STR);
        $query->bindValue(':pn', $this->phone, PDO::PARAM_STR);
        $query->bindValue(':vc', $this->verificationCode, PDO::PARAM_STR);
        if($query->execute()) {
            $_SESSION['error_message'] = "You are registered. We sent verification email to your email addres. Check your email and verify it.";
            $this->sendingVerificationEmail();
        }
    }

    // create verification code for verificaton email for a new user
    public function createVerificationCode(): void
    {
        $this->verificationCode = bin2hex(random_bytes(20));
    }

    // send verification email to a new user's email address
    public function sendingVerificationEmail(): void
    {
        require_once 'PHPMailer.php';
        require_once 'SMTP.php';
        require_once 'Exception.php';
        require_once '../../config/email-config.php';

        $verificationUrl = "http://localhost/ticketing-system/public/actions/email-verification.php";

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = SMTP_SERVER;                            //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = SMTP_USERNAME;                          //SMTP username
            $mail->Password   = SMTP_PASSWORD;                          //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = SMTP_PORT;                              //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom(SEND_FROM, SEND_FROM_NAME);
            $mail->addAddress($this->email, $this->name . " " . $this->surname);         //Add a recipient
            $mail->addReplyTo(REPLY_TO, REPLY_TO_NAME);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Verification email';
            $mail->Body = 'Hello ' . $this->name . ' !<br> To finish registration click on this link:  <a href="' . $verificationUrl .'?email=' . $this->email . '&verification_code=' . $this->verificationCode . '">'. $verificationUrl . '?email=' . $this->email . '&verification_code=' . $this->verificationCode . '</a></b>';
            $mail->AltBody = 'Copy this URL in your broswer navigation bar and click enter to finsih registration proccess by confirming your email address: href="' . $verificationUrl .'?email=' . $this->email . '&verification_code=' . $this->verificationCode . '">'. $verificationUrl . '?email=' . $this->email . '&verification_code=' . $this->verificationCode;

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    // adding verified status to the user
    public function makeUserVerified(): bool
    {
        $verificationCodeFromUrl = htmlspecialchars(trim(filter_input(INPUT_GET, 'verification_code', FILTER_DEFAULT)));
        $this->verificationCode = $this->gettingUserVerificationCode();

        if ($this->verificationCode != null && $this->verificationCode == $verificationCodeFromUrl) 
        {
            $makeUserVerifiedQuery = "UPDATE users SET verification_code = null, verified = 1 WHERE email = '{$this->email}'";
            $query = $this->db->prepare($makeUserVerifiedQuery);
            if ($query->execute()) 
            {
                $_SESSION['verification_status'] = "You are verified successfully.<br>Login in, please.";
                return true;
            } else {
                // TODO: create error log to store error information
                $_SESSION['verification_status'] = "There is a problem with the verification process. Try again and if this notification continue appearing, contact administrator, please.";
                return false;
            }
        }

        // unsuccessful verificaton
        $_SESSION['verification_status'] = "There is a problem with your verification code.";
        return false;
    }

    public function gettingUserVerificationCode(): string|null
    {
        $this->db = $this->dbConn();
        $this->email = htmlspecialchars(trim(filter_input(INPUT_GET, 'email', FILTER_DEFAULT)));
        $verificationCodeQuery = "SELECT verification_code FROM users WHERE email = :em";
        $query = $this->db->prepare($verificationCodeQuery);
        $query->bindValue(':em', $this->email, PDO::PARAM_STR);
        $query->execute();
        return $query->rowCount() >= 1 ? $query->fetchColumn() : null;
    }
}