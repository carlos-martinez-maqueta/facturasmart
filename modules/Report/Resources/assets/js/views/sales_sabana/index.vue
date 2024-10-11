<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header">
            <h3 class="my-0">Reporte de ventas (sabana)</h3>
        </div>
        <div class="card mb-0">
                <div class="card-body">
                    <data-table :resource="resource">
                        <tr slot="heading">
                            <th class="">#</th>
                            <th class="">Vendedor</th>
                            <th class="">Tipo documento</th>
                            <th class="">Número documento</th>
                            <th class="">Proveedor</th>
                            <th class="">Fecha de documento</th>
                            <th class="">Cod producto</th>
                            <th class="">Producto</th>
                            <th class="">Cantidad</th>
                            <th class="">Unidad de medida</th>
                            <th class="">Cod cliente</th>
                            <th class="">Cliente</th>
                            <th class="">Forma de pago</th>
                            <th class="">Importe sin igv</th>
                            <th class="">Importe con igv</th>
                            <th class="">Importe total</th>
                            
                        <tr>
                        <tr slot-scope="{ index, row }">
                            <td class="celda">{{ index }}</td> 
                            <td class="celda">
                                {{row.seller_name}}
                            </td>
                            <td class="celda">
                                {{row.document_type}}
                            </td>
                            <td class="celda">
                                {{row.document_number}}
                            </td>
                            <td class="celda">
                                {{row.supplier_name}}
                            </td>
                            <td class="celda">
                                {{row.date_of_issue}}
                            </td>
                            <td class="celda">
                                {{row.item_internal_id}}
                            </td>
                            <td class="celda">
                                {{row.item_description}}
                            </td>
                            <td class="celda">
                                {{row.quantity}}
                            </td>
                            <td class="celda">
                                {{row.unit_type_id}}
                            </td>
                            <td class="celda">
                                {{row.customer_internal_code}}
                            </td>
                            <td class="celda">
                                {{row.customer_name}}
                            </td>
                            <td class="celda">
                                {{row.payment_condition}}
                            </td>
                            <td class="celda">
                                {{row.unit_value}}
                            </td>
                            <td class="celda">
                                {{row.unit_price}}
                            </td>
                            <td class="celda">
                                {{row.total}}
                            </td>
                        

                        </tr>
                        
                    </data-table>
                     
                    
                </div> 
        </div>
 
    </div>
</template>

<script>
 
    import DataTable from '../../components/DataTableSalesSabana.vue'

    export default { 
        components: {DataTable},
        data() {
            return {
                resource: 'reports/sales-sabana',                 
                form: {}, 

            }
        },
        async created() { 
        },
        methods: { 
             
            
        }
    }
</script>