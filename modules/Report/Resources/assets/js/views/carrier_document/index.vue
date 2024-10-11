<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header">
            <h3 class="my-0">Relación de transporte acumulado por transportistas</h3>
        </div>
        <div class="card mb-0">
                <div class="card-body">
                    <data-table :resource="resource">
                        <tr slot="heading">
                            <th class="">#</th>
                            <th class="">Código</th>
                            <th class="">Descripción</th>
                            <th class="">Cantidad</th>
                            <th class="">Unidad de medida</th>
                            <th class="">Cantidad equivalente</th>
                            <th class="">Precio de venta</th>
                            
                        <tr>
                        <tr slot-scope="{ index, row }">
                            <td>{{ index }}</td> 
                            <td>
                                {{row.internal_id}}
                            </td>
                            <td>
                                {{row.description}}
                            </td>
                            <td>
                                {{row.quantity}}
                            </td>
                            <td>
                                {{row.unit_type_id}}
                            </td>
                            <td>
                                {{row.presentations}}
                            </td>
                            <td>
                                {{row.total}}
                            </td>
                        

                        </tr>
                        
                    </data-table>
                     
                    
                </div> 
        </div>
 
    </div>
</template>

<script>
 
    import DataTable from '../../components/DataTableCarrierDocument.vue'

    export default { 
        components: {DataTable},
        data() {
            return {
                resource: 'reports/carrier-document',                 
                form: {}, 

            }
        },
        async created() { 
        },
        methods: { 
             
            
        }
    }
</script>