<?php

namespace App\Application;

class CurlService
{
    /** @var string */
    private $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    public function __invoke(string $url, array $data): string
    {
        //create a new cURL resource
        $ch = curl_init($this->domain . $url);

        curl_setopt($ch, CURLOPT_POST, 1);
        //attach encoded JSON string to the POST fields
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        //return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //execute the POST request
        $result = curl_exec($ch);

        //close cURL resource
        curl_close($ch);

        return $result;
    }
}