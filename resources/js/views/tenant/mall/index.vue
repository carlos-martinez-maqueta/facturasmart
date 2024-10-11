<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Exportación csv</span></li>
            </ol>
        </div>
        <div class="row page-header p-1">
            <div class="col-md-3">
                <label for="store_id"
                    >Código interno
                    <small class="text-muted">(store_id)</small>
                </label>
                <el-input
                    @input="setConfig"
                    v-model="mallConfig.store_id"
                    placeholder="Código interno"
                ></el-input>
            </div>
            <div class="col-md-3">
                <label for="store_id"
                    >Nombre
                    <small class="text-muted">(store_name)</small>
                </label>
                <el-input
                    @input="setConfig"
                    v-model="mallConfig.store_name"
                    placeholder="Código interno"
                ></el-input>
            </div>
            <div class="col-md-3">
                <label for="mall_id"
                    >Código de Mall
                    <small class="text-muted">(mall_id)</small>
                </label>
                <el-input
                    @input="setConfig"
                    v-model="mallConfig.mall_id"
                    placeholder="Código de Mall"
                ></el-input>
            </div>
            <div class="col-md-3">
                <label for="store_number"
                    >Número de tienda
                    <small class="text-muted">(store_number)</small>
                </label>
                <el-input
                    @input="setConfig"
                    v-model="mallConfig.store_number"
                    placeholder="Número de tienda"
                ></el-input>
            </div>
            <div class="col-md-3 mt-2">
                <el-button
                    type="primary"
                    @click="exportCsv"
                    icon="el-icon-download"
                >
                    Exportar TIENDA-CSV
                </el-button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <data-table :resource="resource" :state-types="state_types">
                    <tr slot="heading">
                        <th>#</th>
                        <th>Fecha emisión</th>
                        <th>Documento</th>
                        <th>Cliente</th>
                        <th class="text-end">Total</th>
                    </tr>

                    <tr></tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ index }}</td>
                        <td>{{ row.date_of_issue }}</td>
                        <td>{{ row.number }}</td>
                        <td>{{ row.customer_name }}</td>
                        <td class="text-end">{{ row.total }}</td>
                    </tr>
                </data-table>
            </div>
        </div>
    </div>
</template>
<style scoped>
.anulate_color {
    color: red;
}
</style>
<script>
import DataTable from "../../../components/DataTable.vue";
import { deletable } from "../../../mixins/deletable";
import { mapActions, mapState } from "vuex";

export default {
    props: [
        "isCommercial",
        "typeUser",
        "soapCompany",
        "generateOrderNoteFromQuotation",
        "isIntegrateSystem",
    ],
    mixins: [deletable],
    components: {
        DataTable,
    },
    computed: {
        ...mapState(["config"]),
    },
    data() {
        return {
            timer: null,
            mallConfig: {
                store_id: null,
                store_name: null,
                mall_id: null,
                store_number: null,
            },
            resource: "mall",
            showDialogSendEmailDocument: false,
            recordId: null,
            showDialogPayments: false,
            showDialogOptions: false,
            showDialogOptionsPdf: false,
            state_types: [],
            loading: true,
            columns: {
                total_exportation: {
                    title: "T.Exportación",
                    visible: false,
                },
                total_unaffected: {
                    title: "T.Inafecto",
                    visible: false,
                },
                total_exonerated: {
                    title: "T.Exonerado",
                    visible: false,
                },
                total_free: {
                    title: "T.Gratuito",
                    visible: false,
                },
                contract: {
                    title: "Contrato",
                    visible: false,
                },
                delivery_date: {
                    title: "T.Entrega",
                    visible: false,
                },
                referential_information: {
                    title: "Inf.Referencial",
                    visible: false,
                },
                order_note: {
                    title: "Pedidos",
                    visible: false,
                },
                exchange_rate_sale: {
                    title: "Tipo de cambio",
                    visible: false,
                },
            },
        };
    },
    async created() {
        console.log("is commercial: ", this.isCommercial);
        await this.filter();
        this.getConfig();
    },
    mounted() {
        this.loadConfiguration();
    },
    methods: {
        getConfig() {
            this.$http.get(`/mall/get_config`).then((response) => {
                console.log("🚀 ~ this.$http.get ~ response:", response)
                let {
                    data: { data },
                } = response;
                if (data) this.mallConfig = data;
            });
        },
        setConfig() {
            if (this.timer) {
                clearTimeout(this.timer);
            }
            this.timer = setTimeout(() => {
                console.log(
                    "🚀 ~ this.timer=setTimeout ~ this.mallConfig:",
                    this.mallConfig
                );
                this.$http
                    .post(`/mall/set_config`, this.mallConfig)
                    .then((response) => {
                        if (response.data.success) {
                            this.$message.success(
                                "Se guardaron los cambios correctamente."
                            );
                        } else {
                            this.$message.error("No se guardaron los cambios");
                        }
                    });
            }, 500);
        },
        exportCsv() {
            let { store_id, store_name, mall_id, store_number } = this.mallConfig;
            if (!store_id || !store_name || !mall_id || !store_number) {
                this.$message.error("Complete todos los campos para exportar.");
                return;
            }
            let url = `/mall/export-csv-company`;
            window.open(url, "_blank");
        },
        clickReturn(id) {
            this.$confirm(
                "¿Está seguro de hacer la devolución?",
                "Advertencia",
                {
                    confirmButtonText: "Sí",
                    cancelButtonText: "No",
                    type: "warning",
                }
            )
                .then(() => {
                    this.loading = true;
                    this.$http
                        .get(`/warranty_document/return_warranty/${id}`)
                        .then((response) => {
                            if (response.data.success) {
                                this.$message.success(
                                    "Se devolvió correctamente."
                                );
                                this.$eventHub.$emit("reloadData");
                            } else {
                                this.$message.error("No se pudo devolver.");
                            }
                        })
                        .catch((error) => {
                            console.log("error: ", error);
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                })
                .catch(() => {});
        },
        clickSendQuotation(id) {
            this.recordId = id;
            this.showDialogSendEmailDocument = true;
        },
        ...mapActions(["loadConfiguration"]),
        canMakeOrderNote(row) {
            let permission = true;

            // Si ya tiene Pedidos, no se genera uno nuevo
            if (row.order_note.full_number) {
                permission = false;
            } else {
                if (this.typeUser !== "admin") {
                    permission = this.generateOrderNoteFromQuotation;
                }
            }

            return permission;
        },
        clickPrintContract(external_id) {
            window.open(`/contracts/print/${external_id}/a4`, "_blank");
        },
        clickPayment(recordId) {
            this.recordId = recordId;
            this.showDialogPayments = true;
        },
        async changeStateType(row) {
            await this.updateStateType(
                `/${this.resource}/state-type/${row.state_type_id}/${row.id}`
            ).then(() => this.$eventHub.$emit("reloadData"));
        },
        async filter() {
            await this.$http
                .get(`/${this.resource}/filter`)
                .then((response) => {
                    this.state_types = response.data.state_types;
                });
        },
        clickEdit(id) {
            this.recordId = id;
            this.showDialogFormEdit = true;
        },
        clickOptions(recordId = null) {
            this.recordId = recordId;
            this.showDialogOptions = true;
        },
        clickOptionsPdf(recordId = null) {
            this.recordId = recordId;
            this.showDialogOptionsPdf = true;
        },
        clickAnulate(id) {
            this.anular(`/${this.resource}/anular/${id}`).then(() =>
                this.$eventHub.$emit("reloadData")
            );
        },
        makeOrder(quotation) {
            let tos = parseInt(quotation);
            localStorage.setItem("Quotation", tos);
            localStorage.setItem("FromQuotation", true);
            window.location.href = "/order-notes/create";
        },
        duplicate(id) {
            this.$http
                .post(`${this.resource}/duplicate`, { id })
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(
                            "Se guardaron los cambios correctamente."
                        );
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error("No se guardaron los cambios");
                    }
                })
                .catch((error) => {});
            this.$eventHub.$emit("reloadData");
        },
        clickGenerateDocument(recordId) {
            window.location.href = `/documents/create/quotations/${recordId}`;
        },
    },
};
</script>
