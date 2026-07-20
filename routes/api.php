<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PainterBookingController;
use App\Http\Controllers\Api\PainterController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Public product browsing
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::get('categories', [ProductController::class, 'categories']);
    Route::get('categories/{categoryId}/products', [ProductController::class, 'byCategory']);

    // Customer auth (OTP)
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('auth/resend-otp', [AuthController::class, 'resendOtp']);

    // Staff auth (painter & delivery agent — OTP)
    Route::post('auth/staff/register', [AuthController::class, 'staffRegister']);
    Route::post('auth/staff/login', [AuthController::class, 'staffLogin']);

    // Refresh must stay outside auth:api — expired tokens can still be renewed here
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    Route::middleware('auth:api')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/fcm-token', [AuthController::class, 'updateFcmToken']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);

        // ── Customer APIs ──────────────────────────────────────────
        Route::middleware('role:customer')->prefix('customer')->group(function (): void {
            Route::get('profile', [ProfileController::class, 'show']);
            Route::post('profile', [ProfileController::class, 'update']);

            Route::get('addresses', [AddressController::class, 'index']);
            Route::post('addresses', [AddressController::class, 'store']);
            Route::get('addresses/{id}', [AddressController::class, 'show']);
            Route::put('addresses/{id}', [AddressController::class, 'update']);
            Route::delete('addresses/{id}', [AddressController::class, 'destroy']);
            Route::post('addresses/{id}/default', [AddressController::class, 'setDefault']);

            Route::get('cart', [CartController::class, 'index']);
            Route::get('cart/checkout', [CartController::class, 'checkout']);
            Route::post('cart', [CartController::class, 'store']);
            Route::post('cart/{productId}/increment', [CartController::class, 'increment']);
            Route::post('cart/{productId}/decrement', [CartController::class, 'decrement']);
            Route::put('cart/{productId}', [CartController::class, 'update']);
            Route::delete('cart/{productId}', [CartController::class, 'destroy']);

            Route::get('orders', [OrderController::class, 'index']);
            Route::post('orders', [OrderController::class, 'store']);
            Route::get('orders/{id}', [OrderController::class, 'show']);
            Route::post('orders/{id}/pay', [OrderController::class, 'initiatePayment']);
            Route::post('payments/verify', [OrderController::class, 'verifyPayment']);

            Route::get('painters', [PainterController::class, 'index']);
            Route::get('painters/{id}', [PainterController::class, 'show']);

            Route::get('bookings', [PainterBookingController::class, 'customerIndex']);
            Route::post('bookings', [PainterBookingController::class, 'store']);
            Route::get('bookings/{id}', [PainterBookingController::class, 'show']);
        });

        // ── Painter APIs ───────────────────────────────────────────
        Route::middleware('role:painter')->prefix('painter')->group(function (): void {
            Route::get('profile', [ProfileController::class, 'show']);
            Route::post('profile', [ProfileController::class, 'update']);

            Route::get('bookings', [PainterBookingController::class, 'painterIndex']);
            Route::get('bookings/{id}', [PainterBookingController::class, 'show']);
            Route::post('bookings/{id}/accept', [PainterBookingController::class, 'accept']);
            Route::post('bookings/{id}/reject', [PainterBookingController::class, 'reject']);
            Route::post('bookings/{id}/start', [PainterBookingController::class, 'startWork']);
            Route::post('bookings/{id}/before-images', [PainterBookingController::class, 'uploadBeforeImages']);
            Route::post('bookings/{id}/after-images', [PainterBookingController::class, 'uploadAfterImages']);
            Route::post('bookings/{id}/complete', [PainterBookingController::class, 'uploadWorkImages']);
        });

        // ── Delivery Agent APIs ────────────────────────────────────
        Route::middleware('role:delivery_agent')->prefix('delivery')->group(function (): void {
            Route::get('profile', [ProfileController::class, 'show']);
            Route::post('profile', [ProfileController::class, 'update']);

            Route::get('orders', [DeliveryController::class, 'index']);
            Route::get('orders/{id}', [DeliveryController::class, 'show']);
            Route::post('orders/{id}/accept', [DeliveryController::class, 'accept']);
            Route::patch('orders/{id}/status', [DeliveryController::class, 'updateStatus']);
            Route::post('orders/{id}/complete', [DeliveryController::class, 'complete']);
        });

        // ── Vendor APIs ────────────────────────────────────────────
        Route::middleware('role:vendor')->prefix('vendor')->group(function (): void {
            Route::get('orders', [\App\Http\Controllers\Api\VendorController::class, 'orders']);
            Route::get('orders/{id}', [\App\Http\Controllers\Api\VendorController::class, 'showOrder']);
            Route::post('orders/{id}/pack', [\App\Http\Controllers\Api\VendorController::class, 'markPacked']);
        });
    });
});
