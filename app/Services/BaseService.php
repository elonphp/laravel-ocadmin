<?php

namespace App\Services;

use App\Exceptions\CustomException;

abstract class BaseService
{
    /**
     * 拋出自訂錯誤（業務邏輯錯誤，預設 400）
     *
     * @param string $generalError 給一般用戶看的友善訊息
     * @param string|null $sysError 給工程師看的詳細訊息
     * @param int $statusCode HTTP 狀態碼
     * @throws CustomException
     */
    protected function fail(
        string $generalError,
        ?string $sysError = null,
        int $statusCode = 400
    ): never {
        throw new CustomException($generalError, $sysError, $statusCode);
    }

    /**
     * 拋出系統錯誤（非預期錯誤，固定 500）
     *
     * @param string $generalError 給一般用戶看的友善訊息
     * @param string|null $sysError 給工程師看的詳細訊息
     * @throws CustomException
     */
    protected function error(
        string $generalError = '系統發生錯誤，請聯絡管理員。',
        ?string $sysError = null
    ): never {
        throw new CustomException($generalError, $sysError, 500);
    }

    /**
     * 拋出找不到資源的錯誤（預設 404）
     *
     * @param string $generalError 給一般用戶看的友善訊息
     * @param string|null $sysError 給工程師看的詳細訊息
     * @throws CustomException
     */
    protected function notFound(
        string $generalError = '找不到指定的資料。',
        ?string $sysError = null
    ): never {
        throw new CustomException($generalError, $sysError, 404);
    }

    /**
     * 拋出權限不足的錯誤（預設 403）
     *
     * @param string $generalError 給一般用戶看的友善訊息
     * @param string|null $sysError 給工程師看的詳細訊息
     * @throws CustomException
     */
    protected function forbidden(
        string $generalError = '您沒有權限執行此操作。',
        ?string $sysError = null
    ): never {
        throw new CustomException($generalError, $sysError, 403);
    }

    /**
     * 拋出服務不可用的錯誤（預設 503）
     *
     * @param string $generalError 給一般用戶看的友善訊息
     * @param string|null $sysError 給工程師看的詳細訊息
     * @throws CustomException
     */
    protected function unavailable(
        string $generalError = '服務暫時無法使用，請稍後再試。',
        ?string $sysError = null
    ): never {
        throw new CustomException($generalError, $sysError, 503);
    }
}
