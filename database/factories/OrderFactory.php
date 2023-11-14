<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{

    protected $model = Order::class;

    public function definition()
    {
        return [
            "total_price" => $this->faker->randomFloat(2, 1, 100),
            "status" => $this->faker->randomElement(['pending', 'processing', 'completed']),
            "created_by" => $this->faker->numberBetween(1, 10),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            $order->orderDetails()->create([
                "first_name" => $this->faker->firstName(),
                "last_name" => $this->faker->lastName(),
                "phone" => $this->faker->phoneNumber(),
                "address1" => $this->faker->address(),
                "address2" => $this->faker->address(),
                "city" => $this->faker->city(),
                'state' => $this->faker->state(),
                'zipcode' => $this->faker->postcode(),
                'country_code' => $this->faker->countryCode(),
            ]);

            $order->orderItems()->create([
                'order_id' => $order->id,
                'product_id' => $this->faker->numberBetween(1, 10),
                'quantity' => $this->faker->numberBetween(1, 10),
                'unit_price' => $this->faker->randomFloat(2, 1, 100),
            ]);




        });
    }
}