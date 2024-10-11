@php
    $establishment = $document->establishment;
    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;
    
    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }
    
    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }
    
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    
    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
    // dd($document->items);
@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>

    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td width="10%">
                    <img src="data:{{ mime_content_type(public_path("storage/uploads/logos/{$company->logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}"))) }}"
                        alt="{{ $company->name }}" alt="{{ $company->name }}" class="company_logo" style="max-width: 300px">
                </td>
            @else
                <td width="10%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td width="50%" class="pl-3">
                <div class="text-left">
                    <h4 class="">{{ $company->name }}</h4>
                    <h5 style="text-transform: uppercase;">
                        {{ $establishment->address !== '-' ? $establishment->address : '' }}
                        {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                        {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                        {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                    </h5>
                    <h5>{{ $establishment->email !== '-' ? $establishment->email : '' }}</h5>
                    <h5>{{ $establishment->telephone !== '-' ? $establishment->telephone : '' }}</h5>
                </div>
            </td>
            <td width="40%" class="border-box bg-blue-light" style="padding: 0px;">
                <table width="100%">
                    <tr>
                        <td class="text-center h4">
                            R.U.C. N° {{ $company->number }}
                        </td>
                    </tr>
                    <tr>
                        <td class=" h4 text-center">
                            GUÍA DE REMISIÓN TRANSPORTISTA
                        </td>
                    </tr>
                    <tr>
                        <td class="h3 text-center">
                            {{ $document_number }}
                        </td>
                    </tr>
                </table>


            </td>
        </tr>
    </table>
    <table class="full-width mt-10 ">
        <thead>
            <tr>
                <th colspan="2" class="bg-blue-light text-left p-5">
                    DATOS GENERALES
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%"  class="text-left desc">
                    <strong>Punto de partida:</strong> <br>
                    {{ optional($document->sender_address_data)['address'] }}

                    @if(isset($document->sender_address_data)&&isset($document->sender_address_data['location_id']))
                    <br> {{ func_get_location($document->sender_address_data['location_id'] ) }}
                    @endif
                </td>
                <td width="50%" class="text-left desc">
                <strong>
                        Punto de llegada:
                    </strong> <br>
                    {{ optional($document->receiver_address_data)['address'] }}

                    @if(isset($document->receiver_address_data)&&isset($document->receiver_address_data['location_id']))
                    <br> {{ func_get_location($document->receiver_address_data['location_id'] ) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td  class="text-left desc">
                    <strong>Fecha de emisión:</strong>
                    {{ $document->date_of_issue->format('d/m/Y') }}
                </td>
                <td  class="text-left desc">
                    <strong>Fecha de traslado:</strong>
                    {{ $document->date_of_shipping->format('d/m/Y') }}
                </td>
            </tr>
            
            <tr>
                <td colspan="2" class="text-left desc">
                    <strong>Documentos relacionados:</strong><br>
                    @foreach ($document->dispatches_related as $related)
                    Guía remitente {{ $related->serie_number }} RUC: {{ $related->company_number }}<br>
                    @endforeach
                </td>
            

            </tr>

        </tbody>
    </table>
    <table class="full-width mt-10 ">
        <thead>
            <tr>
                <th colspan="2" class="bg-blue-light text-left p-5">
                    DATOS DEL REMITENTE
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%"  class="text-left desc ">
                    <strong>Nombre o razón social:</strong> <br>
                    {{ $document->sender_data ? $document->sender_data['name'] : '' }}
                </td>
                <td width="50%" class="text-left desc">
                    <strong>
                        Tipo y número de identificación:
                    </strong> <br>
                    {{ $document->sender_data ? $document->sender_data['identity_document_type_description'] : '' }}-
                    {{ $document->sender_data ? $document->sender_data['number'] : '' }}
                </td>
            </tr>
        </tbody>
    </table>
    <table class="full-width mt-10 ">
        <thead>
            <tr>
                <th colspan="2" class="bg-blue-light text-left p-5">
                    DATOS DEL DESTINATARIO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%"  class="text-left desc ">
                    <strong>Nombre o razón social:</strong> <br>
                    {{ $document->receiver_data ? $document->receiver_data['name'] : '' }}
                </td>
                <td width="50%"  class="text-left desc">
                    <strong>
                        Tipo y número de identificación:
                    </strong> <br>
                    {{ $document->receiver_data ? $document->receiver_data['identity_document_type_description'] : '' }}-
                    {{ $document->receiver_data ? $document->receiver_data['number'] : '' }}
                </td>
            </tr>
        </tbody>
    </table>
    <table class="full-width mt-10 ">
        <thead>
            <tr>
                <th colspan="2" class="bg-blue-light text-left p-5">
                    DATOS DEL TRANSPORTE Y TRASLADO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="50%"  class="text-left desc ">
                    <strong>Número de bultos:</strong> {{ $document->packages_number }} bultos
                </td>
                <td width="50%"  class="text-left desc">
                    <strong>Peso bruto total:</strong> {{ $document->total_weight }} {{ $document->unit_type_id }}
                </td>
            </tr>

            @if ($company->mtc_auth)
                <tr>
                    <td width="50%" colspan="2" class="text-left desc "><strong>Número de autorización MTC:</strong> {{ $company->mtc_auth }}</td>
                </tr>
            @endif
            @if ($document->transport_data)
                <tr>
                    <td width="50%"  class="text-left desc "><strong>Número de placa del vehículo:</strong> {{ $document->transport_data['plate_number'] }}</td>
                    @if (isset($document->transport_data['auth_plate_primary']))
                        <td width="50%"  class="text-left desc "><strong>Autorización de placa principal:</strong> {{ $document->transport_data['auth_plate_primary'] }}</td>
                    @endif
                </tr>
                <tr>
                    @if (isset($document->secondary_transport_data['secondary_plate_number']))
                        <td width="50%" class="text-left desc "><strong>Número de placa secundaria del vehículo:</strong>
                            {{ $document->secondary_transport_data['secondary_plate_number'] }}</td>
                    @else
                        @if (isset($document->transport_data['secondary_plate_number']))
                            <td width="50%"  class="text-left desc "><strong>Número de placa secundaria del vehículo:</strong>
                                {{ $document->transport_data['secondary_plate_number'] }}</td>
                        @endif
                    @endif
                    @if (isset($document->secondary_transport_data['auth_plate_secondary']))
                        <td width="50%" class="text-left desc "><strong>Autorización de placa secundaria:</strong>
                            {{ $document->secondary_transport_data['auth_plate_secondary'] }}</td>
                    @else
                        @if (isset($document->transport_data['auth_plate_secondary']))
                            <td width="50%"  class="text-left desc "><strong>Autorización de placa secundaria:</strong>
                                {{ $document->transport_data['auth_plate_secondary'] }}
                            </td>
                        @endif
                    @endif
                </tr>
                @if ($document->driver->name)
                    <tr>
                        <td width="50%"  class="text-left desc "><strong>Nombre Conductor:</strong> {{ $document->driver->name }}</td>
                        <td width="50%"  class="text-left desc "><strong>Documento Conductor:</strong> {{ $document->driver->number }}</td>
                    </tr>
                @endif
                @isset ($document->secondary_driver->name)
                    <tr>
                        <td width="50%"  class="text-left desc "><strong>Nombre Conductor Secundario:</strong> {{ $document->secondary_driver->name }}</td>
                        <td width="50%"  class="text-left desc "><strong>Documento Conductor Secundario:</strong> {{ $document->secondary_driver->number }}</td>
                    </tr>
                @endisset
                <tr>
                    <td width="50%" colspan="2" class="text-left desc "><strong>Modelo del vehículo:</strong> {{ $document->transport_data['model'] }}</td>
                </tr>
            @endif
            @if ($document->tracto_carreta)
                <tr>
                    <td width="50%" colspan="2" class="text-left desc "><strong>Marca de tracto carreta:</strong> {{ $document->tracto_carreta }}</td>
                </tr>
            @endif


            @if ($document->driver->license)
                <tr>
                    <td width="50%" colspan="2" class="text-left desc "><strong>Licencia del conductor:</strong> {{ $document->driver->license }}</td>
                </tr>
            @endif

            @if ($document->observations)
                <tr>
                    <td width="50%" colspan="2" class="text-left desc "><strong>Observaciones:</strong> {{ $document->observations }}</td>
                </tr>
            @endif
            
        </tbody>
    </table>

    <table class="full-width mt-10">
        <thead>
            <tr>
                <th class="border-box text-center bg-grey desc">N°</th>
                <th class="border-box text-center bg-grey desc">Código</th>
                <th class="border-box text-center bg-grey desc">
                    Descripción
                </th>
                <th class="border-box text-center bg-grey desc">Unidad</th>
                <th class="border-box text-center bg-grey desc">Cantidad</th>
                <th class="border-box text-center bg-grey desc">Peso</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $idx => $row)
                <tr>
                    <td class="border-box bg-grey-light text-center desc">{{ $idx + 1 }}</td>
                    <td class="border-box bg-grey-light text-center desc">{{ $row->item->internal_id }}</td>
                    <td class="border-box bg-grey-light text-left desc">{{ $row->item->description }}</td>
                    <td class="border-box bg-grey-light text-center desc">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="border-box bg-grey-light text-right desc">{{ $row->quantity }}</td>
                    <td class="border-box bg-grey-light text-right desc">{{ $row->item->weight }} {{ $document->unit_type_id }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($document['qr'])
    <table class="full-width">
        <tr>
            <td class="text-left">
                <img src="data:image/png;base64, {{ $document['qr'] }}" style="margin-right: -10px;"/>
            </td>
        </tr>
    </table>
@endif



</body>

</html>
