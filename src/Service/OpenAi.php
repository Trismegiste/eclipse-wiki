<?php

/*
 * eclipse-wiki
 */

namespace App\Service;

/**
 * OpenAi API
 */
class OpenAi
{

    protected $secretKey;
    protected $engine;

    public function __construct(string $secret, string $engine = 'davinci')
    {
        $this->secretKey = $secret;
        $this->engine = $engine;
    }

    public function request(string $prompt, int $max_tokens = 64): string
    {

        $request_body = [
            "prompt" => $prompt,
            "max_tokens" => $max_tokens,
            "temperature" => 0.7,
            "top_p" => 1,
            "presence_penalty" => 0,
            "frequency_penalty" => 0,
            "best_of" => 1,
            "stream" => false,
        ];

        $postfields = json_encode($request_body);
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.openai.com/v1/engines/{$this->engine}/completions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->secretKey
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \RuntimeException("Error #:" . $err);
        }

        return (json_decode($response))->choices[0]->text;
    }

}