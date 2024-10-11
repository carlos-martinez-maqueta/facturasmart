@php
    $establishment = $document->establishment;
    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;
    $documment_columns = \App\Models\Tenant\DocumentColumn::where('is_visible', true)->get();

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }
    $configurations = \App\Models\Tenant\Configuration::first();

    $customer = $document->customer;
    $invoice = $document->invoice;
    $document_base = $document->note ? $document->note : null;

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $document_number = $document->series . '-' . str_pad($document->number, 8, '0', STR_PAD_LEFT);
    $accounts = \App\Models\Tenant\BankAccount::where('show_in_documents', true)->get();

    if ($document_base) {
        $affected_document_number = $document_base->affected_document
            ? $document_base->affected_document->series .
                '-' .
                str_pad($document_base->affected_document->number, 8, '0', STR_PAD_LEFT)
            : $document_base->data_affected_document->series .
                '-' .
                str_pad($document_base->data_affected_document->number, 8, '0', STR_PAD_LEFT);
    } else {
        $affected_document_number = null;
    }

    $payments = $document->payments;

    $document->load('reference_guides');

    $total_payment = $document->payments->sum('payment');
    $balance = $document->total - $total_payment - $document->payments->sum('change');
    $bg = "storage/uploads/header_images/{$configurations->background_image}";
    $total_discount_items = 0;

    $establishment__ = \App\Models\Tenant\Establishment::find($document->establishment_id);
    $logo = $establishment__->logo ?? $company->logo;

    if ($logo === null && !file_exists(public_path("$logo}"))) {
        $logo = "{$company->logo}";
    }

    if ($logo) {
        $logo = "storage/uploads/logos/{$logo}";
        $logo = str_replace('storage/uploads/logos/storage/uploads/logos/', 'storage/uploads/logos/', $logo);
    }

    $configuration_decimal_quantity = App\CoreFacturalo\Helpers\Template\TemplateHelper::getConfigurationDecimalQuantity();

@endphp
<html>

<head>
    {{-- <title>{{ $document_number }}</title> --}}
    {{-- <link href="{{ $path_style }}" rel="stylesheet" /> --}}
</head>

<body>

    @if ($configurations->background_image)
        <div class="centered">
            <img src="data:{{ mime_content_type(public_path("{$bg}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$bg}"))) }}"
                alt="anulado" class="order-1">
        </div>
    @endif


    <table class="full-width">
        <tr>
            @if ($company->logo)
                <td class="text-left desc" width="20%">
                    <div class="company_logo_box">
                        <img src="data:{{ mime_content_type(public_path("{$logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$logo}"))) }}"
                            alt="{{ $company->name }}" class="company_logo" style="max-width: 150px;">
                    </div>
                </td>
            @else
                <td class="text-left desc" width="20%">
                    {{-- <img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px"> --}}
                </td>
            @endif
            <td class="text-left desc" width="50%" class="pl-3">
                <div class="text-left">
                    <h4 class="">{{ $company->name }}</h4>
                    <h6 style="text-transform: uppercase;">
                        {{ $establishment->address !== '-' ? $establishment->address : '' }}
                        {{ $establishment->district_id !== '-' ? ', ' . $establishment->district->description : '' }}
                        {{ $establishment->province_id !== '-' ? ', ' . $establishment->province->description : '' }}
                        {{ $establishment->department_id !== '-' ? '- ' . $establishment->department->description : '' }}
                    </h6>

                    @isset($establishment->trade_address)
                        <h6>{{ $establishment->trade_address !== '-' ? 'D. Comercial: ' . $establishment->trade_address : '' }}
                        </h6>
                    @endisset

                    <h6>{{ $establishment->telephone !== '-' ? 'Central telefónica: ' . $establishment->telephone : '' }}
                    </h6>

                    <h6>{{ $establishment->email !== '-' ? 'Email: ' . $establishment->email : '' }}</h6>

                    @isset($establishment->web_address)
                        <h6>{{ $establishment->web_address !== '-' ? 'Web: ' . $establishment->web_address : '' }}</h6>
                    @endisset

                    @isset($establishment->aditional_information)
                        <h6>{{ $establishment->aditional_information !== '-' ? $establishment->aditional_information : '' }}
                        </h6>
                    @endisset
                </div>
            </td>
            <td class="text-left desc" width="30%" class="border-box py-4 px-2 text-center">
                <h5>{{ 'RUC ' . $company->number }}</h5>
                <h5 class="text-center">{{ $document->document_type->description }}</h5>
                <h5 class="text-center">{{ $document_number }}</h5>
            </td>
        </tr>
    </table>
    <table class="full-width mt-5">
        <tr>
            <td class="text-left desc" width="120px">Fecha de emisión</td>
            <td class="text-left desc" width="8px">:</td>
            <td class="text-left desc">{{ $document->date_of_issue->format('Y-m-d') }}</td>

            @if ($document->detraction)
                <td class="text-left desc" width="120px">N. Cta detracciones</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->detraction->bank_account }}</td>
            @endif
        </tr>
        @if ($invoice)
            <tr>
                <td class="text-left desc">Fecha de vencimiento</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $invoice->date_of_due->format('Y-m-d') }}</td>
            </tr>
        @endif

        @if ($document->detraction)
            <td class="text-left desc" width="140px">B/S Sujeto a detracción</td>
            <td class="text-left desc" width="8px">:</td>
            @inject('detractionType', 'App\Services\DetractionTypeService')
            <td class="text-left desc" width="220px">{{ $document->detraction->detraction_type_id }}
                - {{ $detractionType->getDetractionTypeDescription($document->detraction->detraction_type_id) }}</td>
        @endif
        <tr>
            <td class="text-left desc" style="vertical-align: top;">Cliente</td>
            <td class="text-left desc" style="vertical-align: top;">:</td>
            <td class="text-left desc" style="vertical-align: top;">
                {{ $customer->name }}
                @if ($configurations->show_internal_code_person)
                    @if (isset($customer->internal_code) && $customer->internal_code !== '')
                        <br>
                        <small>{{ $customer->internal_code ?? '' }}</small>
                    @elseif (isset($customer->internal_id) && $customer->internal_id !== '')
                        <br>
                        <small>{{ $customer->internal_id ?? '' }}</small>
                    @endif
                @endif

            </td>

            @if ($document->detraction)
                <td class="text-left desc" width="120px">Método de pago</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc" width="220px">
                    {{ $detractionType->getPaymentMethodTypeDescription($document->detraction->payment_method_id) }}
                </td>
            @endif

        </tr>
        <tr>
            <td class="text-left desc">{{ $customer->identity_document_type->description }}</td>
            <td class="text-left desc">:</td>
            <td class="text-left desc">{{ $customer->number }}</td>

            @if ($document->detraction)
                <td class="text-left desc" width="120px">P. Detracción</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->detraction->percentage }}%</td>
            @endif
        </tr>
        @isset($customer->search_telephone)
            @if ($customer->search_telephone != null)
                <tr>
                    <td class="text-left desc">Teléfono</td>
                    <td class="text-left desc">:</td>
                    <td class="text-left desc">{{ $customer->search_telephone }}</td>
                </tr>
            @endif
        @endisset


        @if ($customer->address !== '')
            <tr>
                <td class="text-left desc" class="align-top desc">Dirección</td>
                <td class="text-left desc" style="vertical-align: top;">:</td>
                <td class="text-left desc" style="desc text-transform: uppercase;">
                    {{ $customer->address }}
                    {{ $customer->district_id !== '-' ? '' . $customer->district->description : '' }}
                    {{ $customer->province_id !== '-' ? ', ' . $customer->province->description : '' }}
                    {{ $customer->department_id !== '-' ? ', ' . $customer->department->description : '' }}
                </td>

                @if ($document->detraction)
                    <td class="text-left desc" width="120px">Monto detracción</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">
                        @php
                        $amount_detraction = $document->detraction->amount;
                        // if($document->currency_type_id != "PEN"){
                        //     $exchange_rate_sale = $document->exchange_rate_sale;
                        //     $amount_detraction = $amount_detraction * $exchange_rate_sale;
                        //     $amount_detraction = number_format($amount_detraction, 2, ".", "");
                        // }
                        @endphp
                        
                    S/ {{ $amount_detraction }}</td>
                @endif
            </tr>
        @endif
        @if ($document->quotations_optional !== '' && $document->quotations_optional_value !== '')
            <tr>
                <td class="text-left desc" class="align-top">{{ $document->quotations_optional }}:</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc" style="text-transform: uppercase;">
                    {{ $document->quotations_optional_value }}
                </td>
            </tr>
        @endif

        @if ($document->hotelRent)
            <tr>
                <td class="text-left desc" class="align-top">DESTINO:</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc" style="text-transform: uppercase;">
                    {{ $document->hotelRent->destiny }}
                </td>
            </tr>
        @endif

        @if ($document->reference_data)
            <tr>
                <td class="text-left desc" width="120px">D. REFERENCIA</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->reference_data }}</td>
            </tr>
        @endif

        @if ($document->detraction)
            @if ($document->detraction->pay_constancy)
                <tr>
                    <td class="text-left desc" colspan="3">
                    </td>
                    <td class="text-left desc" width="120px">Constancia de pago</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $document->detraction->pay_constancy }}</td>
                </tr>
            @endif
        @endif

        @if ($document->detraction && $invoice->operation_type_id == '1004')
            <tr>
                <td class="text-left desc" colspan="4"><strong>Detalle - Servicios de transporte de carga</strong></td>
            </tr>
            <tr>
                <td class="text-left desc" class="align-top">Ubigeo origen</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $document->detraction->origin_location_id[2] }}</td>

                <td class="text-left desc" width="120px">Dirección origen</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->detraction->origin_address }}</td>
            </tr>
            <tr>
                <td class="text-left desc" class="align-top">Ubigeo destino</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $document->detraction->delivery_location_id[2] }}</td>

                <td class="text-left desc" width="120px">Dirección destino</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->detraction->delivery_address }}</td>
            </tr>
            <tr>
                <td class="text-left desc" class="align-top" width="170px">Valor referencial servicio de transporte</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $document->detraction->reference_value_service }}</td>

                <td class="text-left desc" width="170px">Valor referencia carga efectiva</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->detraction->reference_value_effective_load }}</td>
            </tr>
            <tr>
                <td class="text-left desc" class="align-top">Valor referencial carga útil</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $document->detraction->reference_value_payload }}</td>

                <td class="text-left desc" width="120px">Detalle del viaje</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->detraction->trip_detail }}</td>
            </tr>
        @endif

    </table>


    {{-- @if ($document->retention) --}}
    {{-- <table class="full-width mt-3"> --}}
    {{-- <tr> --}}
    {{-- <td class="text-left desc" colspan="3"> --}}
    {{-- <strong>Información de la retención</strong> --}}
    {{-- </td> --}}
    {{-- </tr> --}}
    {{-- <tr> --}}
    {{-- <td class="text-left desc" width="120px">Base imponible</td> --}}
    {{-- <td class="text-left desc" width="8px">:</td> --}}
    {{-- <td class="text-left desc">{{ $document->currency_type->symbol}} {{ $document->retention->base }}</td> --}}

    {{-- <td class="text-left desc" width="80px">Porcentaje</td> --}}
    {{-- <td class="text-left desc" width="8px">:</td> --}}
    {{-- <td class="text-left desc">{{ $document->retention->percentage * 100 }}%</td> --}}
    {{-- </tr> --}}
    {{-- <tr> --}}
    {{-- <td class="text-left desc" width="120px">Monto</td> --}}
    {{-- <td class="text-left desc" width="8px">:</td> --}}
    {{-- <td class="text-left desc">{{ $document->currency_type->symbol}} {{ $document->retention->amount }}</td> --}}
    {{-- </tr> --}}
    {{-- </table> --}}
    {{-- @endif --}}


    @if ($document->isPointSystem())
        <table class="full-width mt-3">
            <tr>
                <td class="text-left desc" width="120px">P. ACUMULADOS</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->person->accumulated_points }}</td>

                <td class="text-left desc" width="140px">PUNTOS POR LA COMPRA</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->getPointsBySale() }}</td>
            </tr>
        </table>
    @endif


    @if ($document->guides)
        <br />
        <table>
            @foreach ($document->guides as $guide)
                <tr>
                    @if (isset($guide->document_type_description))
                        <td class="text-left desc">{{ $guide->document_type_description }}</td>
                    @else
                        <td class="text-left desc">{{ $guide->document_type_id }}</td>
                    @endif
                    <td class="text-left desc">:</td>
                    <td class="text-left desc">{{ $guide->number }}</td>
                </tr>
            @endforeach
        </table>
    @endif


    @if ($document->transport)
        <br>
        <strong>Transporte de pasajeros</strong>
        @php
            $transport = $document->transport;
            $agency_origin = '-';
            $agency_destination = '-';
            if ($transport->agency_origin_id) {
                $agency_origin = $transport->agency_origin->description;
            }
            if ($transport->agency_destination_id) {
                $agency_destination = $transport->agency_destination->description;
            }
            $origin_district_id = (array) $transport->origin_district_id;
            $destinatation_district_id = (array) $transport->destinatation_district_id;
            $origin_district = Modules\Order\Services\AddressFullService::getDescription(
                isset($origin_district_id[2]) ? $origin_district_id[2] : null,
            );
            $destinatation_district = Modules\Order\Services\AddressFullService::getDescription(
                isset($destinatation_district_id[2]) ? $destinatation_district_id[2] : null,
            );
        @endphp

        <table class="full-width mt-3">
            <tr>
                <td class="text-left desc" width="120px">{{ $transport->identity_document_type->description }}</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->number_identity_document }}</td>
                <td class="text-left desc" width="120px">NOMBRE</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->passenger_fullname }}</td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">N° ASIENTO</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->seat_number }}</td>
                <td class="text-left desc" width="120px">M. PASAJERO</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->passenger_manifest }}</td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">F. INICIO</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->start_date }}</td>
                <td class="text-left desc" width="120px">H. INICIO</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->start_time }}</td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">AGENCIA ORIGEN</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $agency_origin }}</td>
                <td class="text-left desc" colspan="3"></td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">U. ORIGEN</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $origin_district }}</td>
                <td class="text-left desc" width="120px">D. ORIGEN</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->origin_address }}</td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">AGENCIA DESTINO</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $agency_destination }}</td>
                <td class="text-left desc" colspan="3"></td>
            </tr>
            <tr>
                <td class="text-left desc" width="120px">U. DESTINO</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $destinatation_district }}</td>
                <td class="text-left desc" width="120px">D. DESTINO</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $transport->destinatation_address }}</td>
            </tr>
        </table>
    @endif
    @if ($document->transport_dispatch)
        <br>
        <strong>Información de encomienda</strong>
        @php
            $transport_dispatch = $document->transport_dispatch;
            $sender_identity_document_type = $transport_dispatch->sender_identity_document_type->description;
            $recipient_identity_document_type = $transport_dispatch->recipient_identity_document_type->description;
            $agency_origin_dispatch = '-';
            $agency_destination_dispatch = '-';
            if ($transport_dispatch->agency_origin_id) {
                $agency_origin_dispatch = $transport_dispatch->agency_origin->description;
            }
            if ($transport_dispatch->agency_destination_id) {
                $agency_destination_dispatch = $transport_dispatch->agency_destination->description;
            }
            $origin_district_dispatch = null;
            $destination_district_dispatch = null;
            if ($transport_dispatch->origin_district_id && $transport_dispatch->destinatation_district_id) {
                $origin_district_id = (array) $transport_dispatch->origin_district_id;
                $destinatation_district_id = (array) $transport_dispatch->destinatation_district_id;
                $origin_district_dispatch = Modules\Order\Services\AddressFullService::getDescription(
                    $origin_district_id[2],
                );
                $destination_district_dispatch = Modules\Order\Services\AddressFullService::getDescription(
                    $destinatation_district_id[2],
                );
            }
        @endphp

        <table class="full-width mt-3">
            <thead>
                <tr>
                    <th colspan="6" class="text-left">
                        <strong>REMITENTE</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left desc" width="120px">{{ $sender_identity_document_type }}</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->sender_number_identity_document }}</td>
                    <td class="text-left desc" width="120px">NOMBRE</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->sender_passenger_fullname }}</td>
                </tr>
                <tr>

                </tr>
                @if ($transport_dispatch->sender_telephone)
                    <tr>
                        <td class="text-left desc" width="120px">TELÉFONO</td>
                        <td class="text-left desc" width="8px">:</td>
                        <td class="text-left desc">{{ $transport_dispatch->sender_telephone }}</td>
                        <td class="text-left desc" colspan="3"></td>
                    </tr>
                @endif
                <tr>
                    <td class="text-left desc" width="120px">U. ORIGEN</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $origin_district_dispatch }}</td>
                    <td class="text-left desc" width="120px">D. ORIGEN</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->origin_address }}</td>

                </tr>
                @if ($agency_origin_dispatch != '-')
                    <tr>
                        <td class="text-left desc" width="120px">AGENCIA ORIGEN</td>
                        <td class="text-left desc" width="8px">:</td>
                        <td class="text-left desc">{{ $agency_origin_dispatch }}</td>
                        <td class="text-left desc" colspan="3"></td>
                    </tr>
                @endif
            </tbody>
            <thead>
                <tr>
                    <th colspan="6" class="text-left">
                        <strong>DESTINATARIO</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left desc" width="120px">{{ $recipient_identity_document_type }}</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->recipient_number_identity_document }}</td>
                    <td class="text-left desc" width="120px">NOMBRE</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->recipient_passenger_fullname }}</td>
                </tr>
                @if ($transport_dispatch->recipient_telephone)
                    <tr>
                        <td class="text-left desc" width="120px">TELÉFONO</td>
                        <td class="text-left desc" width="8px">:</td>
                        <td class="text-left desc">{{ $transport_dispatch->recipient_telephone }}</td>
                        <td class="text-left desc" colspan="3"></td>
                    </tr>
                @endif
                <tr>
                    <td class="text-left desc" width="120px">U. DESTINO</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $destination_district_dispatch }}</td>
                    <td class="text-left desc" width="120px">D. DESTINO</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $transport_dispatch->destinatation_address }}</td>

                </tr>
                @if ($agency_destination_dispatch != '-')
                    <tr>
                        <td class="text-left desc" width="120px">AGENCIA DESTINO</td>
                        <td class="text-left desc" width="8px">:</td>
                        <td class="text-left desc">{{ $agency_destination_dispatch }}</td>
                        <td class="text-left desc" colspan="3"></td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif
    @if ($document->dispatch)
        <br />
        <strong>Guías de remisión</strong>
        <table>
            <tr>
                <td class="text-left desc">{{ $document->dispatch->number_full }}</td>
            </tr>
        </table>
    @elseif ($document->reference_guides)
        @if (count($document->reference_guides) > 0)
            <br />
            <strong>Guías de remisión</strong>
            <table>
                @foreach ($document->reference_guides as $guide)
                    <tr>
                        <td class="text-left desc">{{ $guide->series }}</td>
                        <td class="text-left desc">-</td>
                        <td class="text-left desc">{{ $guide->number }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endif


    <table class="full-width mt-3">
        @if ($document->prepayments)
            @foreach ($document->prepayments as $p)
                <tr>
                    <td class="text-left desc" width="120px">Anticipo</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $p->number }}</td>
                </tr>
            @endforeach
        @endif
        @if ($document->purchase_order)
            <tr>
                <td class="text-left desc" width="120px">Orden de compra</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->purchase_order }}</td>
            </tr>
        @endif
        @if ($document->quotation_id)
            <tr>
                <td class="text-left desc" width="120px">Cotización</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->quotation->identifier }}</td>

                @isset($document->quotation->delivery_date)
                    <td class="text-left desc" width="120px">F. ENTREGA</td>
                    <td class="text-left desc" width="8px">:</td>
                    <td class="text-left desc">{{ $document->date_of_issue->addDays($document->quotation->delivery_date)->format('d-m-Y') }}</td>
                @endisset
            </tr>
        @endif
        @isset($document->quotation->sale_opportunity)
            <tr>
                <td class="text-left desc" width="120px">O. Venta</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $document->quotation->sale_opportunity->number_full }}</td>
            </tr>
        @endisset
        @if (!is_null($document_base))
            <tr>
                <td class="text-left desc" width="120px">Doc. Afectado</td>
                <td class="text-left desc" width="8px">:</td>
                <td class="text-left desc">{{ $affected_document_number }}</td>
            </tr>
            <tr>
                <td class="text-left desc">Tipo de nota</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $document_base->note_type === 'credit' ? $document_base->note_credit_type->description : $document_base->note_debit_type->description }}
                </td>
            </tr>
            <tr>
                <td class="text-left desc">Descripción</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $document_base->note_description }}</td>
            </tr>
        @endif
        @if ($document->folio)
            <tr>
                <td class="text-left desc">FOLIO</td>
                <td class="text-left desc">:</td>
                <td class="text-left desc">{{ $document->folio }}</td>
            </tr>
        @endif
    </table>

    {{-- <table class="full-width mt-3"> --}}
    {{-- <tr> --}}
    {{-- <td class="text-left desc" width="25%">Documento Afectado:</td> --}}
    {{-- <td class="text-left desc" width="20%">{{ $document_base->affected_document->series }}-{{ $document_base->affected_document->number }}</td> --}}
    {{-- <td class="text-left desc" width="15%">Tipo de nota:</td> --}}
    {{-- <td class="text-left desc" width="40%">{{ ($document_base->note_type === 'credit')?$document_base->note_credit_type->description:$document_base->note_debit_type->description}}</td> --}}
    {{-- </tr> --}}
    {{-- <tr> --}}
    {{-- <td class="text-left desc" class="align-top">Descripción:</td> --}}
    {{-- <td class="text-left desc" class="text-left" colspan="3">{{ $document_base->note_description }}</td> --}}
    {{-- </tr> --}}
    {{-- </table> --}}
    @php
        $width_column = 12;
        if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf) {
            if (
                $configuration_decimal_quantity->decimal_quantity_unit_price_pdf > 6 &&
                $configuration_decimal_quantity->decimal_quantity_unit_price_pdf <= 8
            ) {
                $width_column = 13;
            } elseif ($configuration_decimal_quantity->decimal_quantity_unit_price_pdf > 8) {
                $width_column = 15;
            } else {
                $width_column = 12;
            }
        }
    @endphp
    <table class="full-width mt-10 mb-10">
        <thead class="">
            <tr class="bg-grey">
                <th class="border-top-bottom desc text-center py-2" width="8%">Cant.</th>
                <th class="border-top-bottom desc text-center py-2" width="8%">Unidad</th>
                <th class="border-top-bottom desc text-left py-2">Descripción</th>
                @if (!$configurations->document_columns)
                    <th class="border-top-bottom desc text-center py-2" width="0%"></th>
                    <th class="border-top-bottom desc text-center py-2" width="0%"></th>
                    <th class="border-top-bottom desc text-center py-2" width="0%"></th>
                    <th class="border-top-bottom desc text-right desc py-2" width="{{ $width_column }}%">P.Unit</th>
                    <th class="border-top-bottom desc text-right desc py-2" width="8%">Dto.</th>
                    <th class="border-top-bottom desc text-right desc py-2" width="12%">Total </th>
                @else
                    @foreach ($documment_columns as $column)
                        <th class="border-top-bottom desc text-center py-2" width="{{ $column->width }}%">
                            {{ $column->name }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($document->items as $row)
                <tr>
                    <td class="text-left desc" class="text-center desc align-top">
                        @if ((int) $row->quantity != $row->quantity)
                            {{ $row->quantity }}
                        @else
                            {{ number_format($row->quantity, 0) }}
                        @endif
                    </td>
                    <td class="text-left desc" class="text-center desc align-top">{{ symbol_or_code($row->item->unit_type_id) }}</td>
                    <td class="text-left desc" class="text-left desc align-top">
                        @if ($row->name_product_pdf)
                            {!! $row->name_product_pdf !!}
                        @else
                            {!! $row->item->description !!}
                        @endif
                        @if ($configurations->name_pdf)
                            @php
                                $item_name = \App\Models\Tenant\Item::select('name')
                                    ->where('id', $row->item_id)
                                    ->first();
                            @endphp
                            @if ($item_name->name)
                                <div>
                                    <span style="font-size: 9px">{{ $item_name->name }}</span>
                                </div>
                            @endif
                        @endif
                        @if (
                            $configurations->presentation_pdf &&
                                isset($row->item->presentation) &&
                                isset($row->item->presentation->description))
                            <div>
                                <span style="font-size: 9px">{{ $row->item->presentation->description }}</span>
                            </div>
                        @endif
                        @if ($row->total_isc > 0)
                            <br /><span style="font-size: 9px">ISC : {{ $row->total_isc }}
                                ({{ $row->percentage_isc }}%)</span>
                        @endif

                        {{-- 
   
                        --}}

                        @if ($row->total_plastic_bag_taxes > 0)
                            <br /><span style="font-size: 9px">ICBPER : {{ $row->total_plastic_bag_taxes }}</span>
                        @endif

                        @if ($row->attributes)
                            @foreach ($row->attributes as $attr)
                                <br /><span style="font-size: 9px">{!! $attr->description !!} :
                                    {{ $attr->value }}</span>
                            @endforeach
                        @endif
                        @if ($row->discounts)
                            @foreach ($row->discounts as $dtos)
                                @if ($dtos->is_amount == false)
                                    <br /><span style="font-size: 9px">{{ $dtos->factor * 100 }}%
                                        {{ $dtos->description }}</span>
                                @endif
                            @endforeach
                        @endif
                        @isset($row->item->sizes_selected)
                            @if (count($row->item->sizes_selected) > 0)
                                @foreach ($row->item->sizes_selected as $size)
                                    <small> Característica {{ $size->size }} | {{ $size->qty }} und.</small> <br>
                                @endforeach
                            @endif
                        @endisset
                        @if ($row->charges)
                            @foreach ($row->charges as $charge)
                                <br /><span style="font-size: 9px">{{ $document->currency_type->symbol }}
                                    {{ $charge->amount }} ({{ $charge->factor * 100 }}%)
                                    {{ $charge->description }}</span>
                            @endforeach
                        @endif

                        @if ($row->item->is_set == 1 && $configurations->show_item_sets)
                            <br>
                            @inject('itemSet', 'App\Services\ItemSetService')
                            @foreach ($itemSet->getItemsSet($row->item_id) as $item)
                                {{ $item }}<br>
                            @endforeach
                        @endif

                        @if ($row->item->used_points_for_exchange ?? false)
                            <br>
                            <span style="font-size: 9px">*** Canjeado por {{ $row->item->used_points_for_exchange }}
                                puntos ***</span>
                        @endif

                        @if ($document->has_prepayment)
                            <br>
                            *** Pago Anticipado ***
                        @endif
                    </td>
                    @if (!$configurations->document_columns)
                        <td class="text-left desc" class="text-center desc"> </td>
                        <td class="text-left desc" class="text-center desc"> </td>
                        <td class="text-left desc" class="text-center desc"> </td>

                        @if ($configuration_decimal_quantity->change_decimal_quantity_unit_price_pdf)
                            <td class="text-left desc" class="text-right desc desc align-top">
                                {{ $row->generalApplyNumberFormat($row->unit_price, $configuration_decimal_quantity->decimal_quantity_unit_price_pdf) }}
                            </td>
                        @else
                            <td class="text-left desc" class="text-right desc desc align-top">{{ number_format($row->unit_price, 2) }}</td>
                        @endif

                        <td class="text-left desc" class="text-right desc desc align-top">
                            @if ($configurations->discounts_acc)
                                @if ($row->discounts_acc)
                                    @php
                                        $discounts_acc = (array) $row->discounts_acc;
                                    @endphp
                                    @foreach ($discounts_acc as $key => $disto)
                                        <span style="font-size: 9px">{{ $disto->percentage }}%
                                            @if ($key + 1 != count($discounts_acc))
                                                +
                                            @endif
                                        </span>
                                    @endforeach
                                @endif
                            @else
                                @if ($row->discounts)
                                    @php
                                        $total_discount_line = 0;
                                        foreach ($row->discounts as $disto) {
                                            $amount = $disto->amount;
                                            if ($disto->is_split) {
                                                $amount = $amount * 1.18;
                                            }
                                            $total_discount_line = $total_discount_line + $amount;
                                            $total_discount_items += $total_discount_line;
                                        }
                                    @endphp
                                    {{ number_format($total_discount_line, 2) }}
                                @else
                                    0
                                @endif
                            @endif

                        </td>
                        <td class="text-left desc" class="text-right desc desc align-top">{{ number_format($row->total, 2) }}</td>
                    @else
                        @foreach ($documment_columns as $column)
                            <td class="text-left desc" class="text-right desc desc align-top">
                                @php
                                    $value = $column->getValudDocumentItem($row, $column->value);
                                @endphp
                                {{ $value }}
                            </td>
                        @endforeach
                    @endif
                </tr>
                <tr>
                    @php
                        $colspan = 9;
                        if ($configurations->document_columns) {
                            $colspan = count($documment_columns) + 3;
                        }
                    @endphp
                    <td class="text-left desc" colspan="{{ $colspan }}" class="border-bottom desc"></td>
                </tr>
            @endforeach



            @if ($document->prepayments)
                @foreach ($document->prepayments as $p)
                    <tr>
                        <td class="text-left desc" class="text-center desc align-top">1</td>
                        <td class="text-left desc" class="text-center desc align-top">NIU</td>
                        <td class="text-left desc" class="text-left desc align-top">
                            Anticipo: {{ $p->document_type_id == '02' ? 'Factura' : 'Boleta' }} Nro.
                            {{ $p->number }}
                        </td>
                        <td class="text-left desc" class="text-center desc align-top"></td>
                        <td class="text-left desc" class="text-center desc align-top"></td>
                        <td class="text-left desc" class="text-center desc align-top"></td>
                        <td class="text-left desc" class="text-right desc desc align-top">-{{ number_format($p->total, 2) }}</td>
                        <td class="text-left desc" class="text-right desc desc align-top">0</td>
                        <td class="text-left desc" class="text-right desc desc align-top">-{{ number_format($p->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-left desc" colspan="{{ $colspan }}" class="border-bottom"></td>
                    </tr>
                @endforeach
            @endif

            @if ($document->total_exportation > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Op. Exportación:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total_exportation, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_free > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Op. Gratuitas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total_free, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_unaffected > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Op. Inafectas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total_unaffected, 2) }}</td>
                </tr>
            @endif
            @if ($document->total_exonerated > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Op. Exoneradas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total_exonerated, 2) }}</td>
                </tr>
            @endif

            @if ($document->document_type_id === '07')
                @if ($document->total_taxed >= 0)
                    <tr>
                        <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc">Op. Gravadas:
                            {{ $document->currency_type->symbol }}
                        </td>
                        <td class="text-left desc" class="text-right desc">{{ number_format($document->total_taxed, 2) }}</td>
                    </tr>
                @endif
            @elseif($document->total_taxed > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc">Op. Gravadas:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc">{{ number_format($document->total_taxed, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_plastic_bag_taxes > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Icbper:
                        {{ $document->currency_type->symbol }}
                    </td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total_plastic_bag_taxes, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc">IGV: {{ $document->currency_type->symbol }}
                </td>
                <td class="text-left desc" class="text-right desc">{{ number_format($document->total_igv, 2) }}</td>
            </tr>

            @if ($document->total_isc > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">ISC:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total_isc, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_discount > 0 && $document->subtotal > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Subtotal:
                        {{ $document->currency_type->symbol }}
                    </td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->subtotal, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_discount > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">
                        {{ $document->total_prepayment > 0 ? 'Anticipo' : 'Descuento total' }}
                        : {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">

                        @php
                            $total_discount = $document->total_discount;
                            $discounts = $document->discounts;
                            $igv_prepayment = 1;
                            if ($document->total_prepayment > 0) {
                                $item = $document->items->first();
                                $has_affected = $item->affectation_igv_type_id < 20;
                                if ($has_affected) {
                                    $igv_prepayment = 1.18;
                                }
                            }
                            if ($discounts) {
                                $discounts = get_object_vars($document->discounts);
                                $discount = $discounts[0];
                                $is_split = isset($discount->is_split) ? $discount->is_split : false;
                                if ($is_split) {
                                    $total_discount = $total_discount * 1.18;
                                }
                            } else {
                                $total_discount = $total_discount_items;
                            }

                        @endphp

                        {{ number_format($total_discount * $igv_prepayment, 2) }}</td>
                </tr>
            @endif

            @if ($document->total_charge > 0)
                @if ($document->charges)
                    @php
                        $total_factor = 0;
                        foreach ($document->charges as $charge) {
                            $total_factor = ($total_factor + $charge->factor) * 100;
                        }
                    @endphp
                    <tr>
                        <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">CARGOS ({{ $total_factor }}
                            %): {{ $document->currency_type->symbol }}</td>
                        <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total_charge, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">CARGOS:
                            {{ $document->currency_type->symbol }}</td>
                        <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total_charge, 2) }}</td>
                    </tr>
                @endif
            @endif

            @if ($document->perception)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Importe total:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Percepción:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->perception->amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Total a pagar:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">
                        {{ number_format($document->total + $document->perception->amount, 2) }}</td>
                </tr>
            @elseif($document->retention)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc" style="font-size: 16px;">Importe
                        total:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc" style="font-size: 16px;">
                        {{ number_format($document->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc">TOTAL RETENCIÓN
                        ({{ $document->retention->percentage * 100 }}
                        %): {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc">{{ number_format($document->retention->amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc">IMPORTE NETO:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc">{{ number_format($document->total - $document->retention->amount, 2) }}
                    </td>
                </tr>
            @else
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Total a pagar:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total, 2) }}</td>
                </tr>
            @endif

            @if (($document->retention || $document->detraction) && $document->total_pending_payment > 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">M. Pendiente:
                        {{ $document->currency_type->symbol }}</td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format($document->total_pending_payment, 2) }}</td>
                </tr>
            @endif

            @if ($balance < 0)
                <tr>
                    <td class="text-left desc" colspan="{{ $colspan - 1 }}" class="text-right desc font-bold desc">Vuelto:
                        {{ $document->currency_type->symbol }}
                    </td>
                    <td class="text-left desc" class="text-right desc font-bold desc">{{ number_format(abs($balance), 2, '.', '') }}</td>
                </tr>
            @endif

        </tbody>
    </table>
    <table class="full-width desc">
        <tr>
            <td class="text-left desc" width="65%" style="text-align: top; vertical-align: top;">
                @foreach (array_reverse((array) $document->legends) as $row)
                    @if ($row->code == '1000')
                        <p style="desc">Son: <span class="font-bold desc">{{ $row->value }}
                                {{ $document->currency_type->description }}</span></p>
                        @if (count((array) $document->legends) > 1)
                            <p><span class="font-bold desc desc">Leyendas</span></p>
                        @endif
                    @else
                        <p> {{ $row->code }}: {{ $row->value }} </p>
                    @endif
                @endforeach
                <br />
                @if ($document->detraction)
                    <p>
                        <span class="font-bold desc desc">
                            Operación sujeta al Sistema de Pago de Obligaciones Tributarias
                        </span>
                    </p>
                    <br />
                @endif
                @if ($customer->department_id == 16)
                    <br /><br /><br />
                    <div>
                        <center>
                            Representación impresa del Comprobante de Pago Electrónico.
                            <br />Esta puede ser consultada en:
                            <br /><a href="{!! route('search.index', ['external_id' => $document->external_id]) !!}"
                                style="text-decoration: none; font-weight: bold;color:black;">{!! url('/buscar') !!}</a>
                            <br /> "Bienes transferidos en la Amazonía
                            <br />para ser consumidos en la misma".
                        </center>
                    </div>
                    <br />
                @endif
                @foreach ($document->additional_information as $information)
                    @if ($information)
                        @if ($loop->first)
                            <strong>Información adicional</strong>
                        @endif
                        <p>
                            @if (\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
                                {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
                            @else
                                {{ $information }}
                            @endif
                        </p>
                    @endif
                @endforeach
                <br>
                @if (in_array($document->document_type->id, ['01', '03']))
                    @foreach ($accounts as $account)
                        <p>
                            <span class="font-bold desc desc">{{ $account->bank->description }}</span>
                            {{ $account->currency_type->description }}
                            <span class="font-bold desc desc">N°:</span> {{ $account->number }}
                            @if ($account->cci)
                                <span class="font-bold desc desc">CCI:</span> {{ $account->cci }}
                            @endif
                        </p>
                    @endforeach
                @endif
            </td>
            <td class="text-left desc" width="35%" class="text-right desc">
                <img src="data:image/png;base64, {{ $document->qr }}" style="margin-right: -10px;" />
                <p style="font-size: 9px">Código Hash: {{ $document->hash }}</p>
            </td>
        </tr>
    </table>
    @php
        $paymentCondition = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
    @endphp
    {{-- Condicion de pago  Crédito / Contado --}}
    <table class="full-width desc">
        <tr>
            <td class="text-left desc">
                <strong>Condición de Pago: {{ $paymentCondition }} </strong>
            </td>
        </tr>
    </table>

    @if ($document->payment_method_type_id)
        <table class="full-width desc">
            <tr>
                <td class="text-left desc">
                    <strong>Método de Pago: </strong>{{ $document->payment_method_type->description }}
                </td>
            </tr>
        </table>
    @endif

    @if ($document->payment_condition_id === '01')
        @if ($payments->count())
            {{-- @if ($payments->count()) --}}
            <table class="full-width desc">
                <tr>
                    <td class="text-left desc"><strong>Pagos:</strong></td>
                </tr>
                @php $payment = 0; @endphp
                @foreach ($payments as $row)
                    @isset($row->payment_method_type)
                        <tr>
                            <td class="text-left desc">&#8226; {{ $row->payment_method_type->description }}
                                - {{ $row->reference ? $row->reference . ' - ' : '' }}
                                {{ $document->currency_type->symbol }} {{ $row->payment + $row->change }}</td>
                        </tr>
                    @endisset
                @endforeach
                </tr>
            </table>
        @endif
    @else
        <table class="full-width desc">
            @foreach ($document->fee as $key => $quote)
                <tr>
                    <td class="text-left desc">
                        @if (!$configurations->show_the_first_cuota_document)
                            &#8226;
                            {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                            / Fecha: {{ $quote->date->format('d-m-Y') }} /
                            Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                        @else
                            @if ($key == 0)
                                &#8226;
                                {{ empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType() }}
                                / Fecha: {{ $quote->date->format('d-m-Y') }} /
                                Monto: {{ $quote->currency_type->symbol }}{{ $quote->amount }}
                            @endif
                        @endif

                    </td>
                </tr>
            @endforeach
            </tr>
        </table>
    @endif


    @if ($document->retention)
        <br>
        <table class="full-width desc">
            <tr>
                <td class="text-left desc">
                    <strong>Información de la retención:</strong>
                </td>
            </tr>
            <tr>
                <td class="text-left desc">Base imponible de la retención:
                    S/ {{ round($document->retention->amount_pen / $document->retention->percentage, 2) }}</td>
            </tr>
            <tr>
                <td class="text-left desc">Porcentaje de la retención {{ $document->retention->percentage * 100 }}%</td>
            </tr>
            <tr>
                <td class="text-left desc">Monto de la retención S/ {{ $document->retention->amount_pen }}</td>
            </tr>
        </table>
    @endif

    <br>
    <table class="full-width desc">
        <tr>
            <td class="text-left desc">
                <strong>Vendedor:</strong>
            </td>
        </tr>
        <tr>
            @if ($document->seller)
                <td class="text-left desc">{{ $document->seller->name }}</td>
            @else
                <td class="text-left desc">{{ $document->user->name }}</td>
            @endif
        </tr>
    </table>

    <table class="full-width desc">
        @php
            $configuration = \App\Models\Tenant\Configuration::first();
            $establishment_data = \App\Models\Tenant\Establishment::find($document->establishment_id);
        @endphp
        <tbody>
            <tr>
                @if ($configuration->yape_qr_documents && $establishment_data->yape_logo)
                    @php
                        $yape_logo = $establishment_data->yape_logo;
                    @endphp
                    @if ($yape_logo != null && file_exists(public_path("{$yape_logo}")))
                        <td class="text-left desc" class="text-center">
                            <table>
                                <tr>
                                    <td class="text-left desc">
                                        <strong>
                                            Qr yape
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left desc">
                                        <img src="data:{{ mime_content_type(public_path("{$yape_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$yape_logo}"))) }}"
                                            alt="{{ $company->name }}" class="company_logo"
                                            style="max-width: 150px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left desc">
                                        @if ($establishment_data->yape_owner)
                                            <strong>
                                                Nombre: {{ $establishment_data->yape_owner }}
                                            </strong>
                                        @endif
                                        @if ($establishment_data->yape_number)
                                            <br>
                                            <strong>
                                                Número: {{ $establishment_data->yape_number }}
                                            </strong>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>

                    @endif
                @endif
                @if ($configuration->plin_qr_documents && $establishment_data->plin_logo)
                    @php
                        $plin_logo = $establishment_data->plin_logo;
                    @endphp
                    @if ($plin_logo != null && file_exists(public_path("{$plin_logo}")))
                        <td class="text-left desc" class="text-center">
                            <table>
                                <tr>
                                    <td class="text-left desc">
                                        <strong>
                                            Qr plin
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left desc">
                                        <img src="data:{{ mime_content_type(public_path("{$plin_logo}")) }};base64, {{ base64_encode(file_get_contents(public_path("{$plin_logo}"))) }}"
                                            alt="{{ $company->name }}" class="company_logo"
                                            style="max-width: 150px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left desc">
                                        @if ($establishment_data->plin_owner)
                                            <strong>
                                                Nombre: {{ $establishment_data->plin_owner }}
                                            </strong>
                                        @endif
                                        @if ($establishment_data->plin_number)
                                            <br>
                                            <strong>
                                                Número: {{ $establishment_data->plin_number }}
                                            </strong>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    @endif

                @endif
            </tr>
        </tbody>
    </table>
    @if ($document->terms_condition)
        <br>
        <table class="full-width desc">
            <tr>
                <td class="text-left desc">
                    <h6 style="font-size: 12px; font-weight: bold;">Términos y condiciones del servicio</h6>
                    {!! $document->terms_condition !!}
                </td>
            </tr>
        </table>
    @endif
</body>

</html>
