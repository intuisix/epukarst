<?php

namespace App\Service;

/**
 * Générateur de mots de passe forts.
 */
class PasswordGeneratorService
{
    /**
     * Génère un mot de passe fort contenant au moins une lettre majuscule, une
     * lettre minuscule, un chiffre et un symbole, et au moins le nombre de
     * caractères spécifiés.
     * 
     * Pour que le mot de passe soit plus facile à lire et à introduire pour
     * l'utilisateur, le mot de passe peut, en option, contenir des tirets et
     * les symboles auront moins de chances d'être tirés.
     *
     * @param integer $length
     * @param boolean $dashes
     * @return string
     */
    public function generate(int $length = 12, bool $dashes = true): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        $symbols = '!@#$%&*?';

        /* Tirer une majuscule, une minuscule, un chiffre et un symbole pour
        être certain d'avoir un caractère de chaque groupe, et les mettre dans
        un panier */
        $basket = '';
        $basket .= $upper[random_int(0, strlen($upper) - 1)];
        $basket .= $lower[random_int(0, strlen($lower) - 1)];
        $basket .= $digits[random_int(0, strlen($digits) - 1)];
        $basket .= $symbols[random_int(0, strlen($symbols) - 1)];
        /* Compléter le panier avec d'autres caractères, éventuellement
        identiques à ceux déjà tirés, jusqu'à ce que l'on ait le nombre de
        caractères demandé pour le mot de passe; les symboles sont tirés moins
        fréquemment que les autres caractères */
        $all = '';
        $all .= $upper . $upper;
        $all .= $lower . $lower;
        $all .= $digits . $digits;
        $all .= $symbols;
        while (strlen($basket) < $length) {
            $basket .= $all[random_int(0, strlen($all) - 1)];
        }

        /* Constituer le mot de passe en mélangeant les caractères du panier */
        $password = '';
        $dashDistance = ($dashes && ($length > 8)) ? floor(sqrt($length)) : 0;
        while (strlen($basket) > 0) {
            /* Retirer un caractère pris au hasard dans le panier et le transférer dans le mot de passe */
            $index = random_int(0, strlen($basket) - 1);
            $password .= $basket[$index];
            $basket =
                substr($basket, 0, $index) .
                substr($basket, $index + 1);

            /* Ajouter les tirets chaque fois que la distance est atteinte */
            if ((0 != $dashDistance) && (0 == ((strlen($password) + 1) % ($dashDistance + 1))) && (0 != strlen($basket))) {
                $password .= '-';
            }
        }

        return $password;
    }
}
