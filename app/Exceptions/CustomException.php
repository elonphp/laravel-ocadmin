<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    protected string $generalError;
    protected ?string $sysError;
    protected int $statusCode;

    public function __construct(
        string $generalError = '系統發生錯誤，請聯絡管理員。',
        ?string $sysError = null,
        int $statusCode = 500,
        ?\Throwable $previous = null
    ) {
        $this->generalError = $generalError;
        $this->sysError = $sysError;
        $this->statusCode = $statusCode;

        parent::__construct($sysError ?? $generalError, $statusCode, $previous);
    }

    public function getGeneralError(): string
    {
        return $this->generalError;
    }

    public function getSysError(): ?string
    {
        return $this->sysError;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * 業務邏輯錯誤（400）
     */
    public static function fail(string $generalError, ?string $sysError = null): static
    {
        throw new static($generalError, $sysError, 400);
    }

    /**
     * 找不到資源（404）
     */
    public static function notFound(string $generalError, ?string $sysError = null): static
    {
        throw new static($generalError, $sysError, 404);
    }

    /**
     * 權限不足（403）
     */
    public static function forbidden(string $generalError, ?string $sysError = null): static
    {
        throw new static($generalError, $sysError, 403);
    }

    /**
     * 服務不可用（503）
     */
    public static function unavailable(string $generalError, ?string $sysError = null): static
    {
        throw new static($generalError, $sysError, 503);
    }

    /**
     * 系統錯誤（500）
     */
    public static function error(string $generalError, ?string $sysError = null): static
    {
        throw new static($generalError, $sysError, 500);
    }
}
