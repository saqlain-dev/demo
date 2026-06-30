<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Admin\Library\BookIssued;
use DB;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\ItemUnit;
use App\Models\Admin\Tender;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ApprovalProcess;
use App\Models\PurchaseRequest;
use App\Models\VendorQuotation;
use App\Models\Admin\DisposeItem;
use App\Models\Admin\ItemVariant;
use App\Models\Admin\Procurement;
use App\Models\Admin\Library\Book;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\ApprovalProcessList;
use App\Models\WorkOrder\WorkOrder;
use App\Http\Controllers\Controller;
use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\ConsultantContract\ConsultantContract;
use App\Models\Admin\Fleet\FuelRequest;
use App\Models\Admin\Fleet\AssignVehicle;
use App\Models\Admin\Library\BookRequest;
use App\Models\Admin\Library\BookVariant;
use App\Models\Admin\Fleet\VehicleRequest;
use App\Models\PurchaseOrder\PurchaseOrder;
use Illuminate\Support\Facades\Auth;

class AdminDashboardStatsController extends Controller
{
    public function adminDashboardStats()
    {
        $this->authorizeAny([
            'dashboard-admin',
        ]);

        $data['inventary'] = $this->inventaryItems();
        $data['library'] = $this->libraryBooksStats();
        $data['vendor'] = $this->vendorStats();
        $data['fleetStats'] = $this->fleetStats();
        $data['procurementStats'] = $this->procurementStats();
        $data['notifications'] = Auth::user()->notifications;

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    private function inventaryItems(){

        $data['TotalItem'] = Item::count();
        $data['IssuedItems'] = ItemVariant::whereNotNull('assign_to_emp')->count();
        $data['Disposeditems'] = DisposeItem::count();

        $data['approvedItems']= ItemVariant::query()->where('approval_status',1)->count();
        $data['pendingItems']= ItemVariant::query()->where('approval_status',2)->count();

        $data['itemCategory'] = Item::select('item_categories.category_name as category', DB::raw('count(items.id) as item_count'))
        ->join('item_categories', 'items.category_id', '=', 'item_categories.id')
        ->groupBy('item_categories.category_name')
        ->get();

        $data['itemLocation'] = ItemVariant::select('locations.name as location', DB::raw('count(item_variants.id) as location_count'))
        ->join('locations', 'item_variants.location_id', '=', 'locations.id')
        ->groupBy('locations.name')
        ->get();

        // $data['approvalRequest']=ApprovalProcessList::query()->where('approval_process_id',29)->where('approval_request_status',1)->count();
        return $data;
    }


    private function libraryBooksStats(){

        $data['totalBooks'] = Book::count();
        $data['totalBooksWithVariants'] = BookVariant::count();

        $data['bookIssued'] = BookIssued::where('status', 1)->whereNull('return_date')->count();
        $data['totalBookRequests'] = BookRequest::where('status',1)->count();

        $data['bookCategory'] = Book::select('type_values.name as category', DB::raw('count(books.id) as book_count'))
        ->join('type_values', 'books.book_category', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();

        /*$data['bookByAuther'] = Book::select('book_author', DB::raw('count(*) as book_count'))
        ->groupBy('book_author')
        ->get();*/

        $data['bookRequestByCategory'] = Book::select('type_values.name as category', DB::raw('count(books.id) as book_request_count'))
        ->join('type_values', 'books.book_category', '=', 'type_values.id')
        ->join('book_requests', 'books.id', '=', 'book_requests.book_id')
        ->groupBy('type_values.name')
        ->get();

        $data['bookRequestByCategoryIssued'] = Book::select('type_values.name as category', DB::raw('count(books.id) as book_count'))
        ->join('type_values', 'books.book_category', '=', 'type_values.id')
        ->join('book_requests', 'books.id', '=', 'book_requests.book_id')
        ->where('book_requests.status', '=', 2)
        ->groupBy('type_values.name')
        ->get();

        return $data;
    }

    private function vendorStats()
    {
        $data['totalRegisteredVendors'] = Vendor::count();
        $data['approved'] = Vendor::where('profile_status',1)->count();
        $data['pending'] = Vendor::where('profile_status',2)->count();
        $data['rejected'] = Vendor::where('profile_status',3)->count();
        $data['draft'] = Vendor::where('profile_status',4)->count();

        return $data;
    }

    private function fleetStats()
    {
        $data['totalRegisteredVehicles'] = Vehicle::count();
        $data['totalVehiclesRequest'] = VehicleRequest::count();
        $data['totalFuelRequest'] = FuelRequest::where('approval_status',1)->count();

        $data['totalAssignedVehicles'] = Vehicle::withCount('assignments')->get()->sum('assignments_count');
        $data['totalUnassignedVehicles'] = $data['totalRegisteredVehicles'] - $data['totalAssignedVehicles'];

        $data['vehicleByType'] = Vehicle::select('type_values.name as vehicle_type', DB::raw('count(vehicles.id) as vehicle_count'))
        ->join('type_values', 'vehicles.vehicle_type', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();

        $data['vehicleByModel'] = Vehicle::select('vehicle_modal', DB::raw('count(vehicles.id) as vehicle_count'))
        ->groupBy('vehicle_modal')
        ->get();

        $data['vehicleByMake'] = Vehicle::select('vehicle_make', DB::raw('count(vehicles.id) as vehicle_count'))
        ->groupBy('vehicle_make')
        ->get();

        $data['totalAssignedVehiclesToDriver'] = AssignVehicle::whereNotNull('driver_id')
        ->with('DriverId')
        ->get()
        ->groupBy('driver_id')
        ->map(function ($assignments) {
            return [
                'driver_name' => $assignments->first()->DriverId->name,
                'vehicle_count' => $assignments->unique('vehicle_id')->count('vehicle_id'),
            ];
        })->values();

        $data['approvedVehicleRequest'] = VehicleRequest::where('approval_status',1)->count();
        $data['pendingVehicleRequest'] = VehicleRequest::where('approval_status',2)->count();
        $data['rejectedVehicleRequest'] = VehicleRequest::where('approval_status',3)->count();
        $data['draftVehicleRequest'] = VehicleRequest::where('approval_status',4)->count();

        $data['poolVehicleRequest'] = VehicleRequest::select('vehicle_request_details.pool_type', DB::raw('count(vehicle_requests.id) as vehicle_count'))
        ->join('vehicle_request_details', 'vehicle_requests.id', '=', 'vehicle_request_details.vehicle_request_id')
        ->where('vehicle_request_details.pool_type', 1)
        // ->where('vehicle_requests.approval_status', '!=', 1)
        ->groupBy('vehicle_request_details.pool_type')
        ->get();

        $data['nonPoolVehicleRequest'] = VehicleRequest::select('vehicle_request_details.pool_type', DB::raw('count(vehicle_requests.id) as vehicle_count'))
        ->join('vehicle_request_details', 'vehicle_requests.id', '=', 'vehicle_request_details.vehicle_request_id')
        ->where('vehicle_request_details.pool_type', 2)
        // ->where('vehicle_requests.approval_status', '!=', 1)
        ->groupBy('vehicle_request_details.pool_type')
        ->get();

        $data['totalFuelsRequest'] = FuelRequest::count();
        $data['approvedFuelRequest'] = FuelRequest::where('approval_status',1)->count();
        $data['unApprovedFuelRequest'] = FuelRequest::whereNot('approval_status',1)->count();

        $data['fuelRequestsByVehicle'] = FuelRequest::select('vehicles.vehicle_number', DB::raw('count(fuel_requests.id) as fuel_request_count'))
        ->join('vehicles', 'fuel_requests.vehicle_id', '=', 'vehicles.id')
        ->groupBy('vehicles.vehicle_number')
        ->get();

        return $data;
    }


    private function procurementStats()
    {
        $data['procurementPlans'] = Procurement::count();
        $data['draftPlans']= Procurement::where('approval_status',4)->count();
        $data['pendingPlans']= Procurement::where('approval_status',2)->count();
        $data['approvedPlans']= Procurement::where('approval_status',1)->count();
        $data['rejectPlans']= Procurement::where('approval_status',3)->count();

        $data['purchaseRequests'] = PurchaseRequest::count();

        $data['draftpurchaseRequests']= PurchaseRequest::where('pr_approval_status',4)->count();
        $data['pendingpurchaseRequests']= PurchaseRequest::where('pr_approval_status',2)->count();
        $data['approvedpurchaseRequests']= PurchaseRequest::where('pr_approval_status',1)->count();
        $data['rejectpurchaseRequests']= PurchaseRequest::where('pr_approval_status',3)->count();

        $data['activeTenderWithBids'] = Tender::select('name')->where('approval_status', 2)->withCount('vendor_quotations')->get();
        $data['totalVendors'] = Vendor::count();
        $data['activeTenders'] = Tender::where('approval_status', 2)->count();
        $data['InActiveTenders'] = Tender::where('approval_status', 1)->count();

        $data['totalAirTravelRequest'] = AirTravelRequest::count();

        $data['approvedAirTravelRequest'] = AirTravelRequest::where('approval_status',1)->count();
        $data['pendingAirTravelRequest'] = AirTravelRequest::where('approval_status',2)->count();
        $data['rejectedAirTravelRequest'] = AirTravelRequest::where('approval_status',3)->count();
        $data['draftAirTravelRequest'] = AirTravelRequest::where('approval_status',4)->count();
        $data['vendorAtrQuotation'] = AirTravelRequest::select('traveler_name')->withCount('vendorAtrQuotation')->get();
        $data['airTravelReqVendor'] = AirTravelRequest::select('traveler_name')->withCount('airTravelReqVendor')->get();

        $data['airTravelReqByDepartment'] = AirTravelRequest::select('type_values.name as department', DB::raw('count(air_travel_requests.id) as air_travel_count'))
        ->join('type_values', 'air_travel_requests.department_id', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();

        $data['airTravelReqByProject'] = AirTravelRequest::select('type_values.name as project', DB::raw('count(air_travel_requests.id) as air_travel_count'))
        ->join('type_values', 'air_travel_requests.project_id', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();

        $data['atrByAirlineCategory'] = AirTravelRequest::select('type_values.name as category', DB::raw('count(air_travel_requests.id) as atr_count'))
        ->join('type_values', 'air_travel_requests.airline_category_id', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();

        $data['purchaseOrder'] = PurchaseOrder::count();
        $data['workOrder'] = WorkOrder::count();
        $data['consultantContract'] = ConsultantContract::count();

        return $data;
    }
}
