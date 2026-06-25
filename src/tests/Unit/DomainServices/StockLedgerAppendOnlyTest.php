<?php

use App\Models\StockLedger;

it('stock ledger throws exception when update is called', function () {
    $ledger = new StockLedger;
    expect(fn () => $ledger->update(['qty' => 10]))->toThrow(LogicException::class);
});
