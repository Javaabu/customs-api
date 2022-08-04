<?php

namespace Javaabu\Customs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;

class Customs
{
    const DEFAULT_API_URL = 'https://api.customs.gov.mv/api/';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    protected $api_url;

    /**
     * Constructor
     *
     * @param string $username
     * @param string $password
     * @param string|null $url
     * @param array $client_options
     */
    public function __construct(
        string $username,
        string $password,
        ?string $url = null,
        array $client_options = []
    )
    {
        $this->username = $username;
        $this->password = $password;
        $this->api_url = rtrim($url ?: self::DEFAULT_API_URL, '/').'/';

        $this->client = $this->initClient($client_options);
    }

    /**
     * Add the credentials for sending requests
     * Use the placeholder {user} and {password}
     * for where to replace the credentials with
     */
    protected function addCredentials(string $endpoint): string
    {
        $endpoint = str_replace('{user}', $this->username, $endpoint);

        return str_replace('{password}', $this->password, $endpoint);
    }

    /**
     * Initialize the guzzle client
     */
    protected function initClient(array $client_options): Client
    {
        $client_options = array_merge([
            'base_uri' => $this->api_url,
            'headers' => [
                'Content-Type'  => 'application/json',
            ],
        ], $client_options);

        $client = new Client($client_options);

        return $client;
    }

    /**
     * Send a request an endpoint
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     */
    protected function sendRequest(string $endpoint, $params = [], $method = 'POST'): ResponseInterface
    {
        $params_name = $method == 'GET' ? 'query' : 'json';

        $endpoint = ltrim($this->addCredentials($endpoint), '/');

        return $this->client->request($method, $endpoint, [
            $params_name => $params,
        ]);
    }

    /**
     * Get a json representation of a trader
     *
     * @param array $trader
     * @return array
     */
    protected function cleanJsonTrader(?array $trader): ?array
    {
        if ($trader) {
            $Code = $trader['Code'] ?? null;
            $Name = $trader['Name'] ?? null;
            $Address = $trader['Address'] ?? null;
            $Sector = $trader['Sector'] ?? null;
            $MedNo = $trader['MedNo'] ?? null;
            $Tin = $trader['Tin'] ?? null;
            $Email = $trader['Email'] ?? null;
            $NID = $trader['NID'] ?? null;

            if ($NID == 'NA') {
                $NID = null;
            }

            if ($MedNo == 'NA') {
                $MedNo = null;
            }

            if ($Tin == 'NA') {
                $Tin = null;
            }

            return compact(
                'Code',
                'Name',
                'Address',
                'Sector',
                'MedNo',
                'Tin',
                'Email',
                'NID'
            );
        }

        return null;
    }

    /**
     * Get a json response
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     */
    protected function getJson(string $endpoint, $params = [], $method = 'POST'): ?array
    {
        try {
            return json_decode(
                $this->sendRequest($endpoint, $params, $method)
                    ->getBody()
                    ->getContents(),
                true
            );
        } catch (ClientException $e) {
            if ($e->getCode() == 404) {
                return null;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get a single trader by med number
     *
     * @param string $registration_number
     * @return array
     */
    public function getTraderByMedNumber(string $registration_number): ?array
    {
        $trader_json = $this->getJson('Traders/Details/{user}/{password}', ['MedNumber' => $registration_number]);

        $MedNo = trim($trader_json['MedNo'] ?? null);

        if ($registration_number != $MedNo) {
            return null;
        }

        return $this->cleanJsonTrader($trader_json);
    }


    /**
     * Get a single trader by c number
     *
     * @param string $c_number
     * @return array
     */
    public function getTraderByCNumber(string $c_number): ?array
    {
        $trader_json = $this->getJson('Traders/Details/{user}/{password}/'.$c_number, [], 'GET');

        $Code = trim($trader_json['Code'] ?? null);

        if ($c_number != $Code) {
            return null;
        }

        return $this->cleanJsonTrader($trader_json);
    }
}
