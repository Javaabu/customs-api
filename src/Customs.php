<?php

namespace Javaabu\Customs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

class Customs
{
    const DEFAULT_API_URL = 'https://customs.gov.mv/';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \Goutte\Client
     */
    protected $goutte_client;

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
     * Generate the access token for sending requests
     */
    protected function generateAccessToken(): string
    {
        return base64_encode($this->username.':'.$this->password);
    }

    /**
     * Initialize the guzzle client
     */
    protected function initClient(array $client_options): Client
    {
        $client_options = array_merge([
            'base_uri' => $this->api_url,
            /*'headers' => [
                'Authorization' => 'Basic ' . $this->generateAccessToken(),
                'Content-Type'  => 'application/json',
            ],*/
        ], $client_options);

        $client = new Client($client_options);

        $this->goutte_client = new \Goutte\Client();

        return $client;
    }

    /**
     * Send a request to an endpoint using the crawler
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     */
    protected function sendCrawlerRequest(string $endpoint, $params = [], $method = 'GET'): Crawler
    {
        $endpoint = ltrim($endpoint, '/');

        $url = $this->api_url . '/' . $endpoint;

        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        return $this->goutte_client->request($method, $url);
    }

    /**
     * Send a request an endpoint
     *
     * @param string $endpoint
     * @param array $params
     * @param string $method
     */
    protected function sendRequest(string $endpoint, $params = [], $method = 'GET'): ResponseInterface
    {
        $params_name = $method == 'GET' ? 'query' : 'form_params';

        $endpoint = ltrim($endpoint, '/');

        return $this->client->request($method, $endpoint, [
            $params_name => $params,
        ]);
    }

    /**
     * Escape the xpath
     * https://stackoverflow.com/questions/24410671/how-to-escape-xpath-in-php
     */
    protected function escapeQuote(string $value): string
    {
        if (false === strpos($value, '"')) {
            return '"' . $value . '"';
        }

        if (false === strpos($value, '\'')) {
            return '\'' . $value . '\'';
        }

        // if the value contains both single and double quotes, construct an
        // expression that concatenates all non-double-quote substrings with
        // the quotes, e.g.:
        //
        //    concat("'foo'", '"', "bar")
        $sb = 'concat(';
        $substrings = explode('"', $value);
        for ($i = 0; $i < count($substrings); ++$i) {
            $needComma = ($i > 0);
            if ($substrings[$i] !== '') {
                if ($i > 0) {
                    $sb .= ', ';
                }
                $sb .= '"' . $substrings[$i] . '"';
                $needComma = true;
            }
            if ($i < (count($substrings) - 1)) {
                if ($needComma) {
                    $sb .= ', ';
                }
                $sb .= "'\"'";
            }
        }
        $sb .= ')';

        return $sb;
    }

    /**
     * Get a json representation of a trader
     *
     * @param string $number
     * @param string $field
     * @return array
     */
    protected function getJsonTrader(string $number, string $field): ?array
    {
        $crawler = $this->sendCrawlerRequest('eServices/CompanySearch', ['query' => $number]);

        $col_index = $field == 'med_number' ? 5 : 1;
        $number = $this->escapeQuote($number);

        // https://stackoverflow.com/questions/4608097/xpath-to-select-a-table-row-that-has-a-cell-containing-specified-text
        $cells = $crawler->filterXPath('//table[@id="companyList"]/tbody/tr/td['.$col_index.'][normalize-space(text())='.$number.']/../td')->extract(['_text']);

        if ($cells && count($cells) == 6) {
            return [
                'Code' => trim($cells[0]),
                'Name' => trim($cells[1]),
                'Address' => trim($cells[2]),
                'Sector' => trim($cells[3]),
                'MedNo' => trim($cells[4]),
                'Tin' => trim($cells[5]),
            ];
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
    protected function getJson(string $endpoint, $params = []): ?array
    {
        try {
            return json_decode(
                $this->sendRequest($endpoint, $params)
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
        return $this->getJsonTrader($registration_number, 'med_number');
    }


    /**
     * Get a single trader by c number
     *
     * @param string $c_number
     * @return array
     */
    public function getTraderByCNumber(string $c_number): ?array
    {
        return $this->getJsonTrader($c_number, 'c_number');
    }
}
