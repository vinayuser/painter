<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable([
    'name', 'business_name', 'email', 'phone', 'address', 'bio', 'avatar', 'role', 'is_active', 'is_verified',
    'password', 'experience_years', 'experience_text', 'cost_per_hour', 'aadhar_number',
    'specialization', 'license_number', 'vehicle_number', 'fcm_token', 'fcm_topics_subscribed',
])]
#[Hidden(['password', 'remember_token', 'fcm_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'fcm_topics_subscribed' => 'boolean',
            'experience_years' => 'integer',
            'cost_per_hour' => 'decimal:2',
        ];
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role?->value,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function isPainter(): bool
    {
        return $this->role === UserRole::Painter;
    }

    public function isDeliveryAgent(): bool
    {
        return $this->role === UserRole::DeliveryAgent;
    }

    public function isVendor(): bool
    {
        return $this->role === UserRole::Vendor;
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function assignedDeliveries(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_agent_id');
    }

    public function vendorProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function vendorOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'vendor_id');
    }

    public function painterBookings(): HasMany
    {
        return $this->hasMany(PainterBooking::class, 'customer_id');
    }

    public function assignedBookings(): HasMany
    {
        return $this->hasMany(PainterBooking::class, 'painter_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(PainterPortfolio::class, 'painter_id');
    }

    public function completedJobsCount(): int
    {
        return $this->assignedBookings()->where('status', \App\Enums\BookingStatus::Completed)->count();
    }
}
