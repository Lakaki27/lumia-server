<?php

namespace App\Service;

class PasswordGeneratorService
{
    private int $passwordLength;


    public function __construct()
    {
        $this->passwordLength = 12;
    }

    /**
     * Generates a cryptographically-secure password for first user log-in.
     * @return string
     */
    public function generatePassword(): string
    {
        $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*()_-+=';
        $charsetLength = strlen($charset);


        $password = '';
        for ($i = 0; $i < $this->passwordLength; $i++) {
            // Convert the byte to a valid index in charset
            $selectedByte = random_int(0, $charsetLength - 1    );
            $password .= $charset[$selectedByte];
        }

        return $password;
    }
}
