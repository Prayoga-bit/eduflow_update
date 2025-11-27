<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForumException extends Exception
{
    /**
     * The HTTP status code to use for the response.
     *
     * @var int
     */
    protected $statusCode = Response::HTTP_BAD_REQUEST;

    /**
     * The error code for the API response.
     *
     * @var string
     */
    protected $errorCode;

    /**
     * Additional data to include in the response.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  string|null  $errorCode
     * @param  int  $statusCode
     * @param  array  $data
     * @param  \Throwable|null  $previous
     * @return void
     */
    public function __construct(
        string $message = 'An error occurred',
        ?string $errorCode = null,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        array $data = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        
        $this->errorCode = $errorCode ?? 'forum_error';
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    /**
     * Report the exception.
     *
     * @return bool
     */
    public function report(): bool
    {
        // Don't report to logs by default
        return false;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode,
        ];

        if (!empty($this->data)) {
            $response['data'] = $this->data;
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => $this->getTrace(),
            ];
        }

        return response()->json($response, $this->statusCode);
    }

    /**
     * Create a new not found exception.
     *
     * @param  string  $message
     * @param  string|null  $errorCode
     * @param  array  $data
     * @return static
     */
    public static function notFound(
        string $message = 'Resource not found',
        ?string $errorCode = 'not_found',
        array $data = []
    ): self {
        return new static($message, $errorCode, Response::HTTP_NOT_FOUND, $data);
    }

    /**
     * Create a new unauthorized exception.
     *
     * @param  string  $message
     * @param  string|null  $errorCode
     * @param  array  $data
     * @return static
     */
    public static function unauthorized(
        string $message = 'Unauthorized',
        ?string $errorCode = 'unauthorized',
        array $data = []
    ): self {
        return new static($message, $errorCode, Response::HTTP_UNAUTHORIZED, $data);
    }

    /**
     * Create a new forbidden exception.
     *
     * @param  string  $message
     * @param  string|null  $errorCode
     * @param  array  $data
     * @return static
     */
    public static function forbidden(
        string $message = 'Forbidden',
        ?string $errorCode = 'forbidden',
        array $data = []
    ): self {
        return new static($message, $errorCode, Response::HTTP_FORBIDDEN, $data);
    }

    /**
     * Create a new validation exception.
     *
     * @param  array  $errors
     * @param  string  $message
     * @param  string|null  $errorCode
     * @return static
     */
    public static function validation(
        array $errors,
        string $message = 'The given data was invalid.',
        ?string $errorCode = 'validation_failed'
    ): self {
        return new static($message, $errorCode, Response::HTTP_UNPROCESSABLE_ENTITY, [
            'errors' => $errors,
        ]);
    }

    /**
     * Get the error code.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get the status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the additional data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
