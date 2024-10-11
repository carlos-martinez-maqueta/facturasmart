<?php

namespace Modules\Item\Http\Controllers;

use App\Models\Tenant\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Item\Models\ItemLotsGroup;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\Warehouse;
use Carbon\Carbon;
use Modules\Item\Exports\ItemLotGroupExport;
use Modules\Item\Http\Resources\ItemLotGroupCollection;
use Modules\Item\Models\ItemLotsGroupState;

class ItemLotsGroupController extends Controller
{


    public function index()
    {
        return view('item::item-lots-group.index');
    }


    public function columns()
    {
        return [
            'code' => 'Lote',
            'date_of_due' => 'Fecha',
            'item_description' => 'Producto',
        ];
    }
    public function update_warehouse(Request $request)
    {
        $lot_id = $request->lot_id;
        $warehouse_id = $request->warehouse_id;

        ItemLotsGroup::where('id', $lot_id)->update(["warehouse_id" => $warehouse_id]);

        return ["success" => true];
    }
    public function update_state(Request $request)
    {
        $lot_id = $request->lot_id;
        $state_id = $request->state_id;

        ItemLotsGroup::where('id', $lot_id)->update(["state_id" => $state_id]);

        return ["success" => true];
    }
    public function export(Request $request)
    {

        $records = $this->getRecords($request)->get();

        return (new ItemLotGroupExport)
            ->records($records)
            ->download('Lotes_' . Carbon::now() . '.xlsx');
    }
    public function tables(Request $request)
    {

        $states = ItemLotsGroupState::all();
        $warehouses = Warehouse::all();
        return compact('states', 'warehouses');
    }

    public function records(Request $request)
    {

        $records = $this->getRecords($request);

        return new ItemLotGroupCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function getRecords($request)
    {

        if ($request->column == 'item_description') {

            $records = ItemLotsGroup::whereHas('item', function ($query) use ($request) {
                $query->where('description', 'like', "%{$request->value}%")->latest();
            });
        } else {
            $records = ItemLotsGroup::where($request->column, 'like', "%{$request->value}%")->latest();
        }

        return $records;
    }


    public function record($id)
    {
        $record = ItemLotsGroup::findOrFail($id);

        return $record;
    }

    public function reStoreWarehouse(){
        $records = Inventory::whereNotNull('lot_code')->get();

        foreach ($records as $record) {
            $warehouse_id = $record->warehouse_id;
            $item_lot_group = ItemLotsGroup::where('code', $record->lot_code)->first();
            if($item_lot_group){
                $item_lot_group->warehouse_id = $warehouse_id;
                $item_lot_group->save();
            }
        }

        $lots_nulls = ItemLotsGroup::whereNull('warehouse_id')->get();
        foreach ($lots_nulls as $lot_null) {
            $item_id = $lot_null->item_id;
            $item_warehouse = ItemWarehouse::where('item_id', $item_id)->get();
            if(count($item_warehouse) == 1){
                $lot_null->warehouse_id = $item_warehouse[0]->warehouse_id;
                $lot_null->save();
            }
        }
        return [
            'success' => true,
            'message' => 'Almacenes actualizados con éxito',
        ];
    }

    public function store(Request $request)
    {

        $id = $request->input('id');
        $record = ItemLotsGroup::findOrFail($id);
        $record->code = $request->code;
        $record->state_id = $request->state_id;
        $record->warehouse_id = $request->warehouse_id;
        $record->date_of_due = Carbon::parse($request->date_of_due)->format("Y-m-d");
        $record->save();

        return [
            'success' => true,
            'message' => 'Lote editado con éxito',
        ];
    }

    public function getAvailableItemLotsGroup($item_id)
    {
        return ItemLotsGroup::where('item_id', $item_id)
            ->get()
            ->transform(function ($row) {
                return $row->getRowResourceSale();
            });
    }
}
