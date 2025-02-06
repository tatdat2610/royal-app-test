<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiConnector
{
    private HttpClientInterface $httpClient;
    private string $apiBaseUrl = 'https://candidate-testing.com/api/v2';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function request(string $method, string $endpoint, array $options = [], string $token = null)
    {
        try {
            // Check if the request requires authentication
            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }

            if (!isset($options['headers']['Authorization']) && $token) {
                $options['headers']['Authorization'] = 'Bearer ' . $token;
            }

            $response = $this->httpClient->request($method, $this->apiBaseUrl . $endpoint, $options);

            return $response->toArray();
        } catch (\Exception $e) {
            //write the log
        }
    }

    public function login(string $email, string $password): ?array
    {
        try {
            $response = $this->httpClient->request('POST', $this->apiBaseUrl . '/token', [
                'json' => [
                    'email' => $email,
                    'password' => $password
                ]
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }
}