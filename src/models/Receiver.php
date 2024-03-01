<?php

namespace LouCov\LaravelMonCashApi\Models;


/**
 * Receiver
 */
class Receiver
{
    private mixed $receiver;

    private int $amount;

    private string $desc;

    /**
     * __construct
     *
     * @param  mixed $amount
     * @param  mixed $receiver
     * @param  mixed $desc
     * @return void
     */
    public function __construct( int $amount, mixed $receiver, string $desc ) {

        $this->amount = $amount;
        $this->receiver = $receiver;
        $this->desc = $desc;
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
     * getReceiver
     *
     * @return mixed
     */
    public function getReceiver() : mixed {

        return $this->receiver;
    }

    /**
     * getDesc
     *
     * @return string
     */
    public function getDesc() : string {

        return $this->desc;
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
     * setReceiver
     *
     * @param  mixed $receiver
     * @return void
     */
    public function setReceiver( mixed $receiver) : void {

        $this->receiver = $receiver;
    }

    /**
     * setDesc
     *
     * @param  mixed $desc
     * @return void
     */
    public function setDesc( string $desc ) : void {
        $this->desc = $desc;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray() : array {
        return [
            "amount" => $this->getAmount(),
            "receiver" => $this->getReceiver(),
            "desc" => $this->getDesc()
        ];
    }
}
