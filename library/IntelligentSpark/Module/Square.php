<?php
/**
 * Frontend Module Skeleton for Contao Open Source CMS
 *
 * Copyright (C) 2017 Intelligent Spark
 *
 * @package    Contao Module Skeleton
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


namespace IntelligentSpark\Module;

use SquareConnect;

class Square extends \Module {

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_square';


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['square'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->Template->html = (TL_MODE == 'FE') ? $this->html : htmlspecialchars($this->html);
    }

    protected function getLocationIDs() {
        # setup authorization
        \SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($this->access_token);
        # create an instance of the Location API

        $locations_api = new \SquareConnect\Api\LocationsApi();

        try {
            $locations = $locations_api->listLocations();
            print_r($locations->getLocations());
        } catch (\SquareConnect\ApiException $e) {
            echo "Caught exception!<br/>";
            print_r("<strong>Response body:</strong><br/>");
            echo "<pre>"; var_dump($e->getResponseBody()); echo "</pre>";
            echo "<br/><strong>Response headers:</strong><br/>";
            echo "<pre>"; var_dump($e->getResponseHeaders()); echo "</pre>";
            exit(1);
        }
    }

    protected function chargeCard() {
        $transactions_api = new \SquareConnect\Api\TransactionsApi();

        $request_body = array (
            "card_nonce" => $this->nonce,
            # Monetary amounts are specified in the smallest unit of the applicable currency.
            # This amount is in cents. It's also hard-coded for $1.00, which isn't very useful.
            "amount_money" => array (
                "amount" => (float)\Input::post('amount')*100, //(their amounts in dollars, square in cents)
                "currency" => "USD"
            ),
            # Every payment you process with the SDK must have a unique idempotency key.
            # If you're unsure whether a particular payment succeeded, you can reattempt
            # it with the same idempotency key without worrying about double charging
            # the buyer.
            "idempotency_key" => uniqid()
        );

        try {
            $result = $transactions_api->charge($this->location_id,  $request_body);
            print_r($result);
        } catch (\SquareConnect\ApiException $e) {
            echo "Exception when calling TransactionApi->charge:";
            var_dump($e->getResponseBody());
        }
    }
}