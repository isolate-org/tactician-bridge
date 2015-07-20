<?php

namespace Isolate\Tests;

use Isolate\PersistenceContext;
use Isolate\PersistenceContext\Name;
use Isolate\PersistenceContext\Transaction;

class SingleTransactionContextStub implements PersistenceContext
{
    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }
    
    /**
     * @return Name
     */
    public function getName()
    {
        return 'stub';
    }

    /**
     * @return Transaction
     */
    public function openTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return boolean
     */
    public function hasOpenTransaction()
    {
        return true;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return void
     */
    public function closeTransaction()
    {
        $this->transaction->commit();
    }
}