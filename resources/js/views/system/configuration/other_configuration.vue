<template>
    <div class="card mb-2">
        <div class="card-header">
            <h3 class="my-0">Otras configuraciones</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label class="control-label">
                        Habilitar contraseña segura
                        <el-tooltip
                            class="item"
                            content="Se solicitará una contraseña segura (cumplir patrón) al registrar cliente"
                            effect="dark"
                            placement="top-start"
                        >
                            <i class="fa fa-info-circle"></i>
                        </el-tooltip>
                    </label>

                    <div
                        :class="{ 'has-danger': errors.regex_password_client }"
                        class="form-group"
                    >
                        <el-switch
                            v-model="form.regex_password_client"
                            active-text="Si"
                            inactive-text="No"
                            @change="submit"
                        ></el-switch>
                        <small
                            v-if="errors.regex_password_client"
                            class="text-danger"
                            v-text="errors.regex_password_client[0]"
                        ></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <el-tooltip
                        class="item"
                        content="Eliminar carpetas sin clientes activos"
                        effect="dark"
                        placement="top-start"
                    >
                        <el-button type="primary" @click="openDeleteFolders"
                            >Eliminar carpetas</el-button
                        >
                    </el-tooltip>
                </div>
                <div class="col-md-6">
                    <label for="minutes_send_regularizare">
                        Minutos para enviar regularización
                    </label>
                    <el-input
                        v-model="form.minutes_send_regularizare"
                        placeholder="Minutos"
                        clearable
                        @input="submit"
                        step="1"
                    ></el-input>
                </div>
                <div class="col-md-6">
                    <label for="minutes_send_regularizare">
                        Minutos para verificar cdr
                    </label>
                    <el-input
                        v-model="form.minutes_verify_cdr"
                        placeholder="Minutos"
                        clearable
                        @input="submit"
                        step="1"
                    ></el-input>
                </div>
            </div>
            <!-- <div class="form-actions text-end pt-2">
                <el-button type="primary" native-type="submit" :loading="loading_submit">Guardar</el-button>
            </div> -->
        </div>
        <delete-tenant-folder
            :showDialog.sync="showDialogDeleteFolders"
        ></delete-tenant-folder>
    </div>
</template>

<script>
import DeleteTenantFolder from "./delete_tenants_folder.vue";
export default {
    components: {
        DeleteTenantFolder,
    },
    data() {
        return {
            loading_submit: false,
            resource: "configurations",
            errors: {},
            form: {},
            showDialogDeleteFolders: false,
        };
    },
    async created() {
        await this.initForm();
        this.getRecord();
    },
    methods: {
        getRecord(){
            this.$http.get(`/configurations/other-configuration`).then((response) => {
                this.form = response.data;
            });
        },
        openDeleteFolders() {
            this.showDialogDeleteFolders = true;
        },
        initForm() {
            this.errors = {};
            this.form = {
                regex_password_client: false,
                minutes_send_regularizare: 0,
                minutes_verify_cdr: 0,
            };
        },
        submit() {
            this.loading_submit = true;
            this.$http
                .post(`/${this.resource}/other-configuration`, this.form)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        console.log(error);
                    }
                })
                .then(() => {
                    this.loading_submit = false;
                });
        },
    },
};
</script>
