<?php

namespace Reliva\Nexgen;

class NexgenResponse
{
    public bool $success;
    public array $data;


    /**
     * @param bool $success
     * @param array $data
     */
    public function __construct(bool $success, array $data)
    {
        $this->success = $success;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
        ];
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }



}