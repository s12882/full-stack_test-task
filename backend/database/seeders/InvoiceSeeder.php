<?php

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Invoice::factory()->count(12)->create();

        Invoice::factory()->create(['status' => InvoiceStatus::Pending]);
        Invoice::factory()->create(['status' => InvoiceStatus::Approved]);
        Invoice::factory()->create(['status' => InvoiceStatus::Rejected]);
    }
}
