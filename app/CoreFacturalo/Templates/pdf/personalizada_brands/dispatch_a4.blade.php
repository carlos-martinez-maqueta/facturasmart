@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');

	    // $document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
        $transport_principal_plate_number = null;
        $transport_principal_tuce_number = null;
        $transport_principal_authorization_entity_id = null;
        $transport_principal_authorization_entity_number = null;
        $transport_secondaries = [];

    if ($document->transport_mode_type_id === '02') {
        $transport_principal = $document->transports->first();
        if($transport_principal) {
            $transport_principal_plate_number = $transport_principal->transport_data['plate_number'];
            $transport_principal_tuce_number = $transport_principal->transport_data['tuce_number'];
            $transport_principal_authorization_entity_id = $transport_principal->transport_data['authorization_entity_id'];
            $transport_principal_authorization_entity_number = $transport_principal->transport_data['authorization_entity_number'];

            $transport_secondaries = $document->transports
                ->where('transport_id', '<>', $transport_principal->transport_id)
                ->transform(function ($row) {
                    return [
                        'plate_number' => $row->transport_data['plate_number'],
                        'tuce_number' => $row->transport_data['tuce_number'],
                        'authorization_entity_id' => $row->transport_data['authorization_entity_id'],
                        'authorization_entity_number' => $row->transport_data['authorization_entity_number']
                    ];
                });
        }
    }

    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    //$document_type_driver = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->driver->identity_document_type_id);
    //$document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);
    $configuration = \App\Models\Tenant\Configuration::first();
    $allowed_items = 90;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items * 5);
    $total_weight = 0;

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
<br>
    <div class="">
		<div width="75%" class="text-center float-left header-logo">
            @if($configuration->header_image)
				<div class="company_logo_box">
                    <img style="width: 90%" height="120px" src="data:{{mime_content_type(public_path("storage/uploads/header_images/{$configuration->header_image}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/header_images/{$configuration->header_image}")))}}" alt="{{$configuration->id}}" >
                </div>
            @endif
        </div>
		<div width="25%" class="text-center float-left header-number2 py-3 font-xlg">
            R.U.C. {{$company->number }}
            <br>
            <div style="width:100%; background: #449a35!important; padding-top: 5px; color:white; padding-bottom: 5px;">
             {{ $document->document_type->description }}</div>
            <strong>{{ $document_number }}</strong>
        </div>
    </div>
	
<div style="clear: both;" class="m-2"></div>
	
    <div class="information2" style="color:404043">
        <div class="div-table" style="padding: 8px 15px ">
            <div class="div-table-row">
                <div class="div-table-col w-19 font-xs desc">Fecha de emisión:</div>
                <div class="div-table-col w-70 font-xs desc text-uppercase">: {{ $document->date_of_issue->format('d/m/Y') }}</div>
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-19 font-xs desc">Dirección del punto de partida</div>
                <div class="div-table-col w-80 font-xs desc text-uppercase">: {{ $document->origin->address }} - {{ $document->origin->location_id }}</div>
            </div>
		<div style="clear: both;" class="m-2"></div>			
            <div class="div-table-row">
                <div class="div-table-col w-49 font-xs desc"><strong>DATOS DEL DESTINATARIO:</strong></div>
                <div class="div-table-col w-49 font-xs desc"><strong>DATOS DEL TRANSPORTISTA:</strong></div>				
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-49 font-xs desc">Apellido y Nombre o Razón Social: {{ $customer->name }}</div>
				<div class="div-table-col w-49 font-xs desc">Apellido y Nombre o Razón Social: 
				@if($document->transport_mode_type_id === '01')
				@php
					$document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);
				@endphp
				{{ $document->dispatcher->name }}
				@endif</div>
            </div>
            <div class="div-table-row">
                <div class="div-table-col w-49 font-xs desc">Domicilio del punto de llegada: {{ $document->delivery->address }} - {{ $document->delivery->location_id }}</div>
				<div class="div-table-col w-49 font-xs desc">Domicilio fiscal: 
                @if($document->transport_mode_type_id === '01')
				@php
					$document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);
				@endphp
				{{ $document->dispatcher->name}}
				@endif</div>
			</div>
             <div class="div-table-row">
                <div class="div-table-col w-49 font-xs desc">RUC: {{ $customer->number }}</div>
				<div class="div-table-col w-49 font-xs desc">RUC: 
                @if($document->transport_mode_type_id === '01')
				@php
					$document_type_dispatcher = App\Models\Tenant\Catalogs\IdentityDocumentType::findOrFail($document->dispatcher->identity_document_type_id);
				@endphp
				{{ $document->dispatcher->number }}
				@endif</div>
            </div>			
        </div>
    </div>		

<div style="clear: both;" class="m-2"></div>

<table class="full-width mt-0 mb-0 border-top-bottom" style="color:404043" >
    <thead >
        <tr>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="8%" style="background: #e3efe0">CANTIDAD</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="18%" style="background: #e3efe0">CÓDIGO</th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  width="62%" style="background: #e3efe0">DESCRIPCIÓN</th>
        </tr>
    </thead>
    <tbody class="">
        @foreach($document->items as $row)
            @php
                $total_weight_line = 0;
            @endphp
            <tr>
                <td class="p-1 text-center align-top desc cell-solid-rl"> 
					@if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
				</td>
                <td class="p-1 text-center align-top desc cell-solid-rl">{{ $row->item->internal_id }}</td>
                <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
                    {!!$row->item->description!!}
                    @if($row->relation_item->attributes)
                        @foreach($row->relation_item->attributes as $attr)
                            @if($attr->attribute_type_id === '5032')
                            @php
                                $total_weight += $attr->value * $row->quantity;
                                $total_weight_line += $attr->value * $row->quantity;

                            @endphp
                            @endif
                            <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                        @endforeach
                    @endif
                </td>
            </tr>

        @endforeach

        @for($i = 0; $i < $cycle_items; $i++)
        <tr>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
        </tr>
        @endfor
        <tr>
            <td class="cell-solid-offtop"></td>
            <td class="cell-solid-offtop"></td>
            <td class="cell-solid-offtop"></td>
        </tr>
    </tbody>
</table>

<div style="clear: both;" class="m-2"></div>

    <div class="titulo_2 ml-1" style="color:404043">
            <div class="div-table-row">
                <div class="div-table-col w-100 font-xs desc"><strong>UNIDAD DE TRANSPORTE Y CONDUCTOR</strong> </div>
				<div style="clear: both;" class="m-2"></div>
				<div class="div-table-row">
					<div class="div-table-col w-25 font-xs desc">Número de placa:</div>
			    @if($document->is_transport_category_m1l)
					@if($document->plate_number)
					<div class="div-table-col w-30 font-xs desc text-uppercase">{{ $document->plate_number }}</div>@endif
					@else
				@if($transport_principal_plate_number)
					<div class="div-table-col w-30 font-xs desc text-uppercase">{{ $transport_principal_plate_number }}</div>@endif
				@endif	
				</div>
				<div class="div-table-row">
					<div class="div-table-col w-35 font-xs desc">Constancia de Inscripción:</div>
			    @if($transport_principal_tuce_number)
					<div class="div-table-col w-20 font-xs desc text-uppercase">{{ $transport_principal_tuce_number }}</div>
				@endif	
				</div>			
				<div class="div-table-row">
					<div class="div-table-col w-30 font-xs desc">Licencia de Conducir:</div>
				@if($document->driver)
					@if($document->driver->license)	
				<div class="div-table-col w-25 font-xs desc text-uppercase">{{ $document->driver->license }}</div>@endif
				@endif
				</div>								
            </div>
    </div>
    <div class="titulo_2  ml-1" style="color:404043">
            <div class="div-table-row">
                <div class="div-table-col w-100 font-xs desc"><strong> MOTIVO DEL TRASLADO</strong> </div>
				<div style="clear: both;" class="m-2"></div>
				<div class="div-table-row">
					<div class="div-table-col w-23 font-xs desc">Fecha Emisión:</div>
					<div class="div-table-col w-45 font-xs desc text-uppercase">{{ $document->date_of_issue->format('Y-m-d') }}</div>
				</div>
				<div class="div-table-row">
					<div class="div-table-col w-23 font-xs desc">Motivo Traslado:</div>
					<div class="div-table-col w-40 font-xs desc text-uppercase">{{ $document->transfer_reason_type->description }}</div>
				</div>
				<div class="div-table-row">
					<div class="div-table-col w-35 font-xs desc">Modalidad de Transporte:</div>
					<div class="div-table-col w-31 font-xs desc text-uppercase">{{ $document->transport_mode_type->description }}</div>			
				</div>
			</div>	
    </div>
<div style="clear: both;" class="m-2"></div>	
    <div class="titulo_3  ml-1" style="color:404043">
            <div class="div-table-row"><br>
				<div class="div-table-row">
					<div class="div-table-col w-100 font-xs desc">La mercadería viaja por cuenta y riesgo del comprador</div>
				</div>
				<div style="clear: both;" class="m-2"></div>	
				<div class="div-table-row">
					<div class="div-table-col w-100 font-xs desc">Entregada la mercaderia no hay lugar a reclamo.</div><br>
				</div>
		
            </div>
    </div>
    <div class="titulo_4  ml-1" style="color:404043">
            @if($document->qr)
			<div class="div-table-row"><br>
				<div class="div-table-row">
					<img src="data:image/png;base64, {{ $document->qr }}" class="p-0 m-0" style="width: 60px;" />
				</div>
            </div>
			@endif
	</div>	
    <div class="titulo_5  ml-1" style="color:404043">
            <div class="div-table-row"><br><br>
				<div class="div-table-row">
					<div class="div-table-col w-100 font-xs desc">_____________________________________________</div>
				</div>
				<div style="clear: both;" class="m-2"></div>	
				<div class="div-table-row">
					<div class="div-table-col w-100 font-xs desc">RECIBI CONFORME.</div>
				</div>
		
            </div>
    </div>	

</body>
</html>
