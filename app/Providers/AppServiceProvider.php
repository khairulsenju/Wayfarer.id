<?php

namespace App\Providers;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Blade::directive('rupiah', function (string $expression) {
            return "<?php echo \\App\\Services\\PricingService::rupiah((int) ({$expression})); ?>";
        });

        View::composer('layouts.customer', function ($view) {
            $count = 0;

            if (Auth::check() && Auth::user()->isCustomer()) {
                $count = (int) CartItem::query()
                    ->whereHas('cart', function ($query) {
                        $query->where('user_id', Auth::id());
                    })
                    ->sum('qty');
            }

            $view->with('cartCount', $count);
        });
    }
}