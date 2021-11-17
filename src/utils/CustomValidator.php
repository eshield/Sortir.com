<?php

namespace App\utils;



use Symfony\Component\Console\Exception\InvalidArgumentException;


class CustomValidator {

    public function validateEmail(?string $email) : string {

        if (empty($email )) {
            throw new InvalidArgumentException('VEUILLER SAISIR UN EMAIL.') ;
        }
        if(!filter_var($email , FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('EMAIL SAISIE INVALIDE.') ;

        }
        return $email ;
    }

    public function validatePassword(?string $password) : string {

        if (empty($password )) {
            throw new InvalidArgumentException('VEUILLER SAISIR UN MOT DE PASSE.') ;
        }

        $passwordRegex = "^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$^" ;
       if(preg_match( $passwordRegex ,  $password )) {

           throw new InvalidArgumentException('LE MOT DE PASSE DOIT CONTENIR 8 CARACTERE AU MINIMUM 
            : DONT UNE LETTRE MAJUSCULE
            , UNE LETTRE MINUSCULE 
            , ET UN NOMBRE ') ;

       }
        return $password  ;
    }


    public function validateUsername(?string $username) : string {

        if (empty($username )) {
            throw new InvalidArgumentException('VEUILLER SAISIR UN USERNAME.') ;
        }


        return $username  ;
    }


}
