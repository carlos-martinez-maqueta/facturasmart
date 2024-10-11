@php
    use App\CoreFacturalo\Helpers\Template\TemplateHelper;$establishment = $document->establishment;
    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = ($document->note) ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series.'-'.str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::all();
    $configuration = \App\Models\Tenant\Configuration::first();

    if($document_base) {

        $affected_document_number = ($document_base->affected_document) ? $document_base->affected_document->series.'-'.str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT) : $document_base->data_affected_document->series.'-'.str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);

    } else {

        $affected_document_number = null;
    }

    $payments = $document->payments;

    $document->load('reference_guides');
    $total_payment = $document->payments->sum('payment');
    $balance = ($document->total - $total_payment) - $document->payments->sum('change');

    //calculate items
    $allowed_items = 100;
    $quantity_items = $document->items()->count();
    $cycle_items = $allowed_items - ($quantity_items * 3);
    $total_weight = 0;

    $logo = "storage/uploads/logos/{$company->logo}";
    if($establishment->logo) {
        $logo = "{$establishment->logo}";
    }

@endphp
<html>
<head>
    {{--<title>{{ $document_number }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
@if($document->state_type->id == '11')
    <div class="company_logo_box" style="position: absolute; text-align: center; top:30%;">
        <img src="data:{{mime_content_type(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png"))}};base64, {{base64_encode(file_get_contents(public_path("status_images".DIRECTORY_SEPARATOR."anulado.png")))}}" alt="anulado" class="" style="opacity: 0.6;">
    </div>
@endif
<br>
    <div class="">
        @if(in_array($document->document_type_id, ['01', '03']))
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
		@endif
    </div>	
<table class="full-width mt-3">
    <tr>
        <td width="50%" class="pl-3">
            <table class="full-width" style="color:404043">
                <tr>
                    <td class="font-sm" width="100px">SEÑOR(ES):</td>
                    <td class="font-sm" style="text-transform: uppercase">{{ $customer->name }}</td>
                </tr>
                <tr>
                    <td class="font-sm" width="100px">{{$customer->identity_document_type->description}}</td>
                    <td class="font-sm">{{$customer->number}}</td>
                </tr>
                <tr>
                    <td class="font-sm" width="100px">DIRECCIÓN</td>
                    <td class="font-sm">
                        @if ($customer->address !== '')
                            <span style="text-transform: uppercase;">
                                {{ $customer->address }}
                                {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
                                {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
                                {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
                            </span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="font-sm" width="100px">MOD. PAGO:</td>
					@php
						$paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
					@endphp
                    <td class="font-sm" style="text-transform: uppercase">{{ $paymentCondition }}</td>
					<td class="font-sm" width="49px">FEC.VENC.:</td>
					@foreach($document->fee as $key => $quote)
                     <td class="font-sm">    {{ $quote->date->format('Y-m-d') }}</td>
                    @endforeach
                </tr>
            </table>
        </td>
        <td width="15%"></td>
        <td width="35%" class="pl-3">
            <table class="full-width" style="color:404043">
				<tr>
                    <td class="font-sm" width="130px">F. EMISIÓN:</td>
                    <td class="font-sm" style="text-transform: uppercase">{{ $document->date_of_issue->format('Y-m-d') }}</td>
                </tr>
				<tr>
					@if ($document->reference_guides)
					<td class="font-sm" width="130px">GUÍA REMISIÓN:</td>
						@foreach($document->reference_guides as $guide)
						<td class="font-sm">{{ $guide->series }}-{{ $guide->number }}</td>
						@endforeach
					@endif
					
					@if ($document->guides)
					<td class="font-sm" width="130px">GUÍA REMISIÓN:</td>
					@foreach($document->guides as $guide)
						<td class="font-sm">@if(isset($guide->document_type_description)) {{ $guide->document_type_description }} @else {{ $guide->document_type_id }} @endif:{{ $guide->number }}</td>
						@endforeach	
					@endif	
                </tr>				
				<tr>
                    <td class="font-sm" width="130px">ORDEN DE COMPRA:</td>
                    <td class="font-sm" style="text-transform: uppercase">{{ $document->purchase_order }}</td>
                </tr>
				<tr>
                    <td class="font-sm" width="130px">TIPO DE MONEDA:</td>
                    <td class="font-sm" style="text-transform: uppercase">{{ $document->currency_type->description }}</td>
                </tr>				
            </table>
        </td>
    </tr>
</table>
<table class="full-width mt-3">
    <tr>
        <td width="50%" class="pl-3">
            <table class="full-width" style="color:404043">
                @if ($document->detraction)
				<tr>
                    <td class="font-sm" width="100px">N. CTA DETRACCIONES:</td>
                    <td class="font-sm" style="text-transform: uppercase">{{ $document->detraction->bank_account}}</td>
                </tr>
                @endif
				@if ($document->detraction)
				<tr>
                    <td class="font-sm" width="130px">B/S SUJETO A DETRACCIÓN:</td>
					@inject('detractionType', 'App\Services\DetractionTypeService')
                    <td class="font-sm" style="text-transform: uppercase">{{$document->detraction->detraction_type_id}}
					- {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id ) }}</td>
                </tr>
                @endif
				@if ($document->detraction)
				<tr>
                    <td class="font-sm" width="100px">MÉTODO DE PAGO</td>
                    <td class="font-sm">{{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id ) }}</td>
                </tr>	
				@endif
            </table>
        </td>
        <td width="15%"></td>
        <td width="35%" class="pl-3">
            <table class="full-width" style="color:404043">
				@if ($document->detraction)
				<tr>
                    <td class="font-sm" width="100px">MONTO DETRACCIÓN</td>
                    <td class="font-sm">S/ {{ property_exists($document->detraction, 'amount_pen')?$document->detraction->amount_pen:$document->detraction->amount}}</td>
                </tr>
				@endif
				@if ($document->detraction)
				<tr>
					<td class="font-sm" width="130px">P. DETRACCIÓN:</td>
						<td class="font-sm">{{ $document->detraction->percentage}}%</td>
                </tr>
				@endif				
				@if ($document->detraction)
					@if($document->detraction->pay_constancy)
				<tr>
                    <td class="font-sm" width="130px">CONSTANCIA DE PAGO:</td>
                    <td class="font-sm" style="text-transform: uppercase">{{ $document->detraction->pay_constancy}}</td>
                </tr>
					@endif
				@endif
            </table>
        </td>
    </tr>
</table>

<table class="full-width my-2 text-center" border="0">
    <tr>
        <td class="desc"></td>
    </tr>
</table>
<table class="full-width mt-0 mb-0" style="color:404043">
    <thead >
        <tr class="">
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid" style="background: #e3efe0" width="10%"><h6>CÓDIGO</h6></th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  style="background: #e3efe0" width="10%"><h6>CANTIDAD</h6></th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid" style="background: #e3efe0" width="50%"><h6>DESCRIPCIÓN</h6></th>
            <th class="border-top-bottom text-right py-1 desc" class="cell-solid"  style="background: #e3efe0" width="15%"><h6>PRECIO DE VENTA UNITARIO</h6></th>
            <th class="border-top-bottom text-center py-1 desc" class="cell-solid"  style="background: #e3efe0" width="15%"><h6>PRECIO DE VENTA UNITARIO</h6></th>
        </tr>
    </thead>
    <tbody class="">
        @foreach($document->items as $row)
            <tr>
                <td class="p-1 text-center align-top desc cell-solid-rl">{{ $row->item->internal_id }}</td>
                <td class="p-1 text-center align-top desc cell-solid-rl">
                    @if(((int)$row->quantity != $row->quantity))
                        {{ $row->quantity }}
                    @else
                        {{ number_format($row->quantity, 0) }}
                    @endif
                </td>
                <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
                    @if($row->name_product_pdf)
                        {!!$row->name_product_pdf!!}
                    @else
                        {!!$row->item->description!!}
                    @endif

                    @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif

                    @if($row->attributes)
                        @foreach($row->attributes as $attr)
                            @if($attr->attribute_type_id === '5032')
                            @php
                                $total_weight += $attr->value * $row->quantity;
                            @endphp
                            @endif
                            <br/><span style="font-size: 9px">{!! $attr->description !!} : {{ $attr->value }}</span>
                        @endforeach
                    @endif
                    {{-- @if($row->discounts)
                        @foreach($row->discounts as $dtos)
                            <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                        @endforeach
                    @endif --}}

                    @if($row->item->is_set == 1)
                     <br>
                     @inject('itemSet', 'App\Services\ItemSetService')
                        {{join( "-", $itemSet->getItemsSet($row->item_id) )}}
                    @endif
                </td>
                <td class="p-1 text-right align-top desc cell-solid-rl">{{ number_format($row->unit_price, 2) }}</td>
                <td class="p-1 text-right align-top desc cell-solid-rl">{{ number_format($row->total, 2) }}</td>
            </tr>

        @endforeach

        @for($i = 0; $i < $cycle_items; $i++)
        <tr>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-center align-top desc cell-solid-rl">
            </td>
            <td class="p-1 text-center align-top desc cell-solid-rl"></td>
            <td class="p-1 text-left align-top desc text-upp cell-solid-rl">
            </td>
            <td class="p-1 text-right align-top desc cell-solid-rl"></td>
        </tr>
        @endfor

        <tr>
            <td class="p-1 cell-solid-offtop" colspan="1"></td>
			<td class="p-1 cell-solid-offtop" colspan="1"></td>
			<td class="p-1 cell-solid-offtop" colspan="1"></td>
            <td class="p-1 text-left align-top desc cell-solid" colspan="1">
                OP. GRAVADA - VALOR DE VENTA 
            </td>
            <td class="p-1 text-right align-top desc cell-solid">{{$document->currency_type->symbol}} {{ number_format($document->total_taxed, 2) }}</td>
        </tr>
        <tr>
            <td class="p-1 cell-solid-offtop" colspan="1"></td>
			<td class="p-1 cell-solid-offtop" colspan="1"></td>
			<td class="p-1 cell-solid-offtop" colspan="1">
			@if($document->detraction)
			
			@endif
			</td>
            <td class="p-1 text-left align-top desc cell-solid" colspan="1">
                IGV 18%
            </td>
            <td class="p-1 text-right align-top desc cell-solid">{{ number_format($document->total_igv, 2) }}</td>
        </tr>		

        <tr>
            <td class="p-1 text-left align-top desc cell-solid" colspan="3" rowspan="{{($document->retention || $document->detraction) ? '7' : '6'}}">
            @foreach(array_reverse( (array) $document->legends) as $row)
                @if ($row->code == "1000")
                    Son: <span style="text-transform: uppercase;">{{ $row->value }} {{ $document->currency_type->description }}</span>
                    @if (count((array) $document->legends)>1)
                        <br>Leyendas</span>
                    @endif
                @else
                    <br>{{$row->code}}: {{ $row->value }}
                @endif

            @endforeach
            </td>
            <td class="p-1 text-left align-top desc cell-solid" colspan="1">
                IMPORTE TOTAL
            </td>
            <td class="p-1 text-right align-top desc cell-solid">{{ number_format($document->total, 2) }}</td>
        </tr>


    </tbody>

</table>
	
@if ($document->terms_condition)
        <br>
        <table class="full-width">
            <tr>
                <td>
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
@endif
<table class="full-width">
    <td width="75%" class="pl-3">
		<tr>
		<td class="text-left desc">RESUMEN: {{ $document->hash }}
		<br>REPRESENTACIÓN IMPRESA DE FACTURA ELECTRÓNICA
		<br>PUEDE SER CONSULTADA EN {!! url('/buscar') !!}</td>
		</tr>
	</td>
    @if ($document->qr)
	<td width="15%" class="pl-3">
		<img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px; max-width: 80px;" />
	</td>
	@endif	
</table>

</body>
</html>