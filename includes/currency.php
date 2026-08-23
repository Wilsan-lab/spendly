<?php

function getCurrencySymbol($currency)
{
    switch ($currency) {

        case "EUR":
            return "€";

        case "TRY":
            return "₺";

        case "DJF":
            return "Fdj";

        case "USD":
        default:
            return "$";
    }
}