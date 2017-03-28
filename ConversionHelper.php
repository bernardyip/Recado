<?php

class ConversionHelper {

    private $currencyFormatter;
    private static $conversionHelper;
    
    private function __construct() {
        ConversionHelper::$conversionHelper = $this;
        $this->currencyFormatter = NumberFormatter::create("en_SG", NumberFormatter::CURRENCY);
    }
    
    public static function stringToMoney($str) {
        if (is_null($str)) return null;
        if (is_null(ConversionHelper::$conversionHelper)) {
            ConversionHelper::$conversionHelper = new ConversionHelper();
        }
        $currency = "SGD";
        return ConversionHelper::$conversionHelper->currencyFormatter->parseCurrency($str, $currency);
    }
    
    public static function moneyToString($money) {
        if (is_null($money)) return null;
        if (is_null(ConversionHelper::$conversionHelper)) {
            ConversionHelper::$conversionHelper = new ConversionHelper();
        }
        return ConversionHelper::$conversionHelper->currencyFormatter->formatCurrency($money, "SGD");
    }
}
?>