<?php

declare(strict_types=1);

namespace Freema\GA4MeasurementProtocolBundle\Dto\Response;

use Freema\GA4MeasurementProtocolBundle\Exception\HydrationException;
use Psr\Http\Message\ResponseInterface;

class BaseResponse extends AbstractResponse
{
    /**
     * @var int
     */
    protected int $statusCode;

    /**
     * @var string
     */
    protected string $reasonPhrase;

    /**
     * @var string|null
     */
    protected ?string $body = null;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * BaseResponse constructor.
     * @param ResponseInterface|null $response
     * @throws HydrationException
     */
    public function __construct(?ResponseInterface $response = null)
    {
        if ($response) {
            $this->hydrate($response);
        }
    }

    /**
     * @return array
     */
    public function export(): array
    {
        return [
            'status_code' => $this->getStatusCode(),
            'reason_phrase' => $this->getReasonPhrase(),
            'body' => $this->getBody(),
            'headers' => $this->getHeaders()
        ];
    }

    /**
     * @param ResponseInterface|array $blueprint
     * @throws HydrationException
     */
    public function hydrate($blueprint)
    {
        if ($blueprint instanceof ResponseInterface) {
            $this->hydrateFromResponseInterface($blueprint);
        } else {
            throw new HydrationException('Unsupported hydration source');
        }
    }

    /**
     * @param ResponseInterface $response
     */
    protected function hydrateFromResponseInterface(ResponseInterface $response)
    {
        $this->setStatusCode($response->getStatusCode());
        $this->setReasonPhrase($response->getReasonPhrase());
        $this->setBody((string)$response->getBody());

        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }
        $this->setHeaders($headers);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return BaseResponse
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @param string $reasonPhrase
     * @return BaseResponse
     */
    public function setReasonPhrase(string $reasonPhrase): self
    {
        $this->reasonPhrase = $reasonPhrase;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string|null $body
     * @return BaseResponse
     */
    public function setBody(?string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return BaseResponse
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }
}
