<?php

/**
 * This file is part of Year of Prayer Service.
 *
 * Year of Prayer Service is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Year of Prayer Service is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Missional Digerati <info@missionaldigerati.org>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 */

namespace YearOfPrayer\ApiService;

use YearOfPrayer\ApiService\Contracts\HttpServiceInterface;
use GuzzleHttp\Client;

class HttpService implements HttpServiceInterface
{
    /**
     * The Http client
     *
     * @var Client
     */
    private $httpClient;

    /**
     * Construct the class
     *
     * @access public
     */
    public function __construct(
        private string $baseUrl = ''
    ) {
        $this->httpClient = new Client();
    }

    /**
     * Set the base URL for all requests
     *
     * @param  string   $url    The url for all requests
     * @access public
     */
    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    /**
     * Make a GET request to the given URL
     * @param  string   $url    The URL to add to Query String
     * @param  array    $data   An array of data to send
     * @return array            The JSON response decoded
     * @throws \Exception       If the status is not 200 or 201
     * @access public
     */
    public function get(string $url, array $data): array
    {
        $requestUrl = "{$this->baseUrl}{$url}";
        return $this->makeRequest('GET', $requestUrl, $data);
    }

    /**
     * Make a POST request to the given URL
     * @param  string   $url    The URL to post to
     * @param  array    $data   An array of data to send
     * @return array            The JSON response decoded
     * @throws \Exception       If the status is not 200 or 201
     * @access public
     */
    public function post(string $url, array $data): array
    {
        $requestUrl = "{$this->baseUrl}{$url}";
        return $this->makeRequest('POST', $requestUrl, $data);
    }

    /**
     * Make a PUT request to the given URL
     * @param  string   $url    The URL to post to
     * @param  array    $data   An array of data to send
     * @return array            The JSON response decoded
     * @throws \Exception       If the status is not 200 or 201
     * @access public
     */
    public function put(string $url, array $data): array
    {
        $requestUrl = "{$this->baseUrl}{$url}";
        return $this->makeRequest('PUT', $requestUrl, $data);
    }

    /**
     * Make the request
     *
     * @param  string   $method     The http method
     * @param  string   $url        The location of the resource
     * @param  array    $data       The data to pass to the resource
     * @return array                An array of the response
     * @throws \Exception       If the status is not 200 or 201, or the response is not valid JSON
     * @access private
     */
    private function makeRequest(string $method, string $url, array $data): array
    {
        /**
         * Add a user agent header to stop 406 responses
         * @link https://stackoverflow.com/a/37984220
         */
        if (!isset($data['headers'])) {
            $data['headers'] = [];
        }
        $data['headers']['User-Agent'] = 'Mozilla/5.0';
        $response = $this->httpClient->request($method, $url, $data);
        $status = $response->getStatusCode();
        if (in_array($status, [200, 201])) {
            $decoded = json_decode($response->getBody()->getContents(), true);
            if (!is_array($decoded)) {
                throw new \Exception(
                    "Unable to parse the response from {$url}. Expected a JSON object or array."
                );
            }
            return $decoded;
        } else {
            throw new \Exception(
                "Unable to reach the {$url}. Received a status code of {$status}."
            );
        }
    }
}
