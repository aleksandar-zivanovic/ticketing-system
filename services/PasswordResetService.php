<?php
require_once ROOT . 'services' . DS . 'BaseService.php';
require_once ROOT . 'classes' . DS . 'User.php';

class PasswordResetService extends BaseService
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    // *** Korisnicki reset
    // - forma za unose emaila
    // - slanje emaila sa linkom za reset (PasswordForgotController/Service)
    // - forma za unos novog passworda

    // *** Reset od strane adminsitratora
    // - slanje emaila sa linkom za reset (PasswordForgotController/Service)
    // - forma za unos novog passworda


    //   *** PasswordForgotController/Service ***
    // - preuzimanje emaila iz forme ($_POST['email']) i verifikacija - samo za korisnicki reset
    // - brisanje sesije korisnika
    // - kreiranje linka za reset (token u linku)
    // - upis tokena u password_resets tabelu
    // - slanje emaila sa linkom za reset
    //   *** PasswordResetController/Service ***
    // - preuzimanje tokena iz URL-a i obrada
    // - prikaz forme za unos novog passworda (sifra i potvrdna sifra)
    // - obrada forme za unos novog passworda (sifre i token)
    // - provera da li je nova sifra ista kao neka stara sifra iz tabele user_password_history
    // - obrada sifre i upis u tabelu users i stare u tabelu user_password_history ili bacanje greske
    // - audit akcije


    // Service methods for password reset functionality
}
