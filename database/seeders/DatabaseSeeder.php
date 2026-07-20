<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\VendorPackingStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PainterBooking;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'System Admin',
            'email' => 'admin@paintstore.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $customer = User::query()->create([
            'name' => 'John Customer',
            'email' => 'customer@paintstore.com',
            'password' => Hash::make('password'),
            'phone' => '9876543210',
            'address' => '123 Main Street, Mumbai',
            'role' => UserRole::Customer,
            'is_active' => true,
            'is_verified' => true,
        ]);

        $painter = User::query()->create([
            'name' => 'Raj Painter',
            'email' => 'painter@paintstore.com',
            'password' => Hash::make('password'),
            'phone' => '9876543211',
            'bio' => 'Professional painter with 10+ years experience in interior and exterior painting.',
            'experience_years' => 10,
            'experience_text' => '10 years',
            'cost_per_hour' => 500.00,
            'aadhar_number' => '123456789012',
            'specialization' => 'Residential Painting',
            'role' => UserRole::Painter,
            'is_active' => true,
            'is_verified' => true,
        ]);

        $agent = User::query()->create([
            'name' => 'Amit Delivery',
            'email' => 'delivery@paintstore.com',
            'password' => Hash::make('password'),
            'phone' => '9876543212',
            'license_number' => 'DL-1234567890',
            'vehicle_number' => 'MH01AB1234',
            'role' => UserRole::DeliveryAgent,
            'is_active' => true,
            'is_verified' => true,
        ]);

        $vendor = User::query()->create([
            'name' => 'Priya Sharma',
            'business_name' => 'Sharma Paint Supplies',
            'email' => 'vendor@paintstore.com',
            'password' => Hash::make('password'),
            'phone' => '9876543213',
            'address' => '45 Market Road, Mumbai',
            'role' => UserRole::Vendor,
            'is_active' => true,
            'is_verified' => true,
        ]);

        $categories = collect([
            ['name' => 'Interior Paints', 'description' => 'Premium interior wall paints'],
            ['name' => 'Exterior Paints', 'description' => 'Weather-resistant exterior paints'],
            ['name' => 'Primers', 'description' => 'Surface preparation primers'],
            ['name' => 'Wood Finishes', 'description' => 'Varnishes and wood coatings'],
        ])->map(function (array $data) {
            return Category::query()->create([
                ...$data,
                'slug' => Str::slug($data['name']),
                'is_active' => true,
            ]);
        });

        $products = [
            ['name' => 'Royal Emulsion White', 'category' => 0, 'price' => 450.00, 'stock' => 100, 'vendor' => true],
            ['name' => 'Weather Shield Blue', 'category' => 1, 'price' => 680.00, 'stock' => 75, 'vendor' => true],
            ['name' => 'Wall Primer 10L', 'category' => 2, 'price' => 320.00, 'stock' => 50],
            ['name' => 'Teak Wood Varnish', 'category' => 3, 'price' => 890.00, 'stock' => 30, 'vendor' => true],
            ['name' => 'Silk Glow Cream', 'category' => 0, 'price' => 520.00, 'stock' => 60],
            ['name' => 'Exterior Red Oxide', 'category' => 1, 'price' => 410.00, 'stock' => 80, 'vendor' => true],
        ];

        foreach ($products as $data) {
            $product = Product::query()->create([
                'category_id' => $categories[$data['category']]->id,
                'vendor_id' => ($data['vendor'] ?? false) ? $vendor->id : null,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => "High quality {$data['name']} for professional results.",
                'price' => $data['price'],
                'stock_quantity' => $data['stock'],
                'is_active' => true,
            ]);

            ProductImage::query()->create([
                'product_id' => $product->id,
                'image_path' => 'products/placeholder.jpg',
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        $order = Order::query()->create([
            'order_number' => 'ORD-'.strtoupper(Str::random(10)),
            'customer_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'delivery_agent_id' => $agent->id,
            'total_amount' => 970.00,
            'payment_method' => PaymentMethod::Online,
            'payment_status' => PaymentStatus::Paid,
            'order_status' => OrderStatus::Assigned,
            'delivery_status' => DeliveryStatus::Accepted,
            'vendor_packing_status' => VendorPackingStatus::Packed,
            'packing_deadline_at' => now()->addMinutes(30),
            'packed_at' => now()->subMinutes(10),
            'delivery_deadline_at' => now()->addMinutes(20),
            'shipping_address' => $customer->address,
            'shipping_phone' => $customer->phone,
        ]);

        $product1 = Product::query()->first();
        $product2 = Product::query()->skip(1)->first();

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'product_name' => $product1->name,
            'unit_price' => $product1->price,
            'quantity' => 1,
            'subtotal' => $product1->price,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'product_name' => $product2->name,
            'unit_price' => $product2->price,
            'quantity' => 1,
            'subtotal' => $product2->price,
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'razorpay_payment_id' => 'pay_'.Str::random(14),
            'amount' => 970.00,
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        PainterBooking::query()->create([
            'booking_number' => 'BK-'.strtoupper(Str::random(10)),
            'customer_id' => $customer->id,
            'painter_id' => $painter->id,
            'booking_date' => now()->addDays(3)->toDateString(),
            'booking_time' => '10:00',
            'address' => $customer->address,
            'notes' => 'Living room and bedroom painting needed.',
            'status' => BookingStatus::Assigned,
        ]);
    }
}
