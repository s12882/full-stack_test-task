<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $netAmount = fake()->randomFloat(2, 100, 50000);
        $vatAmount = round($netAmount * 0.2, 2);
        $issueDate = fake()->dateTimeBetween('-3 months', 'now');

        return [
            'number' => strtoupper(fake()->unique()->bothify('INV-####-???')),
            'supplier_name' => fake()->company(),
            'supplier_tax_id' => fake()->numerify('##########'),
            'net_amount' => $netAmount,
            'vat_amount' => $vatAmount,
            'gross_amount' => $netAmount + $vatAmount,
            'currency' => fake()->randomElement(['UAH', 'USD', 'EUR']),
            'status' => fake()->randomElement(InvoiceStatus::cases()),
            'issue_date' => $issueDate,
            'due_date' => Carbon::instance($issueDate)->addDays(fake()->numberBetween(0, 30)),
        ];
    }
}
