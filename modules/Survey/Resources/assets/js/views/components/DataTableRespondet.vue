<template>
    <div v-loading="loading_submit">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="row" v-if="applyFilter">
                    <div class="col-lg-4 col-md-4 col-sm-12 pb-2">
                        <div class="d-flex">
                            <div style="width: 100px">Filtrar por:</div>
                            <el-select
                                v-model="search.column"
                                placeholder="Select"
                                @change="changeClearInput"
                            >
                                <el-option
                                    v-for="(label, key) in columns"
                                    :key="key"
                                    :value="key"
                                    :label="label"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12 pb-2">
                        <el-input
                            placeholder="Número de Cotización"
                            v-model="search.value"
                            style="width: 100%"
                            prefix-icon="el-icon-search"
                            @input="getRecords"
                        >
                        </el-input>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <slot name="heading"></slot>
                        </thead>
                        <tbody>
                            <slot
                                v-for="(row, index) in records"
                                :row="row"
                                :index="customIndex(index)"
                            ></slot>
                        </tbody>
                    </table>
                    <div>
                        <el-pagination
                            @current-change="getRecords"
                            layout="total, prev, pager, next"
                            :total="pagination.total"
                            :current-page.sync="pagination.current_page"
                            :page-size="pagination.per_page"
                        >
                        </el-pagination>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import queryString from "query-string";

export default {
    props: {
        type: String,
        isDriver: Boolean,
        configuration: {
            type: Object,
            required: false,
            default: () => {
                return {};
            },
        },
        productType: {
            type: String,
            required: false,
            default: "",
        },
        resource: String,
        applyFilter: {
            type: Boolean,
            default: true,
            required: false,
        },
        pharmacy: Boolean,
    },
    data() {
        return {
            order: false,
            search: {
                column: "name",
                value: "",
                order: "asc",
                order_price: null,
            },
            columns: [],
            records: [],
            personTypes: [],
            pagination: {},
            loading_submit: false,
            fromPharmacy: false,
            unitTypes: [],
            showDialogExportSellerCsv: false,
        };
    },
    created() {
        if (this.pharmacy !== undefined && this.pharmacy === true) {
            this.fromPharmacy = true;
        }
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
        this.$root.$refs.DataTable = this;

        if (this.type === "customers") {
            this.getPersonTypes();
        }
    },
    async mounted() {
        await this.getRecords();
        this.getColumns();
    },
    methods: {
        getColumns(){
            this.$http.get(`/${this.resource}/columns`).then((response) => {
                this.columns = response.data;
            console.log("🚀 ~ this.$http.get ~ response:", response)
            });
        },
        exportCsvSales() {
            let url = `/mall/export-csv-sales?${this.getQueryParameters()}`;
            window.open(url, "_blank");
        },
        openExportCsvSellers() {
            this.showDialogExportSellerCsv = true;
        },
        exportCsvSellers() {
            let url = `/mall/export-csv-sellers?${this.getQueryParameters()}`;
            window.open(url, "_blank");
        },
        clickDownload(type) {
            if (this.resource == "warranty_document/report") {
                window.open(
                    `/warranty_document/report/${type}?${this.getQueryParameters()}`,
                    "_blank"
                );
            } else {
                window.open(
                    `/package-handler/export_packages/${type}?${this.getQueryParameters()}`,
                    "_blank"
                );
            }
        },
        orderPrice() {
            if (!this.order) {
                this.search.order_price = null;
            } else {
                this.search.order_price = "asc";
            }
            this.getRecords();
        },
        async getUnitTypes() {
            this.loading_submit = true;
            const response = await this.$http.get(`/unit_types/records`);
            if (response.status == 200) {
                this.unitTypes = response.data.data;
            }
            this.loading_submit = false;
        },
        ordenar(value) {
            if (this.resource == "items") {
                this.search.order_price = value;
            } else {
                this.search.order = value;
            }
            this.getRecords();
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        async getPersonTypes() {
            const response = await this.$http.get(
                `/person-types/records?column=description&isPharmacy=false&page=1&value`
            );
            this.personTypes = response.data.data;
        },
        getRecords() {
            this.loading_submit = true;
            return this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then((response) => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                })
                .catch((error) => {})
                .then(() => {
                    this.loading_submit = false;
                });
        },
        getQueryParameters() {
            if (this.productType == "ZZ") {
                this.search.type = "ZZ";
            }
            if (this.productType == "PRODUCTS") {
                // Debe listar solo productos
                this.search.type = this.productType;
            }
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                driver: this.isDriver,
                isPharmacy: this.fromPharmacy,
                ...this.search,
            });
        },
        changeClearInput() {
            this.search.value = "";
            this.getRecords();
        },
        getSearch() {
            return this.search;
        },
    },
};
</script>
