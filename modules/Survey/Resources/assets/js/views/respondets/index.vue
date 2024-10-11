<template>
    <div v-loading="loading">
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Participantes</span></li>
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
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th></th>
                    </tr>
                    <tr slot-scope="{ row, index }">
                        <td>{{ index }}</td>
                        <td>{{ row.name }}</td>
                        <td>{{ row.email }}</td>
                        <td>{{ row.phone }}</td>
                        <td>
                            <a
                                href="#"
                                @click.prevent="clickCreate(row.id)"
                                class="btn btn-sm btn-warning"
                                ><i class="fa fa-edit"></i>
                            </a>
                            <el-tooltip
                                class="item"
                                effect="dark"
                                content="Actualizar contraseña"
                                placement="top"
                            >
                                <a
                                    href="#"
                                    @click="clickShowPassword(row.id)"
                                    class="btn btn-sm btn-info"
                                    ><i class="el-icon-key"></i>
                                </a>
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
        <form-password
            :recordId="recordId"
            :showDialog.sync="showDialogPassword"
        ></form-password>
    </div>
</template>

<script>
import FormSurvey from "./form.vue";
import FormPassword from "./form_password.vue";
import DataTable from "../components/DataTableRespondet.vue";
export default {
    props: ["configuration", "typeUser"],
    components: {
        DataTable,
        FormSurvey,
        FormPassword
    },
    data() {
        return {
            recordId: null,
            loading: false,
            resource: "survey/respondet",
            showDialog: false,
            showDialogPassword: false,
        };
    },
    methods: {
        clickShowPassword(id) {
            this.recordId = id;
            this.showDialogPassword = true;
        },
        clickCreate(id = null) {
            this.recordId = id;
            this.showDialog = true;
        },
    },
};
</script>

<style></style>
