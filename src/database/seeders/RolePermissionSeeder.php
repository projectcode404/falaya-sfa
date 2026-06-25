<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Master Data
            'customer.view', 'customer.create', 'customer.approve',
            'customer.edit', 'customer.deactivate',
            'product.view', 'product.manage',
            'area.view', 'area.manage',
            'visit_schedule.view', 'visit_schedule.manage',
            'user.view', 'user.manage',
            'settings.view', 'settings.manage',

            // Inventory - Loading/Unloading
            'stock_loading.view', 'stock_loading.create',
            'stock_loading.post', 'stock_loading.cancel',
            'stock_unloading.view', 'stock_unloading.create', 'stock_unloading.post',
            'stock_balance.view',

            // Inventory - Approval flow
            'stock_adjustment.view', 'stock_adjustment.create', 'stock_adjustment.approve',
            'customer_return.view', 'customer_return.create',
            'customer_return.approve', 'customer_return.process_refund',
            'stock_writeoff.view', 'stock_writeoff.create', 'stock_writeoff.approve',

            // Visit
            'visit_plan.view', 'visit.checkin', 'visit.checkout',
            'visit.create_unplanned', 'visit.edit_status_manual',

            // Sales & Invoice
            'sales_order.view', 'sales_order.create', 'sales_order.post',
            'sales_order.cancel', 'sales_order.void',
            'credit_override.request', 'credit_override.approve',
            'invoice.view', 'invoice.print',

            // Collection
            'collection_task.view', 'collection_task.create_manual',
            'payment.view', 'payment.create_cash', 'payment.create_transfer',
            'payment.post', 'payment.void',
            'payment_receipt.view', 'payment_receipt.download',

            // Cash Reconciliation
            'cash_reconciliation.view', 'cash_reconciliation.process',

            // Closing
            'daily_closing.view', 'daily_closing.execute',

            // Reporting
            'dashboard.view_owner', 'dashboard.view_admin',
            'report.sales', 'report.stock', 'report.visit_compliance',
            'report.ar_outstanding', 'report.collection_risk',
            'report.bad_stock_summary', 'report.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $owner = Role::firstOrCreate(['name' => 'OWNER']);
        $owner->syncPermissions([
            'customer.view', 'customer.approve',
            'product.view', 'area.view', 'visit_schedule.view',
            'user.view', 'settings.view', 'settings.manage',
            'stock_loading.view', 'stock_unloading.view', 'stock_balance.view',
            'stock_adjustment.view', 'stock_adjustment.approve',
            'customer_return.view', 'customer_return.approve',
            'stock_writeoff.view', 'stock_writeoff.approve',
            'visit_plan.view',
            'sales_order.view', 'sales_order.void',
            'credit_override.approve',
            'invoice.view', 'invoice.print',
            'collection_task.view', 'payment.view', 'payment.void',
            'payment_receipt.view',
            'cash_reconciliation.view',
            'daily_closing.view',
            'dashboard.view_owner',
            'report.sales', 'report.stock', 'report.visit_compliance',
            'report.ar_outstanding', 'report.collection_risk',
            'report.bad_stock_summary', 'report.export',
        ]);

        $admin = Role::firstOrCreate(['name' => 'ADMIN']);
        $admin->syncPermissions([
            'customer.view', 'customer.edit', 'customer.deactivate',
            'product.view', 'product.manage',
            'area.view', 'area.manage',
            'visit_schedule.view', 'visit_schedule.manage',
            'user.view', 'user.manage', 'settings.view',
            'stock_loading.view', 'stock_loading.create',
            'stock_loading.post', 'stock_loading.cancel',
            'stock_unloading.view', 'stock_unloading.create', 'stock_unloading.post',
            'stock_balance.view',
            'stock_adjustment.view', 'stock_adjustment.create',
            'customer_return.view', 'customer_return.process_refund',
            'stock_writeoff.view', 'stock_writeoff.create',
            'visit_plan.view', 'visit.edit_status_manual',
            'sales_order.view', 'sales_order.void',
            'invoice.view', 'invoice.print',
            'collection_task.view', 'collection_task.create_manual',
            'payment.view', 'payment.create_transfer', 'payment.post',
            'payment_receipt.view', 'payment_receipt.download',
            'cash_reconciliation.view', 'cash_reconciliation.process',
            'daily_closing.view', 'daily_closing.execute',
            'dashboard.view_admin',
            'report.sales', 'report.stock', 'report.visit_compliance',
            'report.ar_outstanding', 'report.collection_risk',
            'report.bad_stock_summary', 'report.export',
        ]);

        $salesman = Role::firstOrCreate(['name' => 'SALESMAN']);
        $salesman->syncPermissions([
            'customer.view', 'customer.create',
            'product.view', 'area.view', 'visit_schedule.view',
            'stock_loading.view', 'stock_unloading.view', 'stock_balance.view',
            'customer_return.view', 'customer_return.create',
            'visit_plan.view', 'visit.checkin', 'visit.checkout',
            'visit.create_unplanned',
            'sales_order.view', 'sales_order.create',
            'sales_order.post', 'sales_order.cancel',
            'credit_override.request',
            'invoice.view',
            'collection_task.view',
            'payment.view', 'payment.create_cash', 'payment.post',
            'payment_receipt.view', 'payment_receipt.download',
        ]);

        Role::firstOrCreate(['name' => 'MANAGER']);
    }
}
