<template>
    <div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12">
                <br />
                <br />
                <div class="row" v-if="applyFilter">
                    <div class="col-12 pb-3">Filtrar por:</div>
                    <div class="col-lg-2 col-md-4 col-sm-12 pb-2">
                        <div class="d-flex">
                            <el-select
                                v-model="search.column"
                                placeholder="Select"
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
                    <div
                        :class="`${
                            search.column != 'time_of_issue'
                                ? 'col-lg-2 col-md-2 col-sm-12 pb-2'
                                : 'col-lg-6 col-md-6 col-sm-12 pb-2'
                        }`"
                    >
                        <template
                            v-if="
                                [
                                    'date_of_issue',
                                    'date_of_due',
                                    'date_of_payment',
                                ].includes(search.column)
                            "
                        >
                            <el-date-picker
                                v-model="search.value"
                                type="date"
                                style="width: 100%"
                                placeholder="Buscar"
                                value-format="yyyy-MM-dd"
                            ></el-date-picker>
                        </template>
                        <template v-else-if="search.column === 'customer_id'">
                            <el-select
                                v-model="search.value"
                                :loading="loading"
                                :remote-method="searchRemoteCustomers"
                                clearable
                                filterable
                                placeholder="Escriba el nombre o número de documento del cliente"
                                popper-class="el-select-customers"
                                remote
                            >
                                <el-option
                                    v-for="option in customers"
                                    :key="option.id"
                                    :label="option.description"
                                    :value="option.id"
                                ></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="search.column === 'time_of_issue'">
                            <div class="row">
                                <div class="col-6">
                                    <el-time-select
                                        @change="changeTime"
                                        placeholder="Hora de inicio"
                                        v-model="startTime"
                                        :picker-options="{
                                            start: '08:30',
                                            step: '00:15',
                                        }"
                                    >
                                    </el-time-select>
                                </div>
                                <div class="col-6">
                                    <el-time-select
                                        @change="changeTime"
                                        placeholder="Hora de fin"
                                        v-model="endTime"
                                        :picker-options="{
                                            start: '08:30',
                                            step: '00:15',

                                            minTime: startTime,
                                        }"
                                    >
                                    </el-time-select>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <el-input
                                :placeholder="`${
                                    search.column === 'quotation_number'
                                        ? 'Número de cotización'
                                        : 'Buscar'
                                }`"
                                v-model="search.value"
                                style="width: 100%"
                            ></el-input>
                        </template>
                    </div>
                    <div class="col-lg-2 col-md-2 form-group">
                        <el-select
                            placeholder="Serie"
                            v-model="search.series"
                            filterable
                            clearable
                        >
                            <el-option
                                v-for="option in series"
                                :key="option.number"
                                :value="option.number"
                                :label="option.number"
                            ></el-option>
                        </el-select>
                    </div>
                    <div class="col-lg-2 col-md-2 form-group">
                        <el-input
                            placeholder="Número"
                            v-model="search.number"
                        ></el-input>
                    </div>
                    <div class="col-lg-2 col-md-3 form-group">
                        <el-select
                            placeholder="Estado de pago"
                            v-model="search.total_canceled"
                            clearable
                        >
                            <el-option :value="1" label="Pagado"></el-option>
                            <el-option :value="0" label="Pendiente"></el-option>
                        </el-select>
                    </div>
                    <div class="col-lg-2 col-md-2 form-group">
                        <el-input
                            v-model="search.purchase_order"
                            placeholder="Orden de compra"
                            clearable
                        ></el-input>
                    </div>
                    <!-- Date Range Filter -->
                    <div class="col-lg-4 col-md-6 col-sm-12 pb-2">
                        <el-date-picker
                            v-model="search.date_range"
                            type="daterange"
                            range-separator="a"
                            start-placeholder="Fecha inicio"
                            end-placeholder="Fecha fin"
                            value-format="yyyy-MM-dd"
                            style="width: 100%"
                        ></el-date-picker>
                    </div>
                    <!-- New Seller and Admin Filter -->
                    <div class="col-lg-4 col-md-6 col-sm-12 pb-2">
                        <el-select
                            v-model="search.seller_id"
                            placeholder="Seleccione un usuario"
                            clearable
                            filterable
                            style="width: 100%"
                        >
                            <el-option
                                v-for="user in users"
                                :key="user.id"
                                :label="user.name"
                                :value="user.id"
                            ></el-option>
                        </el-select>
                    </div>
                    <div class="col-lg-1 col-md-2 form-group">
                        <el-button
                            class="w-100"
                            type="primary"
                            @click="getRequestData"
                        >
                            <i class="fa fa-search"></i>
                        </el-button>
                    </div>
                    <template v-if="isDriver && records.length > 0">
                        <div class="col-lg-3 d-flex">
                            <el-button
                                class="submit"
                                type="success"
                                @click.prevent="clickDownload('excel')"
                                ><i class="fa fa-file-excel"></i> Exportar
                                Excel</el-button
                            >
                        </div>
                    </template>
                    <template v-if="!isIntegrateSystem">
                        <el-checkbox
                            v-model="search_by_plate"
                            :disabled="recordItem != null"
                            >Filtrar por
                            {{ isDriver ? "vehículo" : "placa" }}</el-checkbox
                        >
                        <div
                            v-if="search_by_plate"
                            class="col-lg-2 col-md-2 col-sm-12 pb-2"
                        >
                            <div class="form-group">
                                <el-input
                                    v-model="search.license_plate"
                                    placeholder="Placa"
                                    clearable
                                ></el-input>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <el-cascader
                                    placeholder="Seleccione ubicación"
                                    :options="locations"
                                    v-model="location_id"
                                    clearable
                                    :props="{ checkStrictly: true }"
                                    filterable
                                ></el-cascader>
                            </div>
                        </div>
                    </template>
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

                    <div class="row mb-5">
                        <div class="col-md-4 text-center">
                            Total notas de venta en soles S/.
                            {{ totals.total_pen }}
                        </div>
                        <div class="col-md-4 text-center">
                            Total pagado en soles S/.
                            {{ totals.total_paid_pen }}
                        </div>
                        <div class="col-md-4 text-center">
                            Total por cobrar en soles S/.
                            {{ totals.total_pending_paid_pen }}
                        </div>
                    </div>

                    <div>
                        <el-pagination
                            @current-change="getRequestData"
                            layout="total, prev, pager, next"
                            :total="pagination.total"
                            :current-page.sync="pagination.current_page"
                            :page-size="pagination.per_page"
                        ></el-pagination>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import queryString from "query-string";

export default {
    props: {
        isIntegrateSystem: Boolean,
        isDriver: Boolean,
        resource: String,
        applyFilter: {
            type: Boolean,
            default: true,
            required: false,
        },
    },
    data() {
        return {
            location_id: [],
            startTime: "",
            endTime: "",
            search: {
                column: null,
                value: null,
                series: null,
                total_canceled: null,
                date_range: null,
                seller_id: null, // Add seller_id to search object
            },
            totals: {
                total_pen: 0,
                total_paid_pen: 0,
                total_pending_paid_pen: 0,
            },
            columns: [],
            records: [],
            pagination: {},
            series: [],
            search_by_plate: false,
            recordItem: null,
            customers: [],
            loading: false,
            locations: [],
            users: [], // Add a users array to data
        };
    },
    computed: {},
    created() {
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
            this.getTotals();
        });
    },
    async mounted() {
        let column_resource = _.split(this.resource, "/");
        await this.$http
            .get(`/${_.head(column_resource)}/columns`)
            .then((response) => {
                this.columns = response.data;
                this.search.column = _.head(Object.keys(this.columns));
            });
        if (this.isIntegrateSystem) {
            this.search.column = "customer_id";
        }
        await this.$http
            .get(`/${_.head(column_resource)}/columns2`)
            .then((response) => {
                this.series = response.data.series;
                this.locations = response.data.locations;
            });

        await this.getRecords();
        await this.getTotals();
        await this.getUsers(); // Fetch users (sellers and admins) when component is mounted
    },
    methods: {
        changeTime() {
            if (this.startTime && this.endTime) {
                this.search.value = `${this.startTime} - ${this.endTime}`;
                this.getRequestData();
            }
        },
        async getUsers() {
            await this.$http
                .get("/users/sellers-and-admins")
                .then((response) => {
                    this.users = response.data;
                });
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
                    .catch((error) => console.log(error))
                    .finally(() => (this.loading = false));
            }
        },
        clickDownload(type) {
            window.open(
                `/package-handler/export/${type}?${this.getQueryParameters()}`,
                "_blank"
            );
        },
        getTotals() {
            this.$http
                .get(`/${this.resource}/totals`, {
                    params: {
                        ...this.search,
                        d_start: this.search.date_range
                            ? this.search.date_range[0]
                            : null,
                        d_end: this.search.date_range
                            ? this.search.date_range[1]
                            : null,
                    },
                })
                .then((response) => {
                    this.totals = response.data;
                });
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        getLastLocations(){
            return this.location_id[this.location_id.length - 1];
        },
        getRecords() {
            return this.$http
                .get(`/${this.resource}/records`, {
                    params: {
                        location_id: this.location_id.length > 0 ? this.getLastLocations() : null,
                        page: this.pagination.current_page,
                        limit: this.limit,
                        ...this.search,
                        d_start: this.search.date_range
                            ? this.search.date_range[0]
                            : null,
                        d_end: this.search.date_range
                            ? this.search.date_range[1]
                            : null,
                    },
                })
                .then((response) => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                });
        },
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.search,
                d_start: this.search.date_range
                    ? this.search.date_range[0]
                    : null,
                d_end: this.search.date_range
                    ? this.search.date_range[1]
                    : null,
            });
        },
        async changeClearInput() {
            this.search.value = "";
            await this.getRecords();
            await this.getTotals();
        },
        async getRequestData() {
            await this.getRecords();
            await this.getTotals();
        },
    },
};
</script>
