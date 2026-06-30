<?php
namespace App\Providers;
use App\Models\Admin\Tender;
use App\Models\Admin\ItemVariant;
use App\Observers\TenderObserver;
use App\Observers\ItemVariantObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\Admin\PurchaseRequestRFQ;
use App\Observers\AssignVehicleObserver;
use App\Models\Admin\Fleet\AssignVehicle;
use App\Observers\PurchaseRequestRFQObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }
    public function boot(): void
    {
        AssignVehicle::observe(AssignVehicleObserver::class);
        PurchaseRequestRFQ::observe(PurchaseRequestRFQObserver::class);; 
        ItemVariant::observe(ItemVariantObserver::class);
        Tender::observe(TenderObserver::class);
    }

}
