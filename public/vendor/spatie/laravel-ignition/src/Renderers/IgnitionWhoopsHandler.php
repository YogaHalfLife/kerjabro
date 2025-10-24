<?php

namespace Spatie\LaravelIgnition\Renderers;

use Error;
use ErrorException;
use Throwable;
use Whoops\Handler\Handler;

class IgnitionWhoopsHandler extends Handler
{
    protected ErrorPageRenderer $errorPageHandler;

    protected Throwable $exception;

    public function __construct(ErrorPageRenderer $errorPageHandler)
    {
        $this->errorPageHandler = $errorPageHandler;
    }

    public function handle(): ?int
    {
        try {
            $this->errorPageHandler->render($this->exception);
        } catch (Error $error) {

            throw new ErrorException(
                $error->getMessage(),
                $error->getCode(),
                1,
                $error->getFile(),
                $error->getLine(),
                $error
            );
        }

        return Handler::QUIT;
    }

    /** @param \Throwable $exception */
    public function setException($exception): void
    {
        $this->exception = $exception;
    }
}
