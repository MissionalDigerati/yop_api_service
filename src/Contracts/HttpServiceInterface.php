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

namespace YearOfPrayer\ApiService\Contracts;

interface HttpServiceInterface
{
    /**
     * Make a GET request to the given URL
     * @param  string   $url    The URL to post to
     * @param  array    $data   An array of data to send
     * @return array            The JSON response decoded
     * @throws \Exception       If the status is not 200 or 201
     * @access public
     */
    public function get(string $url, array $data): array;
    /**
     * Make a POST request to the given URL
     * @param  string   $url    The URL to post to
     * @param  array    $data   An array of data to send
     * @return array            The JSON response decoded
     * @throws \Exception       If the status is not 200 or 201
     * @access public
     */
    public function post(string $url, array $data): array;
    /**
     * Make a PUT request to the given URL
     * @param  string   $url    The URL to post to
     * @param  array    $data   An array of data to send
     * @return array            The JSON response decoded
     * @throws \Exception       If the status is not 200 or 201
     * @access public
     */
    public function put(string $url, array $data): array;
    /**
     * Set the base URL for all requests
     *
     * @param  string   $url    The url for all requests
     * @access public
     */
    public function setBaseUrl(string $url): void;
}
