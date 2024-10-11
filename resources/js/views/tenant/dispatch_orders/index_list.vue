<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>RUTA LIMA</span></li>
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
        <div class="card mb-0" v-loading="loading">
            <div class="card-body">
                <template v-if="records.length > 0">
                    <el-collapse
                        v-model="activeName"
                        accordion
                        @change="getRoute"
                    >
                        <el-collapse-item
                            v-for="(route, index) in records"
                            :name="route.id"
                            :key="index"
                        >
                            <template slot="title">
                                <table class="table" style="margin-bottom: 0px">
                                    <tbody>
                                        <tr>
                                            <td colspan="5" valign="middle">
                                                <h4>
                                                    <strong
                                                        >Ruta:
                                                        {{
                                                            route.description
                                                        }}</strong
                                                    >
                                                </h4>
                                            </td>
                                            <td colspan="2" valign="middle">
                                                <h4>
                                                    <strong
                                                        >Fecha:
                                                        {{ route.date }}</strong
                                                    >
                                                </h4>
                                            </td>
                                            <td valign="middle">
                                                <h4>
                                                    <strong
                                                        >N° de ordenes:
                                                        {{
                                                            route.items_count
                                                        }}</strong
                                                    >
                                                </h4>
                                            </td>
                                            <td>
                                                <el-button
                                                    type="primary"
                                                    @click="pdfOpen(route.id,'a4')"
                                                    size="mini"
                                                >
                                                    <i
                                                        class="fas fa-file-pdf"
                                                    ></i>
                                                    A4
                                                </el-button>
                                                <el-button
                                                    type="primary"
                                                    @click="pdfOpen(route.id,'ticket')"
                                                    size="mini"
                                                >
                                                    <i
                                                        class="fas fa-file-pdf"
                                                    ></i>
                                                    Ticket
                                                </el-button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                            <div class="table-responsive" v-loading="loading">
                                <template v-if="items.length > 0">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ORDEN</th>
                                                <th>CLIENTE</th>
                                                <th>DISTRITO</th>
                                                <th>DIRECCION ENVIO</th>
                                                <th>OBSERVACION</th>
                                                <th>CONDICION PAGO</th>
                                                <th>ESTADO PAGO</th>
                                                <th>CONFIRMACION PAGO</th>
                                                <th>SALDO</th>
                                                <th>WHATSAPP</th>
                                                <th>LLAMADA</th>
                                                <th>GOOGLE MAPS</th>
                                                <th>VENDEDOR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(order, index) in items"
                                                :key="index"
                                            >
                                                <td>{{ index + 1 }}</td>
                                                <td>
                                                    {{ order.full_number }}
                                                </td>
                                                <td>
                                                    {{ order.customer_name }}
                                                </td>
                                                <td>
                                                    {{ order.district_name }}
                                                </td>
                                                <td>
                                                    {{ order.shipping_address }}
                                                </td>
                                                <td>
                                                    {{ order.observation }}
                                                </td>
                                                <td>
                                                    <span
                                                        v-for="(
                                                            payment_method, i
                                                        ) in order.payments_methods"
                                                        :key="i"
                                                        class="badge bg-primary text-white me-1"
                                                    >
                                                        {{ payment_method }}
                                                    </span>
                                                </td>

                                                <td>
                                                    {{
                                                        order.total_canceled
                                                            ? "Pagado"
                                                            : "Pendiente"
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        order.state_payment_id ==
                                                        "01"
                                                            ? "En espera"
                                                            : order.state_payment_id ==
                                                              "02"
                                                            ? "Aprobado"
                                                            : "Rechazado"
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        order.total_pending_paid
                                                    }}
                                                </td>

                                                <td>
                                                    <el-tag
                                                        v-if="
                                                            order.customer_telephone
                                                        "
                                                        type="success"
                                                        @click="
                                                            sendWhatsapp(order)
                                                        "
                                                        size="mini"
                                                        role="button"
                                                    >
                                                        <i
                                                            class="fab fa-whatsapp"
                                                        ></i>
                                                    </el-tag>
                                                </td>
                                                <td>
                                                    <el-tag
                                                        v-if="
                                                            order.customer_telephone
                                                        "
                                                        type="success"
                                                        @click="sendCall(order)"
                                                        size="mini"
                                                        role="button"
                                                    >
                                                        <i
                                                            class="fas fa-phone"
                                                        ></i>

                                                        {{
                                                            order.customer_telephone
                                                        }}
                                                    </el-tag>
                                                </td>

                                                <td>
                                                    <el-tag
                                                        v-if="
                                                            order.customer_location
                                                        "
                                                        role="button"
                                                        type="success"
                                                        @click="
                                                            openLocation(order)
                                                        "
                                                        size="mini"
                                                    >
                                                        <i
                                                            class="fas fa-map-marker-alt"
                                                        ></i>
                                                    </el-tag>
                                                </td>
                                                <td>
                                                    {{ order.seller_name }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <!-- <tfoot>
                                            <tr>
                                                <td colspan="5">
                                                    <a
                                                        href="#"
                                                        @click.prevent="
                                                            clickCreateorder(
                                                                section.id
                                                            )
                                                        "
                                                        class="btn btn-sm btn-primary"
                                                        ><i
                                                            class="fa fa-plus"
                                                        ></i>
                                                        Agregar pregunta</a
                                                    >
                                                </td>
                                            </tr>
                                        </tfoot> -->
                                    </table>
                                </template>
                                <template v-else>
                                    <div class="alert alert-danger">
                                        No hay ordenes incluidas
                                    </div>
                                </template>
                            </div>
                        </el-collapse-item>
                    </el-collapse>
                </template>
                <template v-else>
                    <div class="alert alert-info">No hay rutas registradas</div>
                </template>
            </div>
        </div>
        <create-list-modal :showDialog.sync="showDialogCreate" />
    </div>
</template>
<style>
.state_01_py {
    color: white;
    background: #ffcc00;
}
.state_02_py {
    color: white;
    background: #33cc33;
}
.state_03_py {
    color: white;
    background: red;
}
.state_base {
    padding: 5px;
    text-align: center;
    border-radius: 5px;
}
.state_1 {
    color: white;
    background: #ffcc00;
}
.state_2 {
    color: white;
    background: #ff9900;
}
.state_3 {
    color: white;
    background: #33cc33;
}
.state_4 {
    color: white;
    background: #33cc33;
}
.state_6 {
    color: white;
    background: #0070c0;
}
.state_5 {
    color: white;
    background: gray;
}
.state_7 {
    color: white;
    background: red;
}
.el-dropdown {
    vertical-align: top;
}
.el-dropdown + .el-dropdown {
    margin-left: 15px;
}
.el-icon-arrow-down {
    font-size: 12px;
}
</style>
<script>
import CreateListModal from "./partials/create_list_modal.vue";
import { deletable } from "../../../mixins/deletable";
import { mapActions, mapState } from "vuex/dist/vuex.mjs";
export default {
    props: ["soapCompany", "typeUser", "configuration", "userId"],
    mixins: [deletable],
    components: {
        CreateListModal,
    },
    computed: {
        ...mapState(["config"]),
        isMobileScreen() {
            return window.innerWidth <= 768;
        },
    },
    mounted() {
        window.addEventListener("resize", this.handleResize);
    },
    beforeDestroy() {
        window.removeEventListener("resize", this.handleResize);
    },
    data() {
        return {
            activeName: null,
            currentIdx: null,
            showDialogCreate: false,
            loading: false,
            records: [],
            items: [],
            showGuideModal: false,
            showGuideModalView: false,
            showModalResponsibles: false,
            showModalGenerateGuie: false,
            currentDocument: null,
            showPeriod: false,
            showModalGenerateCPE: false,
            showMigrateNv: false,
            resource: "dispatch-order",
            showDialogPayments: false,
            showDialogOptions: false,
            showDialogGenerate: false,
            saleNotesNewId: null,
            recordId: null,
            dispatchId: null,
            showDialogFinish: false,
            currentRecord: null,
            columns: {},
            isDriver: false,
            states: [],
            showDialogState: false,
            pagination: {},
        };
    },
    created() {
        this.loadConfiguration();
        this.$store.commit("setConfiguration", this.configuration);
        let { package_handlers } = this.configuration;
        this.isDriver = package_handlers;
        this.getTable();
        this.getRecords();
    },

    filters: {
        period(name) {
            let res = "";
            switch (name) {
                case "month":
                    res = "Mensual";
                    break;
                case "year":
                    res = "Anual";
                    break;
                default:
                    break;
            }

            return res;
        },
    },
    methods: {
        pdfOpen(id, type) {
            window.open(`/dispatch-order/pdf-route/${id}/${type}`, "_blank");
        },
        getRoute() {
            if (!this.activeName) return;
            this.loading = true;
            this.$http
                .get(`/${this.resource}/get-route-items/${this.activeName}`)
                .then((response) => {
                    this.items = response.data;
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        selectIdx(idx) {
            this.currentIdx = idx;
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        getRecords() {
            this.loading = true;
            this.$http
                .get(`/${this.resource}/get-route`)
                .then((response) => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        openLocation(row) {
            let { customer_location } = row;
            window.open(customer_location, "_blank");
        },
        sendCall(row) {
            window.open(`tel:${row.customer_telephone}`);
        },
        sendWhatsapp(row) {
            let message = `Hola, le saludamos de Campo Grande Peru, acerca de su pedido #${
                row.full_number.split("-")[1]
            }, nos 
encontramos en ruta, me confirma si puede recepcionar pedido`;
            message = encodeURIComponent(message);
            window.open(
                `https://api.whatsapp.com/send?text=${message}&phone=51${row.customer_telephone}`,
                "_blank"
            );
        },
        showChangeState(row) {
            this.currentRecord = row;
            this.showDialogState = true;
        },
        handleResize() {
            this.$forceUpdate();
        },
        clickGenerateGuide(id) {
            location.href = `/dispatches/create_new/dispatch_order/${id}`;
        },
        openDispatchFinish(id) {
            this.dispatchId = id;
            this.showDialogFinish = true;
        },
        viewGuide(row) {
            this.currentRecord = row;
            this.showGuideModalView = true;
        },
        setGuide(row) {
            this.currentRecord = row;
            this.showGuideModal = true;
        },
        update() {
            this.$refs.dataTable.getRecords();
        },

        async changeState([stateId, rowId]) {
            try {
                await this.$confirm(
                    "¿Está seguro de cambiar el estado?",
                    "Advertencia",
                    {
                        confirmButtonText: "Cambiar",
                        cancelButtonText: "Cancelar",
                        type: "warning",
                    }
                );
                this.loading = true;
                const response = await this.$http(
                    `/${this.resource}/change-state/${rowId}/${stateId}`
                );
                if (response.status == 200) {
                    this.$message.success(response.data.message);
                    this.$refs.dataTable.getRecords();
                }
            } catch (e) {
                return;
            } finally {
                this.loading = false;
            }
        },
        async getTable() {
            const response = await this.$http.get(`/${this.resource}/states`);
            const { states } = response.data;
            this.states = states;
        },
        setResponsible(row) {
            this.currentDocument = row;
            this.showModalResponsibles = true;
        },
        async clickKillDocument(id) {
            try {
                const confirm = await this.$confirm(
                    "¿Está seguro de eliminar el documento y todos los registros relacionados?",
                    "Advertencia",
                    {
                        confirmButtonText: "Eliminar",
                        cancelButtonText: "Cancelar",
                        type: "warning",
                    }
                );
                if (confirm) {
                    const response = await this.$http.get(
                        `/${this.resource}/kill/${id}`
                    );
                    console.log(response);
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$refs.dataTable.getRecords();
                    } else {
                        this.$message.error(response.data.message);
                    }
                }
            } catch (e) {
                return;
            }
        },
        openPeriod(row) {
            console.log(row);
            this.currentDocument = { ...row, document_type_id: "80" };
            this.showPeriod = true;
        },
        ...mapActions(["loadConfiguration"]),

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
        onOpenModalGenerateGuie() {
            this.showModalGenerateGuie = true;
        },
        onOpenModalGenerateCPE() {
            this.showModalGenerateCPE = true;
        },
        onOpenModalMigrateNv() {
            this.showMigrateNv = true;
        },
        clickDownload(external_id) {
            window.open(
                `/dispatch-order/downloadExternal/${external_id}/ticket`,
                "_blank"
            );
        },
        clickOptions(recordId) {
            this.saleNotesNewId = recordId;
            this.showDialogOptions = true;
        },
        sendToServer(recordId) {
            this.$http
                .post("/sale-notes/UpToOther", { sale_note_id: recordId })
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (
                        error.response !== undefined &&
                        error.response.status !== undefined &&
                        error.response.status.errors !== undefined &&
                        error.response.status === 422
                    ) {
                        this.errors = error.response.data.errors;
                    } else {
                        console.log(error);
                    }
                })
                .then(() => {});
        },
        clickGenerate(recordId) {
            this.recordId = recordId;
            this.showDialogGenerate = true;
        },
        clickPayment(recordId) {
            this.recordId = recordId;
            this.showDialogPayments = true;
        },
        clickCreate() {
            this.showDialogCreate = true;
        },
        changeConcurrency(row) {
            this.$http
                .post(`/${this.resource}/enabled-concurrency`, row)
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(response.data.message);
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error(response.data.message);
                    }
                })
                .catch((error) => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    } else {
                        console.log(error);
                    }
                })
                .then(() => {});
        },
        clickVoided(id) {
            this.anular(`/${this.resource}/anulate/${id}`).then(() =>
                this.$eventHub.$emit("reloadData")
            );
        },
        // deleteRelationInvoice(saleNote) {
        //     this.dataDeleteRelation.documents = saleNote.documents
        //     this.dataDeleteRelation.id = saleNote.id
        //     this.showDialogDeleteRelationInvoice = true
        // },
        sendDeleteRelationInvoice(id) {
            this.$http
                .post(`${this.resource}/delete-relation-invoice`, { id })
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(
                            "Se ha eliminado el comprobante relacionado correctamente."
                        );
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error("No se guardaron los cambios");
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
            this.$eventHub.$emit("reloadData");
        },
    },
};
</script>
