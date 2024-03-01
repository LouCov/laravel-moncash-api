<?php

namespace LouCov\LaravelMonCashApi\Models;

/**
 * Order
 */
class Order
{
    private mixed $orderId;

    private int $amount;

    /**
     * __construct
     *
     * @param  mixed $amount
     * @param  mixed $orderId
     * @return void
     */
    public function __construct( int $amount, mixed $orderId ) {

        $this->amount = $amount;
        $this->orderId = $orderId;
    }

    /**
     * getAmount
     *
     * @return int
     */
    public function getAmount() : int {

        return $this->amount;
    }

    /**
     * getOrderId
     *
     * @return mixed
     */
    public function getOrderId() : mixed {

        return $this->orderId;
    }

    /**
     * setAmount
     *
     * @param  mixed $amount
     * @return void
     */
    public function setAmount( int $amount) : void {

        $this->amount = $amount;
    }

    /**
     * setOrderId
     *
     * @param  mixed $orderId
     * @return void
     */
    public function setOrderId( mixed $orderId ) : void {

        $this->orderId = $orderId;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray() : array {

        return [
            "amount" => $this->getAmount(),
            "orderId" => $this->getOrderId()
        ];
    }
}
