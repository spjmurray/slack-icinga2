<?php
/**
 * Slack-Icinga2
 * Copyright (C) 2012-2016 Icinga Development Team (https://www.icinga.org/)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA.
 */

/**
 * Encapsulates a HTTP request
 *
 * Allows GET and POST requests to be sent to the Icinga2 API.  The query
 * is configurred as a hash of key value pairs.  Preformatted data can be
 * optionally supplied to POST requests.
 */
class HttpRequest
{
    /* Reference to the static configuration for API, authentication and SSL config */
    private $config;

    /**
     * Initialize a new instance
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Formats a URL
     *
     * Note that Icinga2 does not support + as a replacement for space characters
     * and must use %20
     */
    private function url($path, $query)
    {
        $url = 'https://' . $this->config->host . ':' . $this->config->port . $path;
        if($query) {
            $url .= '?' . http_build_query($query, null, '&', PHP_QUERY_RFC3986);
        }
        return $url;
    }

    /**
     * Setup common cURL parameters
     *
     * Sets the URL, headers and CA.  If a client certificate is supplied in
     * the configuration add that and the private key for mutual authentication.
     * If a username and password are supplied set them for basic authentication.
     */
    private function curl_common($curl, $path, $query)
    {
        global $logger;

        $url = $this->url($path, $query);
        $logger->write('URL: ' . $url);

        $headers = array(
            'Accept: application/json',
        );

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CAINFO, $this->config->ssl_ca);
        if($this->config->ssl_cert) {
            curl_setopt($curl, CURLOPT_SSLCERT, $this->config->ssl_cert);
            curl_setopt($curl, CURLOPT_SSLKEY, $this->config->ssl_key);
        }
        if($this->config->username) {
            curl_setopt($curl, CURLOPT_USERPWD, $this->config->username . ':' . $this->config->password);
        }
        #curl_setopt($curl, CURLOPT_VERBOSE, true);
    }

    /**
     * Perform a GET request
     *
     * Returns the decoded JSON result output.
     */
    public function get($path, $query = False)
    {
        global $logger;

        $logger->write('=== Started GET request ===');

        $curl = curl_init();
        $this->curl_common($curl, $path, $query);

        $result = curl_exec($curl);
        curl_close($curl);
        if($result === false) {
            die('query failed');
        }

        $logger->write('Result: ' . $result);
        return json_decode($result, true);
    }

    /**
     * Perform a POST request
     *
     * Optionally accepts data. Returns the decoded JSON result output.
     */
    public function post($path, $query = False, $data = False)
    {
        global $logger;

        $logger->write('=== Started POST request ===');

        $curl = curl_init();
        $this->curl_common($curl, $path, $query);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        if(isset($data)) {
            $logger->write('Data: ' . $data);
        }

        $result = curl_exec($curl);
        curl_close($curl);
        if($result === false) {
            die('query failed');
        }

        $logger->write('Result: ' . $result);
        return json_decode($result, true);
    }
}

# vi: ts=4 et:
?>
