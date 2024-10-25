<?php

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

    private function dbConn()
    {
        require_once('Database.php');
        $connection = new Database;
        return $this->db = $connection->connect();
    }

    public function register()
    {
        $this->dbConn();
        session_start();

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

            // inserting a new user to the database
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
        $addUserQuery = "INSERT INTO users (email, password, name, surname, phone, role_id, department_id) VALUES(:em, :pw, :nm, :sn, :pn, 1, 1)";
        $query = $this->db->prepare($addUserQuery);
        $query->bindValue(':em', $this->email, PDO::PARAM_STR);
        $query->bindValue(':pw', $this->password, PDO::PARAM_STR);
        $query->bindValue(':nm', $this->name, PDO::PARAM_STR);
        $query->bindValue(':sn', $this->surname, PDO::PARAM_STR);
        $query->bindValue(':pn', $this->surname, PDO::PARAM_STR);
        if($query->execute()) {
            $_SESSION['error_message'] = "You are registered. We sent verification email to your email addres. Check your email and verify it.";
            // TODO: send verification email
        }
    }
}