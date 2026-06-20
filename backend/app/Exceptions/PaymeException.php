<?php

namespace App\Exceptions;

use Exception;

/**
 * Payme JSON-RPC xato kodlari.
 */
class PaymeException extends Exception
{
    public const INVALID_AMOUNT = -31001;
    public const TRANSACTION_NOT_FOUND = -31003;
    public const CANT_PERFORM = -31008;
    public const ACCOUNT_NOT_FOUND = -31050; // order_id topilmadi
    public const ORDER_NOT_PAYABLE = -31051;
    public const INVALID_AUTH = -32504;
    public const METHOD_NOT_FOUND = -32601;

    public array $data;

    public function __construct(int $code, string $message = '', array $data = [])
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    public function toRpc(?int $id = null): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $this->getCode(),
                'message' => [
                    'ru' => $this->getMessage(),
                    'uz' => $this->getMessage(),
                    'en' => $this->getMessage(),
                ],
                'data' => $this->data['field'] ?? null,
            ],
        ];
    }
}
