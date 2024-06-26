<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\InwardRequest;
use App\Http\Requests\ConsumptionRequest;
use App\Models\Consumption;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Inward;
use App\Models\Item;
use App\Models\PurchaseBook;
use Illuminate\Http\Request;
use Carbon\Carbon; 

class InwardController extends Controller
{
    public function index(){

        $data = Inward::with(['account', 'item'])->where('vehicle_status','pending')->latest()->get();
        return response()->json($data);
    }
    

    public function inward_index(Request $req){
        $data = array(
            'title'     => 'Inwards',
            'accounts'  => Account::latest()->get(),
            'items'     => Item::latest()->get(),
            //'purchases' => PurchaseBook::with(['account', 'item'])->latest()->get(),
            'inwards'   => Inward::with(['account', 'item'])
                                    ->when(isset($req->parent_id), function($query) use ($req){
                                        $query->where('account_id', hashids_decode($req->parent_id));
                                    })
                                    ->when(isset($req->vehicle_no), function($query) use ($req){
                                        $query->where('vehicle_no', $req->vehicle_no);
                                    })
                                    ->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                    })
                                    ->latest()->get(),
            'account_types' => AccountType::whereNull('parent_id')->get(), 

        );
        return view('admin.weighbridge.add_inward')->with($data);
    }

    

    public function get_account(Request $req){
        
        $inward = Inward::findOrFail($req->id);
            
        return response()->json($inward);
        
    }

    public function all_items(){
        $data = Item::where('type','purchase')->latest()->get();
        return response()->json($data);
    }

    public function all_accounts(){
        $data = Account::latest()->get();
        return response()->json($data);
    }

    public function purchase_accounts(){
        $data = Item::where('type','purchase')->latest()->get();
        return response()->json(
            [
                'list'=> $data,
            ]);
    }

    public function save_inward(InwardRequest $req){
        
        if(check_empty($req->inward_id)){
            $inward = Inward::findOrFail($req->inward_id);
            $msg      = 'Inward data udpated successfully';
        }else{
            $inward = new Inward();
            $msg      = 'Inward data added successfully';
        }

        
        $inward->date              = $req->date;
        $inward->item_id           = $req->item_name;
        $inward->account_id        = $req->account_name;
        $inward->vehicle_no        = $req->vehicle_no;
        $inward->vehicle_status    = "completed";
        $inward->no_of_bags        = $req->no_of_begs;
        $inward->fare              = $req->fare_value;
        $inward->bilty_no          = $req->bilty_no;
        $inward->company_weight    = $req->gross_weight - $req->tare_weight;
        $inward->party_weight       = $req->party_weight;
        $inward->first_weight      = $req->gross_weight;
        $inward->second_weight     = $req->tare_weight;
        $inward->party_weight_difference = $req->party_weight - ($req->gross_weight - $req->tare_weight)  ;
        
        $inward->remarks           = $req->remarks;
        $inward->save();

        return response()->json([
            'success'   => $msg,
            'redirect'    => route('admin.inwards.index')
        ]);
 
    }


    public function save(InwardRequest $req){
        
        $inward = new Inward();
        
        //dd($req->inward_weight);
        $inward->date              = $req->date;
        $inward->item_id           = $req->item_name;
        $inward->account_id        = $req->account_name;
        $inward->vehicle_no        = $req->vehicle_no;
        $inward->vehicle_status    = "pending";
        $inward->no_of_bags        = $req->no_of_begs;
        $inward->fare              = $req->fare_value;
        $inward->bilty_no          = $req->bilty_no;
        $inward->gp_no             = $req->gp_no;
        $inward->company_weight    = 0;
        $inward->party_weight    = $req->party_weight;
        $inward->first_weight      = $req->gross_weight;
        $inward->second_weight     = 0;
        $inward->weight_difference = 0;
        $inward->party_weight_difference = $req->party_weight - $req->gross_weight  ;
        
        $inward->remarks           = $req->remarks;
        $inward->save();

        if(isset($req->inward_weight) ){

            return response()->json([
                'success'   => 'Inward data added successfully',
                'reload'    => true
            ]);

        }else{

            return response()->json("success");
        }
        
        
        
    }

    public function inward_list(Request $req){
        
        $data = array(
            'title'     => 'Inwards',
            'accounts'  => Account::latest()->get(),
            'items'     => Item::latest()->get(),
            // 'purchases' => PurchaseBook::with(['account', 'item'])->latest()->get(),
            'inwards'   => Inward::with(['account', 'item'])
                                    ->when(isset($req->parent_id), function($query) use ($req){
                                        $query->where('account_id', hashids_decode($req->parent_id));
                                    })
                                    ->when(isset($req->vehicle_no), function($query) use ($req){
                                        $query->where('vehicle_no', $req->vehicle_no);
                                    })
                                    ->when(isset($req->from_date, $req->to_date), function($query) use ($req){
                                        $query->whereBetween('date', [$req->from_date, $req->to_date]);
                                    })
                                    ->latest()->get(),
            'account_types' => AccountType::whereNull('parent_id')->get(),

        );
        return view('admin.weighbridge.all_inwards')->with($data);
    }


    public function edit_inward(InwardRequest $req){
        
        //dd($req->all());
        
        $inward = Inward::findOrFail($req->inward_id);
            
        $inward->date              = $req->date;
        $inward->item_id           = $req->item_name;
        $inward->account_id        = $req->account_name;
        $inward->vehicle_no        = $req->vehicle_no;
        $inward->vehicle_status    = "completed";
        $inward->no_of_bags        = $req->no_of_begs;
        $inward->fare              = $req->fare_value;
        $inward->bilty_no          = $req->bilty_no;
        $inward->first_weight      = $req->gross_weight;
        $inward->second_weight     = $req->tare_weight;
        $inward->company_weight    = $req->gross_weight - $req->tare_weight;
        $inward->weight_difference = $req->gross_weight - $req->tare_weight;
        
        $inward->remarks           = $req->remarks;
        $inward->save();


            return response()->json("success");
        
        
    }
    
    public function store(PurchaseRequest $req){
        
        if(check_empty($req->purchase_id)){
            $purchase = PurchaseBook::findOrFail(hashids_decode($req->purchase_id));
            $msg      = 'Purchase udpated successfully';
        }else{
            $purchase = new PurchaseBook();
            $msg      = 'Purchase added successfully';
        }
        // dd($req->all());
        $purchase->date              = $req->purchase_date;
        $purchase->vehicle_no        = $req->vehicle_no;
        $purchase->bilty_no          = $req->bilty_no;
        $purchase->pro_inv_no        = $req->prod_inv_no;
        $purchase->account_id        = hashids_decode($req->account_id);
        $purchase->item_id           = hashids_decode($req->item_id);
        $purchase->company_weight    = $req->company_weight;
        $purchase->party_weight      = $req->party_weight;
        $purchase->weight_difference = $req->weight_difference;
        $purchase->posted_weight     = $req->posted_weight;
        $purchase->bag_rate              = $req->rate;
        // $purchase->gross_ammount     = $req->gross_ammount;
        $purchase->fare              = $req->fare;
        // $purchase->others_charges    = $req->others_charges;
        $purchase->net_ammount       = $req->net_ammount;
        $purchase->remarks           = $req->remarks;
        $purchase->save();

        Item::find(hashids_decode($req->item_id))->increment('stock_qty', $req->company_weight);//increment item stock
        
        return response()->json([
            'success'   => $msg,
            'redirect'    => route('admin.purchases.index')
        ]);
        
    }

    public function edit(Request $req){
        
        $inward = Inward::findOrFail($req->id);
            
        return response()->json($inward);
        
    }

    public function editinward($id){
        
            
        $data = array(
            'title'     => 'Inwards',
            'accounts'  => Account::latest()->get(),
            'items'     => Item::latest()->get(),
            
            'account_types' => AccountType::whereNull('parent_id')->get(), 
            'edit_inward' => Inward::with(['item'])->findOrFail(hashids_decode($id)),
            'inwards'   => Inward::with(['account', 'item'])->latest()->get(),
            'is_update'     => true
        );
        return view('admin.weighbridge.add_inward')->with($data);

    }
    
    public function get_items(Request $req){
       
        $inward = Item::findOrFail($req->id);
            
        return response()->json($inward);
        
    }

    public function consumption(Request $req){
        //dd($req->weight);
        
        $consumption = new Consumption();
        $msg        = 'Consumption added successfully';
        

        $consumption->item_id  = $req->id;
        $consumption->qunantity = $req->weight;
        $consumption->date     = Carbon::today();
        $consumption->save();
        
        return response()->json([
            'success'   => $msg,
            'redirect'  => route('admin.consumptions.index'),
        ]);
        
    }

    public function delete($id){
        PurchaseBook::destroy(hashids_decode($id));
        return response()->json([
            'success'   => 'Purcahase deleted successfully',
            'reload'    => true
        ]);
    }

    public function delete_inward ($id){
        Inward::destroy(hashids_decode($id));
        return response()->json([
            'success'   => 'Inward deleted successfully',
            'reload'    => true
        ]);
    }

    public function deleteinward($id){
        Inward::destroy(hashids_decode($id));
        return response()->json([
            'success'   => 'Inward deleted successfully',
            'reload'    => true
        ]);
    }
}
