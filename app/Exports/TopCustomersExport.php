<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TopCustomersExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = DB::connection('tenant')->table('documents')
            ->select('persons.id', 'persons.name', DB::raw('COUNT(documents.id) as purchases'), DB::raw('SUM(documents.total) as total'))
            ->join('persons', 'documents.customer_id', '=', 'persons.id')
            ->groupBy('persons.id', 'persons.name')
            ->orderBy('purchases', 'desc');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('documents.date_of_issue', [$this->startDate, $this->endDate]);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID Cliente',
            'Nombre',
            'Compras',
            'Total'
        ];
    }
}
