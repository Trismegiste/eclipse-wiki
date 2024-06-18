<?php

/*
 * Eclipse Wiki
 */

namespace App\Service;

use Iterator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Description of LlmIterator
 *
 * @author florent
 */
class LlmIterator implements Iterator
{

    protected array $stream;

    public function __construct(
            protected HttpClientInterface $client,
            protected string $model,
            protected string $prompt)
    {
        $this->fill();
    }

    public function current(): mixed
    {
        return json_decode($this->stream[0])->response;
    }

    public function key(): mixed
    {
        return json_decode($this->stream[0])->created_at;
    }

    public function next(): void
    {
        $item = array_shift($this->stream);
        $token = json_decode($item);
        if ((count($this->stream) === 0) && !$token->done) {
            $this->fill($token->context);
        }
    }

    public function rewind(): void
    {
        
    }

    public function valid(): bool
    {
        return count($this->stream);
    }

    protected function fill(array $context = []): void
    {
        $request = [
            'model' => $this->model,
            'prompt' => $this->prompt,
            'stream' => true
        ];
        if (count($context)) {
            $request['context'] = $context;
        }

        $response = $this->client->request('POST', '/api/generate', ['json' => $request]);
        $this->stream = preg_split("#\n+#", $response->getContent(), flags: PREG_SPLIT_NO_EMPTY);
    }

}
