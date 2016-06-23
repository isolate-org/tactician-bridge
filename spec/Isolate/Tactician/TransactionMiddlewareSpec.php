<?php

namespace spec\Isolate\Tactician;

use Isolate\Isolate;
use Isolate\PersistenceContext;
use Isolate\PersistenceContext\Factory;
use Isolate\PersistenceContext\Name;
use Isolate\Tests\SingleTransactionContextStub;
use League\Tactician\Middleware;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TransactionMiddlewareSpec extends ObjectBehavior
{
    function let(Factory $contextFactory)
    {
        $isolate = new Isolate($contextFactory->getWrappedObject());
        $this->beConstructedWith($isolate);
    }

    function it_is_middleware()
    {
        $this->shouldImplement(Middleware::class);
    }

    function it_opens_transaction_before_next_middleware_and_commit_after_it(Factory $contextFactory, PersistenceContext\Transaction $transaction)
    {
        $isolate = new Isolate($contextFactory->getWrappedObject());
        $this->beConstructedWith($isolate, "custom-context-name");

        $transaction->commit()->shouldBeCalled();
        $context = new SingleTransactionContextStub($transaction->getWrappedObject());
        $contextFactory->create('custom-context-name')->willReturn($context);

        $this->execute("command", function($command) { return true; })->shouldReturn(true);
    }

    function it_opens_transaction_and_rollback_if_after_exception_from_next_middleware(Factory $contextFactory, PersistenceContext\Transaction $transaction)
    {
        $transaction->commit()->shouldNotBeCalled();
        $transaction->rollback()->shouldBeCalled();
        $context = new SingleTransactionContextStub($transaction->getWrappedObject());
        $contextFactory->create(Argument::type(Name::class))->willReturn($context);

        $this->shouldThrow(\Exception::class)
            ->during("execute", ["command", function($command) { throw new \Exception(); }]);
    }

    function it_not_opens_transaction_and_rollback_if_transaction_already_opened(Factory $contextFactory, PersistenceContext\Transaction $transaction)
    {
        $transaction->commit()->shouldNotBeCalled();
        $context = SingleTransactionContextStub::createOpened($transaction->getWrappedObject());
        $contextFactory->create(Argument::type(Name::class))->willReturn($context);

        $this->execute("command", function($command) { return true; })->shouldReturn(true);
    }
}
