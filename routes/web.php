<?php

use App\Http\Controllers\Admin\AddressController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CartItemController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryAgentController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PainterBookingController;
use App\Http\Controllers\Admin\PainterController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Delivery\AuthController as DeliveryAuthController;
use App\Http\Controllers\Delivery\DashboardController as DeliveryDashboardController;
use App\Http\Controllers\Delivery\OrderController as DeliveryOrderController;
use App\Http\Controllers\FirebaseMessagingSwController;
use App\Http\Controllers\Vendor\AuthController as VendorAuthController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/login');
Route::get('firebase-messaging-sw.js', FirebaseMessagingSwController::class);

// ── Admin Panel ──────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware(['auth:web', 'admin'])->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

        Route::resource('users', UserController::class);
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{user}', [CustomerController::class, 'show'])->name('customers.show');
        Route::resource('vendors', VendorController::class)->except(['destroy']);
        Route::get('painters', [PainterController::class, 'index'])->name('painters.index');
        Route::get('painters/{user}', [PainterController::class, 'show'])->name('painters.show');
        Route::resource('delivery-agents', DeliveryAgentController::class)->except(['destroy']);
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'edit', 'update']);
        Route::resource('bookings', PainterBookingController::class)->only(['index', 'show', 'edit', 'update']);
        Route::get('addresses', [AddressController::class, 'index'])->name('addresses.index');
        Route::get('carts', [CartItemController::class, 'index'])->name('carts.index');
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{order}', [PaymentController::class, 'show'])->name('payments.show');

        Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/broadcast', [AdminNotificationController::class, 'broadcast'])->name('notifications.broadcast');
        Route::post('notifications/test-self', [AdminNotificationController::class, 'testSelf'])->name('notifications.test-self');
        Route::post('notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('notifications/{id}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('fcm-token', [\App\Http\Controllers\Web\FcmTokenController::class, 'store'])->name('fcm-token');
    });
});

// ── Vendor Panel ─────────────────────────────────────────────
Route::prefix('vendor')->name('vendor.')->group(function (): void {
    Route::get('login', [VendorAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [VendorAuthController::class, 'login']);

    Route::middleware(['auth:web', 'vendor'])->group(function (): void {
        Route::post('logout', [VendorAuthController::class, 'logout'])->name('logout');
        Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');
        Route::resource('products', VendorProductController::class)->except(['show']);
        Route::get('orders', [VendorOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/pack', [VendorOrderController::class, 'markPacked'])->name('orders.pack');
        Route::get('notifications', [\App\Http\Controllers\Vendor\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [\App\Http\Controllers\Vendor\NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('notifications/{id}/read', [\App\Http\Controllers\Vendor\NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('fcm-token', [\App\Http\Controllers\Web\FcmTokenController::class, 'store'])->name('fcm-token');
    });
});

// ── Delivery Panel ───────────────────────────────────────────
Route::prefix('delivery')->name('delivery.')->group(function (): void {
    Route::get('login', [DeliveryAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [DeliveryAuthController::class, 'login']);

    Route::middleware(['auth:web', 'delivery.panel'])->group(function (): void {
        Route::post('logout', [DeliveryAuthController::class, 'logout'])->name('logout');
        Route::get('/', [DeliveryDashboardController::class, 'index'])->name('dashboard');
        Route::get('orders', [DeliveryOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [DeliveryOrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/deliver', [DeliveryOrderController::class, 'markDelivered'])->name('orders.deliver');
        Route::get('notifications', [\App\Http\Controllers\Delivery\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [\App\Http\Controllers\Delivery\NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('notifications/{id}/read', [\App\Http\Controllers\Delivery\NotificationController::class, 'markRead'])->name('notifications.read');
    });
});
