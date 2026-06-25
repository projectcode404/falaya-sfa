<?php

use App\Actions\Inventory\CreateStockLoadingAction;
use App\Actions\Inventory\PostStockLoadingAction;
use App\Actions\Sales\CreateSalesOrderAction;
use App\Actions\Sales\PostSalesOrderAction;
use App\DomainServices\DocumentNumberService;
use App\Models\Area;
use App\Models\Customer;
use App\Models\OperationalDate;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\VisitPlan;
use Database\Seeders\SettingsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => SettingsSeeder::class]);
    OperationalDate::create([
        'current_date_value' => now()->toDateString(),
        'is_closing_in_progress' => false,
        'updated_at' => now(),
    ]);
});

it('document number service generates unique numbers under sequential load', function () {
    $admin = actingAsRole('ADMIN');
    $service = app(DocumentNumberService::class);
    $numbers = [];

    for ($i = 0; $i < 20; $i++) {
        $numbers[] = DB::transaction(fn () => $service->generate('SO'));
    }

    expect(count($numbers))->toBe(count(array_unique($numbers)));
    expect($numbers[0])->toEndWith('-0001');
    expect($numbers[19])->toEndWith('-0020');
});

it('stock balance never goes negative when two loadings compete for same stock', function () {
    $admin = actingAsRole('ADMIN');

    $product = Product::create([
        'product_code' => 'KS-CONC-'.uniqid(),
        'product_name' => 'Keripik Concurrency Test',
        'unit' => 'pcs',
        'selling_price' => 5000,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    // Stok gudang hanya 50
    StockBalance::create([
        'product_id' => $product->id,
        'holder_type' => 'WAREHOUSE',
        'holder_id' => null,
        'condition' => 'GOOD',
        'qty' => 50,
        'updated_at' => now(),
    ]);

    $salesman1 = User::factory()->create(['role' => 'SALESMAN']);
    $salesman1->assignRole('SALESMAN');
    $salesman2 = User::factory()->create(['role' => 'SALESMAN']);
    $salesman2->assignRole('SALESMAN');

    $loading1 = app(CreateStockLoadingAction::class)->execute(
        $salesman1->id,
        [['product_id' => $product->id, 'qty' => 30]]
    );
    $loading2 = app(CreateStockLoadingAction::class)->execute(
        $salesman2->id,
        [['product_id' => $product->id, 'qty' => 30]]
    );

    // Simulasi race condition: keduanya coba post hampir bersamaan
    // Dalam single-process test, yang pertama harus sukses, yang kedua gagal
    $successCount = 0;
    $failCount = 0;

    try {
        app(PostStockLoadingAction::class)->execute($loading1);
        $successCount++;
    } catch (RuntimeException $e) {
        $failCount++;
    }

    try {
        app(PostStockLoadingAction::class)->execute($loading2);
        $successCount++;
    } catch (RuntimeException $e) {
        $failCount++;
    }

    // Tepat satu yang berhasil, satu yang gagal
    expect($successCount)->toBe(1);
    expect($failCount)->toBe(1);

    // Stok gudang tidak boleh negatif
    $warehouseBalance = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'WAREHOUSE')
        ->where('condition', 'GOOD')
        ->first();

    expect((float) $warehouseBalance->qty)->toBeGreaterThanOrEqual(0.0);
    expect((float) $warehouseBalance->qty)->toBe(20.0); // 50 - 30 = 20
});

it('lockForUpdate prevents overselling when two sales orders compete for same salesman stock', function () {
    $admin = actingAsRole('ADMIN');

    $area = Area::create([
        'area_name' => 'Area Concurrency',
        'area_code' => 'AC-'.uniqid(),
        'is_active' => true,
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $product = Product::create([
        'product_code' => 'KS-SELL-'.uniqid(),
        'product_name' => 'Keripik Sell Test',
        'unit' => 'pcs',
        'selling_price' => 5000,
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    $salesman = User::factory()->create(['role' => 'SALESMAN']);
    $salesman->assignRole('SALESMAN');

    // Salesman hanya punya 10 pcs
    StockBalance::create([
        'product_id' => $product->id,
        'holder_type' => 'SALESMAN',
        'holder_id' => $salesman->id,
        'condition' => 'GOOD',
        'qty' => 10,
        'updated_at' => now(),
    ]);

    $customer1 = Customer::create([
        'customer_code' => 'C1-'.uniqid(),
        'customer_name' => 'Customer 1',
        'address' => 'Jl. A',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $customer2 = Customer::create([
        'customer_code' => 'C2-'.uniqid(),
        'customer_name' => 'Customer 2',
        'address' => 'Jl. B',
        'area_id' => $area->id,
        'customer_type' => 'CASH',
        'status' => 'ACTIVE',
        'requested_by' => $salesman->id,
        'created_at' => now(),
    ]);

    $visit1 = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer1->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => false,
        'area_id_snapshot' => $area->id,
        'status' => 'IN_PROGRESS',
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    $visit2 = VisitPlan::create([
        'salesman_id' => $salesman->id,
        'customer_id' => $customer2->id,
        'operational_date' => now()->toDateString(),
        'is_planned' => false,
        'area_id_snapshot' => $area->id,
        'status' => 'IN_PROGRESS',
        'created_by' => $admin->id,
        'created_at' => now(),
    ]);

    actingAsRole('SALESMAN');

    // Dua SO masing-masing minta 8 pcs -- total 16, tapi stok hanya 10
    $so1 = app(CreateSalesOrderAction::class)->execute(
        $visit1->id, $customer1->id, $salesman->id, 'CASH',
        [['product_id' => $product->id, 'qty' => 8, 'unit_price' => 5000]]
    );
    $so2 = app(CreateSalesOrderAction::class)->execute(
        $visit2->id, $customer2->id, $salesman->id, 'CASH',
        [['product_id' => $product->id, 'qty' => 8, 'unit_price' => 5000]]
    );

    $successCount = 0;
    $failCount = 0;

    try {
        app(PostSalesOrderAction::class)->execute($so1);
        $successCount++;
    } catch (RuntimeException $e) {
        $failCount++;
    }

    try {
        app(PostSalesOrderAction::class)->execute($so2);
        $successCount++;
    } catch (RuntimeException $e) {
        $failCount++;
    }

    expect($successCount)->toBe(1);
    expect($failCount)->toBe(1);

    // Stok salesman tidak boleh negatif
    $balance = StockBalance::where('product_id', $product->id)
        ->where('holder_type', 'SALESMAN')
        ->where('holder_id', $salesman->id)
        ->where('condition', 'GOOD')
        ->first();

    expect((float) $balance->qty)->toBeGreaterThanOrEqual(0.0);
    expect((float) $balance->qty)->toBe(2.0); // 10 - 8 = 2
});

it('stock balance constraint rejects negative qty at db level', function () {
    expect(fn () => DB::table('stock_balances')->insert([
        'product_id' => 1,
        'holder_type' => 'WAREHOUSE',
        'holder_id' => null,
        'condition' => 'GOOD',
        'qty' => -1,
        'updated_at' => now(),
    ]))->toThrow(QueryException::class);
});
