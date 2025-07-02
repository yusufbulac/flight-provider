<?php

namespace App\Infrastructure\Client\AirArabia;


use App\DTO\External\AirArabia\Soap\Request\AirArabiaSoapPricingRequestDto;
use SoapClient;
use SoapFault;

class AirArabiaSoapClient
{
    private string $wsdl;

    public function __construct(
        string $wsdl,
        private string $username,
        private string $password,
        private string $requestorId = 'RBG300',
        private string $terminalId = 'TestUser/Test Runner'
    )
    {
        $this->wsdl = $wsdl;
    }


    /**
     * @throws SoapFault
     */
    public function getPrice(AirArabiaSoapPricingRequestDto $dto): array
    {
        $client = new SoapClient($this->wsdl, [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'soap_version' => SOAP_1_1,
        ]);

        // WSSE SOAP Header (Security)
        $wsseHeaderXml = '
        <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
                       xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"
                       xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
                       soap:mustUnderstand="1">
            <wsse:UsernameToken wsu:Id="UsernameToken-32124385">
                <wsse:Username>' . $this->username . '</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'
            . $this->password . '</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>';

        $wsseVar = new \SoapVar($wsseHeaderXml, XSD_ANYXML);
        $header = new \SoapHeader(
            'http://schemas.xmlsoap.org/soap/envelope/',
            'Security',
            $wsseVar,
            true
        );

        $client->__setSoapHeaders($header);

        $timestamp = date('c');
        $token = uniqid();

        $arrivalDateTime = $dto->segments[0]->arrivalDateTime;
        $departureDateTime = $dto->segments[0]->departureDateTime;
        $flightNumber = $dto->segments[0]->flightNumber;
        $departureCode = $dto->segments[0]->departureAirport;
        $arrivalCode = $dto->segments[0]->arrivalAirport;
        $operatingAirline = $dto->segments[0]->operatingAirline;

        $adt = $dto->adt;
        $chd = $dto->chd;
        $inf = $dto->inf;

        // XML Body (ns1:OTA_AirPriceRQ)

        $bodyXml = <<<XML
<OTA_AirPriceRQ xmlns="http://www.opentravel.org/OTA/2003/05"
    EchoToken="{$token}" PrimaryLangID="en-us" SequenceNmbr="1"
    TimeStamp="{$timestamp}" Version="20061.00">
    <POS>
        <Source TerminalID="{$this->terminalId}">
            <RequestorID ID="{$this->username}" Type="4"/>
            <BookingChannel Type="12"/>
        </Source>
    </POS>
    <AirItinerary DirectionInd="OneWay">
        <OriginDestinationOptions>
            <OriginDestinationOption>
                <FlightSegment ArrivalDateTime="{$arrivalDateTime}"
                               DepartureDateTime="{$departureDateTime}"
                               FlightNumber="{$flightNumber}">
                    <DepartureAirport LocationCode="{$departureCode}"/>
                    <ArrivalAirport LocationCode="{$arrivalCode}"/>
                    <OperatingAirline Code="{$operatingAirline}"/>
                </FlightSegment>
            </OriginDestinationOption>
        </OriginDestinationOptions>
    </AirItinerary>
    <TravelerInfoSummary>
        <AirTravelerAvail>
            <PassengerTypeQuantity Code="ADT" Quantity="{$adt}"/>
            <PassengerTypeQuantity Code="CHD" Quantity="{$chd}"/>
            <PassengerTypeQuantity Code="INF" Quantity="{$inf}"/>
        </AirTravelerAvail>
        <SpecialReqDetails>
            <SSRRequests/>
        </SpecialReqDetails>
    </TravelerInfoSummary>
</OTA_AirPriceRQ>
XML;

        $soapVar = new \SoapVar($bodyXml, XSD_ANYXML);


        try {
            $client->__soapCall('getPrice', [$soapVar]);

            $xml = $client->__getLastResponse();
            $xmlObj = simplexml_load_string($xml);
            $xmlObj->registerXPathNamespace('ns1', 'http://www.opentravel.org/OTA/2003/05');

            $segment = $xmlObj->xpath('//ns1:FlightSegment');
            $departureAirport = $xmlObj->xpath('//ns1:DepartureAirport');
            $arrivalAirport = $xmlObj->xpath('//ns1:ArrivalAirport');
            $totalFare = $xmlObj->xpath('//ns1:TotalFare');


            $result = [
                "ArrivalDateTime" => (string)$segment[0]["ArrivalDateTime"],
                "DepartureDateTime" => (string)$segment[0]["DepartureDateTime"],
                "FlightNumber" => (string)$segment[0]["FlightNumber"],
                "DepartureAirport" => (string)$departureAirport[0]["LocationCode"],
                "ArrivalAirport" => (string)$arrivalAirport[0]["LocationCode"],
                "TotalPrice" => $this->applyCommission((float)$totalFare[0]["Amount"]),
            ];

            return $result;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function applyCommission(float $basePrice, float $percentage = 10): float
    {
        return round($basePrice * (1 + $percentage / 100), 2);
    }

}
