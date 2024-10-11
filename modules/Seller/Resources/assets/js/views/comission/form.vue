<template>
    <el-dialog
        v-loading="loading"
        :close-on-click-modal="false"
        @open="open"
        @close="close"
        :title="title"
        :visible="showDialog"
        append-to-body
    >
        <div class="row mt-2">
            <div class="col-md-6 col-lg-6 col-12">
                <label for="percentage">Porcentaje</label>
                <el-input
                    v-model="form.percentage"
                    placeholder="Porcentaje"
                    clearable
                    type="number"
                ></el-input>
            </div>
            <div class="col-md-6 col-lg-6 col-12">
                <label for="margin">Margen</label>
                <el-input
                    v-model="form.margin"
                    placeholder="Margen"
                    clearable
                    type="number"
                ></el-input>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="submit">Guardar</el-button>
        </span>
    </el-dialog>
</template>

<script>
export default {
    props: ["showDialog", "recordId"],
    data() {
        return {
            resource: "/seller/comission",
            loading: false,
            title: "Crear comisión",
            form: {
                percentage: 0,
                margin: 0,
            },
        };
    },
    methods: {
        getRecord() {
            this.loading = true;
            this.$http
                .get(`${this.resource}/record/${this.recordId}`)
                .then((response) => {
                    this.form = response.data;
                })
                .catch((error) => {
                    this.$message.error("Error al obtener la comisión");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        initForm() {
            this.form = {
                percentage: 0,
                margin: 0,
            };
        },
        open() {
            this.initForm();
            if (this.recordId) {
                this.getRecord();
                this.title = "Editar comisión";
            }
        },

        submit() {
            if (!this.form.percentage || !this.form.margin) {
                this.$message.error("Todos los campos son requeridos");
                return;
            }
            if (this.form.percentage == 0) {
                this.$message.error("El porcentaje no puede ser 0");
                return;
            }
            this.loading = true;
            this.$http
                .post(this.resource, this.form)
                .then((response) => {
                    this.loading = false;
                    this.$message.success("Comisión guardada correctamente");
                    this.$eventHub.$emit("reloadData");
                    this.$emit("update:showDialog", false);
                })
                .catch((error) => {
                    this.loading = false;
                    this.$message.error("Error al guardar la comisión");
                });
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>

<style></style>
