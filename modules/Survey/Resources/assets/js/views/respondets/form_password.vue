<template>
    <el-dialog
        :visible="showDialog"
        append-to-body
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        @open="open"
        @close="close"
        title="Actualizar contraseña"
        width="50%"
        v-loading="loading"
    >
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <el-input
                        v-model="form.password"
                        placeholder="Contraseña"
                        clearable
                    >
                        <el-button
                            slot="append"
                            icon="el-icon-refresh"
                            @click="createPassword"
                        ></el-button>
                    </el-input>
                </div>
            </div>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button
                type="primary"
                :loading="loading"
                @click="updatePassword"
            >
                Actualizar
            </el-button>
        </div>
    </el-dialog>
</template>

<script>
export default {
    props: ["showDialog", "recordId"],
    data() {
        return {
            form: {
                password: "",
            },
            loading: false,
            title: "Actualizar contraseña",
        };
    },
    methods: {
        updatePassword() {
            this.loading = true;
            this.$http
                .post(
                    `/survey/respondet/${this.recordId}/update-password`,
                    this.form
                )
                .then((response) => {
                    this.loading = false;
                    this.$message({
                        message: response.data.message,
                        type: "success",
                    });
                    this.close();
                })
                .catch((error) => {
                    this.loading = false;
                    this.$message({
                        message: error.response.data.message,
                        type: "error",
                    });
                });
        },
        open() {
            this.initForm();
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        initForm() {
            this.form = {
                password: "",
            };
        },
        generatePassword(length) {
            let result = "";
            let characters =
                "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            let charactersLength = characters.length;
            for (let i = 0; i < length; i++) {
                result += characters.charAt(
                    Math.floor(Math.random() * charactersLength)
                );
            }
            return result;
        },

        createPassword() {
            this.form.password = this.generatePassword(10);
        },
    },
};
</script>
