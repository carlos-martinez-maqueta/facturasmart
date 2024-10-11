<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>Listado de tipo de reservas </span>
                </li>
            </ol>
            <div class="right-wrapper pull-right">
                <button
                    type="button"
                    class="btn btn-custom btn-sm mt-2 mr-2"
                    @click.prevent="showDialog = true"
                >
                    <i class="fas fa-plus"></i>
                    Nuevo
                </button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header">
                <h3 class="my-0">Listado de tipo de reservas</h3>
            </div>
            <div class="card-body">
                <data-table :resource="resource" ref="dataTable">
                    <tr slot="heading">
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Descripción</th>
                        <th class="text-center">Acciones</th>
                    </tr>

                    <tr></tr>
                    <tr slot-scope="{ row, index }">
                        <td>{{ index }}</td>
                        <td>{{ row.name }}</td>
                        <td>{{ row.location }}</td>
                        <td>{{ row.description }}</td>
                        <td class="text-center">
                            <button
                                type="button"
                                class="btn waves-effect waves-light btn-sm btn-info"
                                @click="clickEdit(row.id)"
                            >
                                Editar
                            </button>
                            <!-- <button
                                type="button"
                                class="btn waves-effect waves-light btn-sm btn-danger"
                            >
                                Eliminar
                            </button> -->
                        </td>
                    </tr>
                </data-table>
            </div>
        </div>
        <form-type
            @getRecords="getRecords"
            :showDialog.sync="showDialog"
            :recordId="recordId"
        ></form-type>
    </div>
</template>

<script>
import FormType from "./form.vue";
import DataTable from "../../../../components/DataTable.vue";
export default {
    components: {
        DataTable,
        FormType,
    },
    created() {
        // this.getRecords();
    },
    data() {
        return {
            showDialog: false,
            canchas: [],
            resource: "canchas/types",
            loading: false,
            recordId: null,
        };
    },
    methods: {
        clickEdit(id){
            this.recordId = id;
            this.showDialog = true;
        },
        getRecords() {
            this.$refs.dataTable.getRecords();
            // this.loading = true;
            // this.$http.get(`/canchas/types/records`).then((response) => {
            //     this.canchas = response.data.data;
            //     this.loading = false;
            // });
        },
    },
};
</script>
