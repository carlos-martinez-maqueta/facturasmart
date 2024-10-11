<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>Listado de reservas </span>
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
                <!-- <button type="button" class="btn btn-custom btn-sm mt-2 mr-2">
                    <i class="fas fa-plus"></i>
                    Agregar tipos de reservas
                </button> -->
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header">
                <h3 class="my-0">Listado de reservas</h3>
            </div>
            <div class="card-body">
                <data-table :resource="resource" ref="dataTable">
                    <tr slot="heading">
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Celular</th>
                        <th class="text-center">Hora</th>
                        <th class="text-center">Fecha</th>
                        <th>Duración</th>
                        <th>Ticket</th>
                        <th class="text-center">Acciones</th>
                    </tr>

                    <tr></tr>
                    <tr slot-scope="{ row, index }">
                        <td :class="`${row.anulado?'text-danger':''}`">
                            {{ index }}
                        </td>
                        <td :class="`${row.anulado?'text-danger':''}`">
                            {{ row.reservante }}
                        </td>
                        <td :class="`${row.anulado ?'text-danger':''}`">
                            {{ row.phone }}
                        </td>
                        <td
                            :class="`${row.anulado?'text-danger':''}`"
                            class="text-center"
                        >
                            {{ row.time }}
                        </td>
                        <td
                            :class="`${row.anulado?'text-danger':''}`"
                            class="text-center"
                        >
                            {{ row.date }}
                        </td>
                        <td :class="`${row.anulado?'text-danger':''}`">
                            {{ row.duration }}
                        </td>
                        <td :class="`${row.anulado?'text-danger':''}`">
                            {{ row.ticket }}
                        </td>
                        <!-- <td>
                             <template v-if="row.qr_code">
                                <img
                                    :src="
                                        'data:image/png;base64,' + row.qr_code
                                    "
                                    alt="qr"
                                    style="width: 150px; height: 150px"
                                />
                            </template> 
                        </td> -->
                        <td class="text-center">
                            <button
                                type="button"
                                class="btn waves-effect waves-light btn-sm btn-info"
                                @click.prevent="showPdf(row.id)"
                            >
                                Ver
                            </button>
                            <button
                                v-if="!row.anulado"
                                type="button"
                                class="btn waves-effect waves-light btn-sm btn-danger"
                                @click.prevent="anulate(row.id)"
                            >
                                Anular
                            </button>
                        </td>
                    </tr>
                </data-table>
            </div>
        </div>
        <form-cancha :showDialog.sync="showDialog"></form-cancha>
    </div>
</template>

<script>
import DataTable from "../../../components/DataTable.vue";
import FormCancha from "./form.vue";
export default {
    components: {
        DataTable,
        FormCancha,
    },
    data() {
        return {
            showDialog: false,
            canchas: [],
            resource: "canchas",
            showDialogPreview: false,
        };
    },
    methods: {
        showPdf(id){
            window.open(`/canchas/reserva/${id}`, "_blank");
        },
        anulate(id) {
            this.$confirm("¿Está seguro de anular esta reserva?", "Anular", {
                confirmButtonText: "Si",
                cancelButtonText: "No",
                type: "warning",
            })
                .then(() => {
                    this.$http
                        .post(`/canchas/anular/${id}`)
                        .then((response) => {
                            // this.getRecords();
                            this.$refs.dataTable.getRecords();
                        });
                })
                .catch(() => {
                    this.$message({
                        type: "info",
                        message: "Anulación cancelada",
                    });
                });
        },
    },
};
</script>
