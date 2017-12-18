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

use Contao\Module as Contao_Module;
use SquareConnect;

class Square extends Contao_Module {

    /**
     * Square Location
     * @var object
     */
    protected $location;

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
        $GLOBALS['TL_CSS'][] = 'system/modules/contao-square-payments/assets/square.css';
        $this->getLocationIDs();
        $GLOBALS['TL_BODY'][] = '<script type="text/javascript" src="https://js.squareup.com/v2/paymentform"></script>';
        $GLOBALS['TL_BODY'][] = '<script>
        // Set the application ID
        var applicationId = "'.$this->application_id.'";
        
        // Set the location ID
        var locationId = "'.$this->location->getId().'";
        function requestCardNonce(event){event.preventDefault();paymentForm.requestCardNonce()}
var paymentForm=new SqPaymentForm({applicationId:applicationId,locationId:locationId,inputClass:\'form-control\',inputStyles:[{fontSize:\'.9em\'}],applePay:{elementId:\'sq-apple-pay\'},masterpass:{elementId:\'sq-masterpass\'},cardNumber:{elementId:\'sq-card-number\',placeholder:\'•••• •••• •••• ••••\'},cvv:{elementId:\'sq-cvv\',placeholder:\'CVV\'},expirationDate:{elementId:\'sq-expiration-date\',placeholder:\'MM/YY\'},postalCode:{elementId:\'sq-postal-code\'},callbacks:{methodsSupported:function(methods){var applePayBtn=document.getElementById(\'sq-apple-pay\');var applePayLabel=document.getElementById(\'sq-apple-pay-label\');var masterpassBtn=document.getElementById(\'sq-masterpass\');var masterpassLabel=document.getElementById(\'sq-masterpass-label\');if(methods.applePay===!0){applePayBtn.style.display=\'inline-block\';applePayLabel.style.display=\'none\'}
            if(methods.masterpass===!0){masterpassBtn.style.display=\'inline-block\';masterpassLabel.style.display=\'none\'}},createPaymentRequest:function(){return{requestShippingAddress:!1,currencyCode:"USD",countryCode:"US",total:{label:"Merchant Name",amount:document.getElementById(\'sq-amount\'),pending:!1,},lineItems:[{label:"Subtotal",amount:document.getElementById(\'sq-amount\'),pending:!1,}]}},cardNonceResponseReceived:function(errors,nonce,cardData){if(errors){console.log("Encountered errors:");var errormsgs=[];errors.forEach(function(error){console.log(\'  \'+error.message);errormsgs.push(error.message)});document.getElementById(\'errors\').innerHTML=errormsgs.join(\'<br>\');document.getElementById(\'errors\').classList.remove(\'hidden\');return;}
            document.getElementById(\'card-nonce\').value=nonce;document.getElementById(\'nonce-form\').submit()},unsupportedBrowserDetected:function(){},inputEventReceived:function(inputEvent){switch(inputEvent.eventType){case \'focusClassAdded\':break;case \'focusClassRemoved\':break;case \'errorClassAdded\':break;case \'errorClassRemoved\':break;case \'cardBrandChanged\':break;case \'postalCodeChanged\':break}},paymentFormLoaded:function(){}}})
        </script>';

        if (\Input::post('FORM_SUBMIT') == 'nonce-form') {

                $amount = (float)\Input::post('amount');
                $nonce = \Input::post('card-nonce');
                $invoice_number = \Input::post('invoice-number');

                $result = $this->chargeCard($nonce,$amount,$invoice_number);

                if(array_key_exists('error',$result)) {
                    $this->Template->error = $result['error']['detail'];
                }else{
                    $this->Template->message = "Charge Successful! Thank you for your payment.";
                }
        }
    }

    protected function getLocationIDs() {
        # setup authorization
        SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($this->personal_access_token);
        # create an instance of the Location API

        $locations_api = new SquareConnect\Api\LocationsApi();

        try {
            $locations = $locations_api->listLocations();
            $this->location = current(array_filter($locations->getLocations(), function($location) {
                $capabilities = $location->getCapabilities();
                return is_array($capabilities) &&
                    in_array('CREDIT_CARD_PROCESSING', $capabilities);
            }));
        } catch (SquareConnect\ApiException $e) {
            /*echo "Caught exception!<br/>";
            print_r("<strong>Response body:</strong><br/>");
            echo "<pre>"; var_dump($e->getResponseBody()); echo "</pre>";
            echo "<br/><strong>Response headers:</strong><br/>";
            echo "<pre>"; var_dump($e->getResponseHeaders()); echo "</pre>";
            exit(1);*/
        }
    }

    protected function chargeCard($nonce,$amount,$invoice_number) {
        $transactions_api = new SquareConnect\Api\TransactionsApi();

        $request_body = array (
            "card_nonce" => $nonce,
            # Monetary amounts are specified in the smallest unit of the applicable currency.
            # This amount is in cents. It's also hard-coded for $1.00, which isn't very useful.
            "amount_money" => array (
                "amount" => $amount*100, //(their amounts in dollars, square in cents)
                "currency" => "USD"
            ),

            "reference_id" => $invoice_number,
            # Every payment you process with the SDK must have a unique idempotency key.
            # If you're unsure whether a particular payment succeeded, you can reattempt
            # it with the same idempotency key without worrying about double charging
            # the buyer.
            "idempotency_key" => uniqid()
        );

        try {
            $result = $transactions_api->charge($this->location->getId(),  $request_body);
            return $result;
        } catch (SquareConnect\ApiException $e) {
            //echo "Exception when calling TransactionApi->charge:";
            //var_dump($e->getResponseBody());
        }
    }
}