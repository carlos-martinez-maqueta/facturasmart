<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Person;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TopCustomersExport;

class Reporttopcliente extends Controller
{
    public function index(Request $request)
    {
        // Verificar que las tablas existen
        if (!DB::connection('tenant')->getSchemaBuilder()->hasTable('documents') || !DB::connection('tenant')->getSchemaBuilder()->hasTable('persons')) {
            return view('clientestopreport.index')->withErrors('Las tablas necesarias no existen en la base de datos.');
        }

        // Filtro de fechas
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Consulta para obtener los clientes y el número de compras realizadas y la suma total
        $query = DB::connection('tenant')->table('documents')
            ->select('persons.id', 'persons.name', DB::raw('COUNT(documents.id) as purchases'), DB::raw('SUM(documents.total) as total'))
            ->join('persons', 'documents.customer_id', '=', 'persons.id')
            ->groupBy('persons.id', 'persons.name')
            ->orderBy('purchases', 'desc');

        // Aplicar el filtro de fechas si están presentes
        if ($startDate && $endDate) {
            $query->whereBetween('documents.date_of_issue', [$startDate, $endDate]);
        }

        $topCustomers = $query->get();

        return view('clientestopreport.index', compact('topCustomers', 'startDate', 'endDate'));
    }

    public function export(Request $request)
    {
        return Excel::download(new TopCustomersExport($request->start_date, $request->end_date), 'top_customers.xlsx');
    }
}
