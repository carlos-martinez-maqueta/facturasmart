<template>
    <el-dialog
        width="40%"
        :title="titleDialog"
        :visible="showDialog"
        @open="create"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        :show-close="false"
    >
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <el-alert
                            :title="getAlertTitle"
                            type="error"
                            :closable="false"
                            show-icon
                        >
                        </el-alert>
                    </div>
                    <div class="col-md-12 mt-3">
                        <div
                            class="form-group"
                            v-for="(token, index) in tokens"
                            :key="index"
                        >
                            <label class="control-label"
                                >Token de autorización ({{ token.name }} |
                                {{ Number(token.discount).toFixed(2) }}%)</label
                            >
                            <el-input
                                v-model="token.value"
                                @change="checkToken(token, index)"
                                @input="checkToken(token, index)"
                            ></el-input>
                            <template v-if="token.checked">
                                <small
                                    v-if="token.hasError"
                                    class="text-danger"
                                >
                                    <i class="fa fa-times"></i> El token es
                                    válido</small
                                >
                                <small v-else class="text-success">
                                    <i class="fa fa-check"></i> Token
                                    válido</small
                                >
                            </template>
                            <!-- <small v-if="errors.token" class="text-danger" v-text="errors.token[0]"></small> -->
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="form-actions text-end mt-3">
                <el-button @click.prevent="close()">Cerrar</el-button>
                <el-button
                    type="primary"
                    native-type="submit"
                    :loading="loading_submit"
                    >Emitir</el-button
                >
            </div>
        </form>
    </el-dialog>
</template>

<script>
export default {
    props: [
        "showDialog",
        "sellersDiscountLimit",
        "totalDiscountPercentage",
        "itemExtraPercentages",
    ],
    data() {
        return {
            titleDialog: "Permiso para registrar descuento",
            loading: false,
            errors: {},
            form: {},
            resource: "authorized-discount-users",
            loading_submit: false,
            tokens: [],
            timer: null,
        };
    },
    computed: {
    
        getAlertTitle() {
            // return `El porcentaje de descuento de la venta (${this.totalDiscountPercentage}%), es superior al límite configurado (${this.sellersDiscountLimit}%). Si desea continuar, comuníquese con el administrador.`
            return `Algunos productos tienen un porcentaje de descuento superior al límite configurado (${this.sellersDiscountLimit}%). Si desea continuar, comuníquese con el administrador.`;
        },
    },
    async created() {},
    methods: {
        checkToken(token, index) {
            if (token.value.length > 2) {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => {
                    this.$http
                        .post(`/${this.resource}/check-token`, {
                            token: token.value,
                        })
                        .then((response) => {
                            if (response.data.success) {
                                token.hasError = false;
                                token.isSuccess = true;
                                token.checked = true;
                            } else {
                                token.hasError = true;
                                token.isSuccess = false;
                                token.checked = true;
                            }
                        })
                        .catch((error) => {
                            token.hasError = true;
                            token.isSuccess = false;
                            token.checked = true;
                        });
                }, 500);
            }
        },
        initForm() {
            this.form = {
                token: null,
            };
        },
        create() {
            this.tokens = this.itemExtraPercentages.map((item) => ({
                name: item.description,
                discount: item.percentage_discount,
                value: null,
                hasError: false,
                isSuccess: true,
                checked: false,
            }));
        },

        async submit() {
            if (this.tokens.some((token) => token.hasError || !token.checked))
                return this.$message.error("Algunos tokens no son válidos");
            this.loading_submit = true;
            await this.$http
                .post(`/${this.resource}/validate-tokens`, {
                    tokens: this.tokens.map((token) => token.value),
                })
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$emit("tokenValidated");
                        this.close();
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data;
                    } else {
                        this.$message.error(error.response.data.message);
                    }
                })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>
