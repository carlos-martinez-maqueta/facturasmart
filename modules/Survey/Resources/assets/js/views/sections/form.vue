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
                    <label for="description">Subtitulo</label>
                    <el-input
                        v-model="form.subtitle"
                        placeholder="Descripción"
                        clearable
                    >
                    </el-input>
                </div>
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
    props: ["showDialog", "resource", "recordId", "survey"],
    data() {
        return {
            form: {},
            loading: false,
            title: "Nueva Encuesta",
        };
    },
    methods: {
        initForm() {
            this.form = {
                title: "",
                subtitle: "",
                order:1,
                survey_id: this.survey.id,
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
            this.initForm();
            if (this.recordId) {
                this.title = "Editar sección";
                this.getRecord();
            }
            console.log(this.form);
            // this.$emit("update:showDialog", true);
        },
        save() {
            if (!this.form.title) {
                this.$message.error("El título es requerido");
                return;
            }
            this.loading = true;
            this.$http
                .post(`/${this.resource}`, this.form)
                .then((response) => {
                    this.$message.success("Sección guardada correctamente");
                    this.$emit("update:showDialog", false);
                    this.$emit("reloadData");
                })
                .catch((error) => {
                    console.log("🚀 ~ save ~ error:", error);
                    this.$message.error("Error al guardar la sección");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
