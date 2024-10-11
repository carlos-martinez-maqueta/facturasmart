<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header">
            <h3 class="my-0">Liquidación de documentos por transportistas</h3>
        </div>
        <div class="card mb-0">
                <div class="card-body">
                    <data-table :resource="resource">
                        <tr slot="heading">
                            <th class="">#</th>
                            <th class="">Tipo Documento</th>
                            <th class="">Comprobante</th>
                            <th class="">Código Cliente</th>
                            <th class="">Nombre / Razón Social</th>
                            <th class="">Precio Venta</th>
                            <th class="">Forma de pago</th> 
                            <th class="">Deuda</th>
                            
                        <tr>
                        <tr slot-scope="{ index, row }">
                            <td>{{ index }}</td> 
                            <td>
                                {{row.document_type_description}}
                            </td>
                            <td>
                                {{row.document_number}}
                            </td>
                            <td>
                                {{row.customer_internal_id}}
                            </td>
                            <td>
                                {{row.customer_name}}
                            </td>
                            <td>
                                {{row.sale_price}}
                            </td>
                            <td>
                                {{row.payment_method_description}}
                            </td>
                            <td>
                                {{row.due}}
                            </td>

                        </tr>
                        
                    </data-table>
                     
                    
                </div> 
        </div>
 
    </div>
</template>

<script>
 
    import DataTable from '../../components/DataTableCarrierDocumentLiquidation.vue'

    export default { 
        components: {DataTable},
        data() {
            return {
                resource: 'reports/carrier-document-settlement',                 
                form: {}, 

            }
        },
        async created() { 
        },
        methods: { 
             
            
        }
    }
</script>