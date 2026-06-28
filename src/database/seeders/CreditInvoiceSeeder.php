<?php

namespace Database\Seeders;

use App\Actions\Sales\CreateSalesOrderAction;
use App\Actions\Sales\PostSalesOrderAction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\VisitPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class CreditInvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $salesman = User::where('email', 'salesman@falaya.test')->first();

        if (! $salesman) {
            return;
        }

        $creditCustomers = Customer::where('customer_type', 'CREDIT')
            ->where('status', 'ACTIVE')
            ->get();

        if ($creditCustomers->isEmpty()) {
            return;
        }

        $product = Product::where('is_active', true)->first();

        if (! $product) {
            return;
        }

        $previousUser = Auth::user();
        Auth::login($salesman);

        foreach ($creditCustomers as $customer) {
            $balance = StockBalance::where('product_id', $product->id)
                ->where('holder_type', 'SALESMAN')
                ->where('holder_id', $salesman->id)
                ->where('condition', 'GOOD')
                ->first();

            $availableQty = $balance ? (float) $balance->qty : 0;

            // Pakai sebagian kecil dari credit_limit supaya outstanding
            // realistis (terpakai sebagian, bukan langsung melebihi limit).
            $orderQty = min(5, max(1, (int) floor($availableQty / 2)));

            if ($orderQty < 1) {
                // Stok salesman tidak cukup (StockLoadingSeeder belum jalan
                // atau qty terlalu kecil) -- skip customer ini, jangan
                // membuat data yang melanggar validasi stok.
                continue;
            }

            // VisitPlan dummy khusus untuk demo outstanding ini --
            // is_planned=false (unplanned), status IN_PROGRESS supaya
            // konsisten dengan precondition Sales Order pada umumnya.
            $visitPlan = VisitPlan::create([
                'salesman_id' => $salesman->id,
                'customer_id' => $customer->id,
                'operational_date' => now()->toDateString(),
                'is_planned' => false,
                'area_id_snapshot' => $customer->area_id,
                'visit_schedule_id' => null,
                'status' => 'IN_PROGRESS',
                'created_by' => $salesman->id,
                'created_at' => now(),
            ]);

            $salesOrder = app(CreateSalesOrderAction::class)->execute(
                $visitPlan->id,
                $customer->id,
                $salesman->id,
                'CREDIT',
                [[
                    'product_id' => $product->id,
                    'qty' => $orderQty,
                    'unit_price' => (float) $product->selling_price,
                ]],
                'Demo Seeder'
            );

            app(PostSalesOrderAction::class)->execute($salesOrder);
        }

        if ($previousUser) {
            Auth::login($previousUser);
        } else {
            Auth::logout();
        }
    }
}
