<template>
    <div v-loading="loading_submit">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="row">
                    <div class="col-md-3">
                        <label class="control-label">Periodo</label>
                        <el-select v-model="form.period" @change="changePeriod">
                            <el-option
                                key="month"
                                label="Por mes"
                                value="month"
                            ></el-option>
                            <el-option
                                key="between_months"
                                label="Entre meses"
                                value="between_months"
                            ></el-option>
                            <el-option
                                key="date"
                                label="Por fecha"
                                value="date"
                            ></el-option>
                            <el-option
                                key="between_dates"
                                label="Entre fechas"
                                value="between_dates"
                            ></el-option>
                        </el-select>
                    </div>

                    <template
                        v-if="
                            form.period === 'month' ||
                            form.period === 'between_months'
                        "
                    >
                        <div class="col-md-3">
                            <label class="control-label">Mes de</label>
                            <el-date-picker
                                v-model="form.month_start"
                                :clearable="false"
                                format="MM/yyyy"
                                type="month"
                                value-format="yyyy-MM"
                                @change="changeDisabledMonths"
                            ></el-date-picker>
                        </div>
                    </template>
                    <template v-if="form.period === 'between_months'">
                        <div class="col-md-3">
                            <label class="control-label">Mes al</label>
                            <el-date-picker
                                v-model="form.month_end"
                                :clearable="false"
                                :picker-options="pickerOptionsMonths"
                                format="MM/yyyy"
                                type="month"
                                value-format="yyyy-MM"
                                @change="getRecords"
                            ></el-date-picker>
                        </div>
                    </template>
                    <template
                        v-if="
                            form.period === 'date' ||
                            form.period === 'between_dates'
                        "
                    >
                        <div class="col-md-3">
                            <label class="control-label">Fecha del</label>
                            <el-date-picker
                                v-model="form.date_start"
                                :clearable="false"
                                format="dd/MM/yyyy"
                                type="date"
                                value-format="yyyy-MM-dd"
                                @change="changeDisabledDates"
                            ></el-date-picker>
                        </div>
                    </template>
                    <template v-if="form.period === 'between_dates'">
                        <div class="col-md-3">
                            <label class="control-label">Fecha al</label>
                            <el-date-picker
                                v-model="form.date_end"
                                :clearable="false"
                                :picker-options="pickerOptionsDates"
                                format="dd/MM/yyyy"
                                type="date"
                                value-format="yyyy-MM-dd"
                                @change="getRecords"
                            ></el-date-picker>
                        </div>
                    </template>

                    <div
                        class="col-lg-3 col-md-4 col-sm-12 pb-2"
                        v-if="search.column == 'between_dates'"
                    >
                        <el-date-picker
                            v-model="search.end_dates"
                            style="width: 100%"
                            value-format="yyyy-MM-dd"
                            @change="getRecords"
                        >
                        </el-date-picker>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Establecimiento</label>
                            <el-select
                                v-model="form.establishment_id"
                                clearable
                                @change="getRecords"
                            >
                                <el-option
                                    v-for="option in establishments"
                                    :key="option.id"
                                    :value="option.id"
                                    :label="option.description"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <label class="control-label">Cliente</label>
                        <el-select
                            v-model="form.person_id"
                            :loading="loading"
                            :remote-method="searchRemoteCustomers"
                            filterable
                            placeholder="Escriba el nombre o número de documento del cliente"
                            popper-class="el-select-customers"
                            remote
                            @change="getRecords"
                        >
                            <el-option
                                v-for="option in customers"
                                :key="option.id"
                                :label="option.description"
                                :value="option.id"
                            ></el-option>
                        </el-select>
                    </div>
                    <div class="col-lg-3">
                        <label class="control-label">Cotización</label>
                        <el-input
                            placeholder="Número de Cotización"
                            v-model="form.number"
                            style="width: 100%"
                            prefix-icon="el-icon-search"
                            @input="getRecordsTimer"
                        >
                        </el-input>
                    </div>
                </div>
            </div>
            <div class="d-flex mt-1" v-if="records.length > 0">
                <div class="col-md-3">
                    <el-button
                        class="submit"
                        type="success"
                        @click.prevent="clickDownload('excel')"
                        ><i class="fa fa-file-excel"></i> Exportar
                        Excel</el-button
                    >
                </div>
                <div class="col-md-3">
                    <el-button
                        class="submit"
                        type="danger"
                        @click.prevent="clickDownload('pdf')"
                    >
                        <i class="fa fa-file"></i> Exportar PDF
                    </el-button>
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
        <seller-export-csv
            :showDialog.sync="showDialogExportSellerCsv"
        ></seller-export-csv>
    </div>
</template>

<script>
import queryString from "query-string";
import SellerExportCsv from "./SellerExportCsv.vue";

export default {
    components: { SellerExportCsv },
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
            form: {
                period: "month",
                month_start: moment().format("YYYY-MM"),
                month_end: moment().format("YYYY-MM"),
                date_start: moment().format("YYYY-MM-DD"),
                date_end: moment().format("YYYY-MM-DD"),
                establishment_id: "",
                person_id: "",
                number: "",
            },
            establishments: [],
            loading: false,
            pickerOptionsDates: {
                disabledDate: (time) => {
                    time = moment(time).format("YYYY-MM-DD");
                    return this.form.date_start > time;
                },
            },
            pickerOptionsMonths: {
                disabledDate: (time) => {
                    time = moment(time).format("YYYY-MM");
                    return this.form.month_start > time;
                },
            },
            order: false,
            search: {
                column: null,
                value: null,
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
            timer: null,
            customers: [],
            showDialogExportSellerCsv: false,
        };
    },
    created() {
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
        this.$root.$refs.DataTable = this;
    },
    async mounted() {
        await this.getRecords();
        this.getTables();
    },
    methods: {
        getRecordsTimer() {
            if (this.timer) {
                clearTimeout(this.timer);
            }
            this.timer = setTimeout(() => {
                this.getRecords();
            }, 500);
        },
        searchRemoteCustomers(input) {
            if (input.length > 0) {
                this.loading = true;
                let parameters = `input=${input}&document_type_id=&operation_type_id=`;

                this.$http
                    .get(`/documents/search/customers?${parameters}`)
                    .then((response) => {
                        this.customers = response.data.customers;
                    })
                    .catch((error) => this.axiosError(error))
                    .finally(() => (this.loading = false));
            }
        },
        getTables() {
            this.$http.get(`tables`).then((response) => {
                this.establishments = response.data.establishments;
            });
        },
        changeDisabledDates() {
            if (this.form.date_end < this.form.date_start) {
                this.form.date_end = this.form.date_start;
            }
            this.getRecords();
        },
        changeDisabledMonths() {
            if (this.form.month_end < this.form.month_start) {
                this.form.month_end = this.form.month_start;
            }
            this.getRecords();
        },
        changePeriod() {
            if (this.form.period === "month") {
                this.form.month_start = moment().format("YYYY-MM");
                this.form.month_end = moment().format("YYYY-MM");
            }
            if (this.form.period === "between_months") {
                this.form.month_start = moment()
                    .startOf("year")
                    .format("YYYY-MM"); //'2019-01';
                this.form.month_end = moment().endOf("year").format("YYYY-MM");
            }
            if (this.form.period === "date") {
                this.form.date_start = moment().format("YYYY-MM-DD");
                this.form.date_end = moment().format("YYYY-MM-DD");
            }
            if (this.form.period === "between_dates") {
                this.form.date_start = moment()
                    .startOf("month")
                    .format("YYYY-MM-DD");
                this.form.date_end = moment()
                    .endOf("month")
                    .format("YYYY-MM-DD");
            }
        },

        clickDownload(type) {
            window.open(
                `/purchase-orders/reports/${type}?${this.getQueryParameters()}`,
                "_blank"
            );
        },

        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
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
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form,
            });
        },
    },
};
</script>
