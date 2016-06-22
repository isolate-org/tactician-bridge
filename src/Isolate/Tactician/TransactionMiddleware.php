<?php

namespace Isolate\Tactician;

use Isolate\Isolate;
use League\Tactician\Middleware;

final class TransactionMiddleware implements Middleware
{
    /**
     * @var Isolate
     */
    private $isolate;
    /**
     * @var string
     */
    private $contextName;

    /**
     * @param Isolate $isolate
     * @param string $contextName
     */
    public function __construct(Isolate $isolate, $contextName = Isolate::DEFAULT_CONTEXT)
    {
        $this->isolate = $isolate;
        $this->contextName = $contextName;
    }

    /**
     * @param object $command
     * @param callable $next
     * @return mixed
     * @throws \Exception
     */
    public function execute($command, callable $next)
    {
        $context = $this->isolate->getContext($this->contextName);

        if ($context->hasOpenTransaction()) {
            return $next($command);
        }

        $transaction = $context->openTransaction();

        try {
            $returnValue = $next($command);

            $context->closeTransaction();

            return $returnValue;
        } catch (\Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }
}