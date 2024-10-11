<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>Ordenes de compra</span>
                </li>
            </ol>
            <div class="right-wrapper pull-right">
                <a
                    :href="`/${resource}/create`"
                    class="btn btn-custom btn-sm mt-2 mr-2"
                >
                    <i class="fa fa-plus-circle"></i> Nuevo
                </a>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <data-table :resource="resource">
                    <tr slot="heading">
                        <th>#</th>
                        <th class="text-center">F. Emisión</th>
                        <th class="text-center">F. Vencimiento</th>
                        <th>Proveedor</th>
                        <th>Tipo</th>
                        <th>O. Compra</th>
                        <th class="text-center">Estado</th>
                        <th>O. Venta</th>
                        <th class="text-center">Moneda</th>

                        <th class="text-end">T.Gravado</th>
                        <th class="text-end">T.Igv</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Cliente</th>
                        <th class="text-end">Cotización</th>
                    </tr>
                    <tr></tr>
                    <tr slot-scope="{ index, row }">
                        <td>{{ index }}</td>
                        <td class="text-center">{{ row.date_of_issue }}</td>
                        <td class="text-center">{{ row.date_of_due }}</td>
                        <td>
                            {{ row.supplier_name }}
                            <br />
                            <small v-text="row.supplier_number"></small>
                        </td>
                        <td>
                            {{ row.type == "goods" ? "Bienes" : "Servicios" }}
                        </td>
                        <td>
                            {{ row.number }}
                            <br />
                            <small
                                v-text="row.document_type_description"
                            ></small>
                            <br />
                        </td>

                        <td class="text-center">
                            <span
                                class="badge bg-secondary text-white"
                                :class="{
                                    'bg-danger': row.state_type_id === '11',
                                    'bg-warning': row.state_type_id === '13',
                                    'bg-secondary': row.state_type_id === '01',
                                }"
                            >
                                {{ row.state_type_description }}
                            </span>
                        </td>

                        <td>{{ row.sale_opportunity_number_full }}</td>

                        <td class="text-center">{{ row.currency_type_id }}</td>

                        <td class="text-end">{{ row.total_taxed }}</td>
                        <td class="text-end">{{ row.total_igv }}</td>
                        <td class="text-end">{{ row.total }}</td>
                        <td class="text-end">
                            {{ row.customer_name }}
                        </td>
                        <td class="text-end">
                            {{ row.quotation_number }}
                        </td>
                    </tr>
                </data-table>
            </div>
        </div>
    </div>
</template>

<script>
import DataTable from "../../../../components/DataTablePurchaseOrder.vue";

import { deletable } from "@mixins/deletable";

export default {
    mixins: [deletable],
    // components: {DocumentsVoided, DocumentOptions, DataTable},
    components: { DataTable }, //DocumentOptions
    data() {
        return {
            showDialogVoided: false,
            resource: "purchase-orders/reports",
            recordId: null,
            showDialogOptions: false,
            showDialogGenerateDocument: false,
        };
    },
    created() {},
    methods: {
        clickCreate(id = "") {
            location.href = `/${this.resource}/create/${id}`;
        },
        clickVoided(recordId = null) {
            this.recordId = recordId;
            this.showDialogVoided = true;
        },
        clickDownload(external_id) {
            window.open(`/${this.resource}/download/${external_id}`, "_blank");
        },
        clickGenerateDocument(recordId) {
            this.recordId = recordId;
            this.showDialogGenerateDocument = true;
        },
        clickAnulate(id) {
            this.anular(`/${this.resource}/anular/${id}`).then(() =>
                this.$eventHub.$emit("reloadData")
            );
        },
        clickOptions(recordId = null) {
            this.recordId = recordId;
            this.showDialogOptions = true;
        },
    },
};
</script>
