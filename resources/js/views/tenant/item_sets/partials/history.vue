<template>
    <el-dialog
        :title="titleDialog"
        :visible="showDialog"
        @close="close"
        @open="create"
        append-to-body
        top="7vh"
    >
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-12">
                        <el-tabs v-model="activeName">
                            <el-tab-pane label="Últimas ventas" name="second">
                                <div
                                    class="table-responsive"
                                    v-loading="sales_loading"
                                >
                                    <table class="table">
                                        <thead>
                                            <tr slot="heading">
                                                <th>#</th>
                                                <th class="text-center">
                                                    Fecha
                                                </th>
                                                <th class="text-center">
                                                    Documento
                                                </th>
                                                <th class="text-center">
                                                    Estado
                                                </th>
                                                <th class="text-left">
                                                    Cliente
                                                </th>
                                                <th class="text-center">
                                                    Cantidad
                                                </th>
                                                <th class="text-center">
                                                    Precio
                                                </th>
                                                <th class="text-center">
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    row, index
                                                ) in sales_records"
                                                :key="index"
                                            >
                                                <td>
                                                    {{
                                                        salesCustomIndex(index)
                                                    }}
                                                </td>
                                                <td class="text-center">
                                                    {{ row.date_of_issue }}
                                                </td>
                                                <td class="text-center">
                                                    {{ row.number_full }}
                                                </td>
                                                <td class="text-center">
                                                    <span
                                                        slot="reference"
                                                        class="badge bg-secondary text-white"
                                                        :class="{
                                                            'bg-danger':
                                                                row.state_type_id ===
                                                                '11',
                                                            'bg-warning':
                                                                row.state_type_id ===
                                                                '13',
                                                            'bg-secondary':
                                                                row.state_type_id ===
                                                                '01',
                                                            'bg-info':
                                                                row.state_type_id ===
                                                                '03',
                                                            'bg-success':
                                                                row.state_type_id ===
                                                                '05',
                                                            'bg-secondary':
                                                                row.state_type_id ===
                                                                '07',
                                                            'bg-dark':
                                                                row.state_type_id ===
                                                                '09',
                                                            'bg-primary':
                                                                row.state_type_id ===
                                                                '55',
                                                        }"
                                                    >
                                                        {{
                                                            row.state_type_description
                                                        }}
                                                    </span>
                                                </td>
                                                <td class="text-left">
                                                    {{ row.customer_name
                                                    }}<br /><small
                                                        v-text="
                                                            row.customer_number
                                                        "
                                                    >
                                                    </small>
                                                    -
                                                    <span
                                                        style="
                                                            font-weight: bold;
                                                            color: #000;
                                                        "
                                                    >
                                                        {{ web_platform }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    {{ row.quantity }}
                                                </td>
                                                <td class="text-center">
                                                    {{ row.price }}
                                                </td>
                                                <td class="text-center">
                                                    {{ row.total }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div>
                                        <el-pagination
                                            @current-change="getSalesRecords"
                                            layout="total, prev, pager, next"
                                            :total="sales_pagination.total"
                                            :current-page.sync="
                                                sales_pagination.current_page
                                            "
                                            :page-size="
                                                sales_pagination.per_page
                                            "
                                        >
                                        </el-pagination>
                                    </div>
                                </div>
                            </el-tab-pane>
                        </el-tabs>
                    </div>
                </div>
            </div>
            <div class="form-actions text-end pt-2">
                <el-button @click.prevent="close()">Cerrar</el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>
import queryString from "query-string";

export default {
    props: ["showDialog", "recordId"],
    components: {},
    data() {
        return {
            showImportDialog: false,
            showDialogItemLots: false,
            resource: "items",
            activeName: "second",
            titleDialog: "Historial",
            data: {
                warehouses: [],
            },
            selecteWarehouse: {},
            form_history: null,
            sales_records: [],
            sales_loading: false,
            sales_pagination: {},
            purchases_records: [],
            purchases_loading: false,
            purchases_pagination: {},
            web_platform: "",
        };
    },
    created() {},
    async mounted() {
        // console.log(this.form)
        // await this.getSalesRecords()
    },
    methods: {
        getInfoItem() {
            this.$http
                .get(`/items/record/${this.recordId}`)
                .then((response) => {
                    let { data } = response.data;
                    console.log("🚀 ~ .then ~ data:", data);
                    let { web_platform } = data;
                    console.log("🚀 ~ .then ~ web_platform:", web_platform);
                    this.web_platform = web_platform;
                });
        },
        async create() {
            await this.initForm();
            this.getInfoItem();
            await this.getSalesRecords();
        },
        initForm() {
            this.form_history = {
                item_id: this.recordId,
            };
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        salesCustomIndex(index) {
            return (
                this.sales_pagination.per_page *
                    (this.sales_pagination.current_page - 1) +
                index +
                1
            );
        },
        getSalesRecords() {
            this.sales_loading = true;
            return this.$http
                .get(
                    `/${
                        this.resource
                    }/history-sales/records?${this.getSalesQueryParameters()}`
                )
                .then((response) => {
                    this.sales_records = response.data.data;
                    this.sales_pagination = response.data.meta;
                    this.sales_pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                })
                .then(() => {
                    this.sales_loading = false;
                });
        },

        getSalesQueryParameters() {
            return queryString.stringify({
                page: this.sales_pagination.current_page,
                form: JSON.stringify(this.form_history),
                limit: this.limit,
            });
        },
        getPurchasesQueryParameters() {
            return queryString.stringify({
                page: this.purchases_pagination.current_page,
                form: JSON.stringify(this.form_history),
                limit: this.limit,
            });
        },
    },
};
</script>
