<?php

namespace App\Service;

class BarcodeService
{
    public function __construct() {}

    /**
     * Calculates the check digit as per the EAN-13 rule.
     * @var string The input 12 character barcode.
     * @return string|null The complete 13 character barcode, or null if the input barcode is incorrect.
     */
    public function addCheckDigit(string $inputBarcode): string
    {
        if (strlen($inputBarcode) !== 12 || ctype_digit($inputBarcode)) {
            return null;
        }

        /*
        The GTIN-13 standards (here, EAN-13 for EU) specifies the need for a last check digit.
        It is calculated in five steps, using a string of 12 characters:
        
        1) Reverse the order of the digits.
        2) Calculate the sum of even-numbered digits.
        3) Calculate the sum of odd-numbered digits and multiply it by 3.
        4) Add the two sums together.
        5) The check digit is the smallest number that makes the total a multiple of 10.

        Finally, append the check digit to the original barcode.
        The final product barcode should be 13 characters long.
        */

        // Reverse the order of digits
        $reversed = strrev($inputBarcode);

        // Initialize sums for odd and even positions
        $oddSum = 0;
        $evenSum = 0;

        // Loop through each digit in the reversed barcode
        for ($i = 0; $i < strlen($reversed); $i++) {
            $digit = (int) $reversed[$i];

            // Odd positions (1st, 3rd, 5th, ...) => Multiply by 3
            if ($i % 2 == 0) {
                $oddSum += $digit;
            } else {  // Even positions (2nd, 4th, 6th, ...) => Multiply by 1
                $evenSum += $digit;
            }
        }

        // Multiply the oddSum by 3 as per the GTIN-13 calculation rule
        $oddSum *= 3;

        // Add both sums together
        $totalSum = $oddSum + $evenSum;

        // Find the check digit (smallest number that makes the total a multiple of 10)
        $checkDigit = (10 - ($totalSum % 10)) % 10;

        return "$inputBarcode$checkDigit";
    }
}
