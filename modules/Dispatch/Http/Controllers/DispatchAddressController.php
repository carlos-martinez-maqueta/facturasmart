<?php

namespace Modules\Dispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Person;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\ApiPeruDev\Data\ServiceData;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Dispatch\Http\Requests\DispatchAddressRequest;
use Modules\Dispatch\Models\DispatchAddress;

class DispatchAddressController extends Controller
{
    public function tables()
    {
        $locations = func_get_locations();

        return [
            'locations' => $locations
        ];
    }

    public function store(DispatchAddressRequest $request)
    {
        $id = $request->input('id');
        $record = DispatchAddress::query()->firstOrNew(['id' => $id]);
        $record->fill($request->all());
        $record->save();

        return [
            'success' => true,
            'id' => $record->id
        ];
    }
    public function delete($id)
    {
        // Dispatch::where()
        //search in model Dispatch in columns [origin_address_id, delivery_address_id, sender_address_id, receiver_address_id] 
        //if exist $id
        //update to null
        Dispatch::where('origin_address_id', $id)
            ->orWhere('delivery_address_id', $id)
            ->orWhere('sender_address_id', $id)
            ->orWhere('receiver_address_id', $id)
            ->update([
                'origin_address_id' => DB::raw("CASE WHEN origin_address_id = {$id} THEN NULL ELSE origin_address_id END"),
                'delivery_address_id' => DB::raw("CASE WHEN delivery_address_id = {$id} THEN NULL ELSE delivery_address_id END"),
                'sender_address_id' => DB::raw("CASE WHEN sender_address_id = {$id} THEN NULL ELSE sender_address_id END"),
                'receiver_address_id' => DB::raw("CASE WHEN receiver_address_id = {$id} THEN NULL ELSE receiver_address_id END"),
            ]);

        DispatchAddress::query()
            ->find($id)
            ->delete();

        return [
            'success' => true,
            'message' => 'Dirección eliminada con éxito'
        ];
    }
    public function destroy($id)
    {
        DispatchAddress::query()
            ->find($id)
            ->update([
                'is_active' => false,
            ]);

        return [
            'success' => true,
            'message' => 'Dirección eliminada con éxito'
        ];
    }
    function transformAddress($person)
    {
        $address = $person->address;
        $department_id = $person->department_id;
        $province_id = $person->province_id;
        $district_id = $person->district_id;
        $location_id = null;
        if ($department_id && $province_id && $district_id) {
            $location_id = [$department_id, $province_id, $district_id];
        } else {
            $department_id = "15";
            $province_id = "1501";
            $district_id = "150101";
            $person->department_id = $department_id;
            $person->province_id = $province_id;
            $person->district_id = $district_id;
            $person->save();
            $location_id = [$department_id, $province_id, $district_id];
        }

        return [
            'location_id' => $location_id,
            'address' => $address,
        ];
    }

    public function getOptions($person_id)
    {
        $person = Person::find($person_id);
        $address = $this->transformAddress($person);
        // if($address['location_id'] == null){
        //     throw new Exception("El cliente no tiene el ubigeo en su dirección", 1);
        // }
        $addresses = DispatchAddress::query()
            ->where('person_id', $person_id)
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'location_id' => $row->location_id,
                    'address' => $row->address
                ];
            });
        //insertar address en primer lugar
        //buscar en $addresses si existe address 

        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        if ($address['location_id'] != null && $address['address'] != null) {
            $address_exist = $addresses->where('address', $address['address'])
                ->first();

            if (!$address_exist && !$is_integrate_system) {
                $dispatch_address =
                    DispatchAddress::updateOrCreate([
                        'person_id' => $person_id,
                        'address' => $address['address'],
                        'location_id' => $address['location_id'],
                    ]);
                $addresses->prepend($dispatch_address);
            } else {
                if ($is_integrate_system) {
                    $addresses = $addresses->filter(function ($row) use ($address) {
                        return $row['address'] != $address['address'] && $row['location_id'] != $address['location_id'];
                    });
                }
            }
        }
        //crear address en DispatchAddress




        return $addresses;
    }

    public function searchAddress($person_id)
    {
        $person = Person::query()->find($person_id);
        if ($person->identity_document_type_id === '1') {
            $type = 'dni';
        } elseif ($person->identity_document_type_id === '6') {
            $type = 'ruc';
        } else {
            return [
                'success' => false,
                'message' => 'No se encontró dirección'
            ];
        }
        return (new ServiceData())->service($type, $person->number);
    }
}
