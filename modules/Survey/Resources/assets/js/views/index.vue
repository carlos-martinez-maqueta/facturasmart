<template>
    <div v-loading="loading">
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Encuestas</span></li>
            </ol>

            <div class="right-wrapper pull-right">
                <a
                    href="#"
                    @click.prevent="clickCreate()"
                    class="btn btn-custom btn-sm mt-2 mr-2"
                    ><i class="fa fa-plus-circle"></i> Nuevo</a
                >
            </div>
        </div>

        <div class="card mb-0">
            <div class="card-body">
                <data-table
                    :resource="resource"
                    :configuration="configuration"
                    :typeUser="typeUser"
                >
                    <tr slot="heading">
                        <th>#</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                    <tr slot-scope="{ row, index }">
                        <td>{{ index }}</td>
                        <td>{{ row.title }}</td>
                        <td>{{ row.description }}</td>
                        <td>
                            <el-tooltip
                                class="item"
                                effect="dark"
                                content="Copiar URL"
                                placement="top"
                            >
                                <a
                                    href="#"
                                    @click.prevent="clickCopyUrl(row.uuid)"
                                    class="btn btn-sm btn-primary"
                                    ><i class="fa fa-copy"></i>
                                </a>
                            </el-tooltip>
                            <a
                                href="#"
                                @click.prevent="clickCreate(row.id)"
                                class="btn btn-sm btn-warning"
                                ><i class="fa fa-edit"></i>
                            </a>
                            <el-tooltip
                                class="item"
                                effect="dark"
                                :content="`${
                                    row.sections == 0 ? 'Agregar' : 'Ver'
                                } secciones`"
                                placement="top"
                            >
                                <a
                                    :href="`/survey/section/${row.uuid}`"
                                    class="btn btn-sm btn-info"
                                >
                                    {{ row.sections }}</a
                                >
                            </el-tooltip>
                            <el-tooltip
                                class="item"
                                effect="dark"
                                content="Participantes"
                                placement="top"
                            >
                                <button
                                    @click="clickParticipants(row.id)"
                                    class="btn btn-sm btn-success"
                                >
                                    <i class="fa fa-users"></i>
                                </button>
                            </el-tooltip>
                            <el-tooltip
                                class="item"
                                effect="dark"
                                content="Ver totales"
                                placement="top"
                            >
                                <button
                                    @click="clickViewTotals(row.id)"
                                    class="btn btn-sm btn-info"
                                >
                                    <i class="el-icon-s-data"></i>
                                </button>
                            </el-tooltip>
                        </td>
                    </tr>
                </data-table>
            </div>
        </div>
        <form-survey
            :recordId.sync="recordId"
            :showDialog.sync="showDialog"
            :resource="resource"
        ></form-survey>
        <modal-resolvers
            :id="recordId"
            :showDialog.sync="showParticipants"
        ></modal-resolvers>
        <modal-totals
            :id="recordId"
            :showDialog.sync="showTotals"
        ></modal-totals>
    </div>
</template>

<script>
import FormSurvey from "./form.vue";
import DataTable from "./components/DataTableSurvey.vue";
import ModalResolvers from "./modal_resolvers.vue";
import ModalTotals from "./modal_totals.vue";
export default {
    props: ["configuration", "typeUser"],
    components: {
        DataTable,
        FormSurvey,
        ModalResolvers,
        ModalTotals,
    },
    data() {
        return {
            recordId: null,
            loading: false,
            resource: "survey",
            showDialog: false,
            showParticipants: false,
            showTotals: false,
        };
    },
    methods: {
        clickViewTotals(id) {
            this.recordId = id;
            this.showTotals = true;
        },
        clickParticipants(id) {
            this.recordId = id;
            this.showParticipants = true;
        },
        clickCopyUrl(uuid) {
            const el = document.createElement("textarea");
            el.value = `${window.location.origin}/survey-resolve/resolve/${uuid}`;
            document.body.appendChild(el);
            el.select();
            document.execCommand("copy");
            document.body.removeChild(el);
            this.$message({
                message: "URL copiada",
                type: "success",
            });
        },
        clickCreate(id = null) {
            this.recordId = id;
            this.showDialog = true;
        },
    },
};
</script>

<style></style>
