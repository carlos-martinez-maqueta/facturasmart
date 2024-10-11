<template>
    <el-dialog
        :title="title"
        :visible="showDialog"
        width="50%"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        v-loading="loading"
        @open="open"
        @close="close"
    >
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="title">Título</label>
                    <el-input
                        v-model="form.title"
                        placeholder="Título"
                        clearable
                    >
                    </el-input>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="description">Descripción</label>
                    <el-input
                        type="textarea"
                        v-model="form.description"
                        placeholder="Descripción"
                        clearable
                    >
                    </el-input>
                </div>
            </div>
            <div class="col-md-12">
                <el-upload
                    :action="`/survey/image/upload`"
                    :data="{ type: 'items' }"
                    :headers="headers"
                    :on-success="onSuccess"
                    :show-file-list="false"
                    class="avatar-uploader"
                >
                    <img
                        v-if="form.image_url"
                        :src="form.image_url"
                        class="avatar"
                    />
                    <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                </el-upload>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="save">Guardar</el-button>
        </span>
    </el-dialog>
</template>

<script>
export default {
    props: ["showDialog", "resource", "recordId"],
    data() {
        return {
            form: {
                title: "",
                description: "",
                image: "",
                image_url: "",
                temp_path: "",
            },
            loading: false,
            title: "Nueva Encuesta",
                headers: headers_token,
        };
    },
    methods: {
            onSuccess(response, file, fileList) {
            if (response.success) {
                this.form.image = response.data.filename;
                this.form.image_url = response.data.temp_image;
                this.form.temp_path = response.data.temp_path;
                this.$forceUpdate();
            } else {
                this.$message.error(response.message);
            }
        },
        initForm() {
            this.form = {
                title: "",
                description: "",
                image: "",
                image_url: "",
                temp_path: "",
            };
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        getRecord() {
            this.$http
                .get(`${this.resource}/record/${this.recordId}`)
                .then((response) => {
                    this.form = response.data;
                });
        },
        open() {
            if (this.recordId) {
                this.title = "Editar Encuesta";
                this.getRecord();
            }
            this.initForm();
            // this.$emit("update:showDialog", true);
        },
        save() {
            if (!this.form.title) {
                this.$message.error("El título es requerido");
                return;
            }
            this.loading = true;
            this.$http
                .post(`${this.resource}`, this.form)
                .then((response) => {
                    this.$message.success("Encuesta guardada correctamente");
                    this.$emit("update:showDialog", false);
                    this.$eventHub.$emit("reloadData");
                })
                .catch((error) => {
                    console.log("🚀 ~ save ~ error:", error);
                    this.$message.error("Error al guardar la encuesta");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
