<template>
    <div>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12">
                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Fecha de inicio</label>
                            <el-date-picker
                                v-model="form.date_start"
                                placeholder="Seleccione una fecha"
                                value-format="yyyy-MM-dd"
                                format="yyyy-MM-dd"
                            >
                            </el-date-picker>
                        </div>
                    </div>
                        <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">Fecha final</label>
                            <el-date-picker
                                v-model="form.date_end"
                                placeholder="Seleccione una fecha"
                                value-format="yyyy-MM-dd"
                                format="yyyy-MM-dd"
                            >
                            </el-date-picker>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Transportista</label>
                            <el-select
                                v-model="form.dispatcher_id"
                                clearable
                                placeholder="Seleccionar transportista"
                            >
                                <el-option
                                    v-for="option in dispatchers"
                                    :key="option.id"
                                    :label="
                                        option.number +
                                        ' - ' +
                                        option.name +
                                        ' - ' +
                                        option.number_mtc
                                    "
                                    :value="option.id"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>

                    <div
                        class="col-lg-9 col-md-9 col-md-9 col-sm-12"
                        style="margin-top: 29px"
                    >
                        <el-button
                            class="submit"
                            type="primary"
                            @click.prevent="getRecordsByFilter"
                            :loading="loading_submit"
                            icon="el-icon-search"
                            >Buscar</el-button
                        >

                        <template v-if="records.length > 0">
                            <el-button
                                class="submit"
                                type="danger"
                                icon="el-icon-tickets"
                                @click.prevent="clickDownload('pdf')"
                                >Exportar PDF</el-button
                            >

                            <el-button
                                class="submit"
                                type="success"
                                @click.prevent="clickDownload('excel')"
                                ><i class="fa fa-file-excel"></i> Exportal
                                Excel</el-button
                            >
                        </template>
                    </div>
                </div>
                <div class="row mt-1 mb-4"></div>
            </div>

            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table">
                        <template v-if="records.length > 0">
                            <!-- <thead>
                                <tr>
                                    <th colspan="8" class="text-left">
                                        <strong>VENDEDORES:</strong> {{ uniqueArraySellers().join(", ")}}
                                    </th>
                                </tr>
                            </thead> -->
                        </template>
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
<style>
.font-custom {
    font-size: 15px !important;
}
</style>
<script>
import moment from "moment";
import queryString from "query-string";

export default {
    props: {
        resource: String,
    },
    data() {
        return {
            sellers: [],
            loading_submit: false,
            columns: [],
            records: [],
            headers: headers_token,
            document_types: [],
            pagination: {},
            search: {},
            totals: {},
            establishment: null,
            users: [],
            form: {},
            dispatchers: [],
        };
    },
    computed: {},
    created() {
        this.initForm();
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
    },
    
    async mounted() {
        await this.$http.get(`/${this.resource}/filter`).then((response) => {
            this.document_types = response.data.document_types;
            this.users = response.data.users;
            this.dispatchers = response.data.dispatchers;
        });

        // await this.getRecords()
    },
    methods: {
        uniqueArraySellers() {
        return this.records
            .map((item) => item.seller_code)
            .filter((value, index, self) => self.indexOf(value) === index);
    },
        clickDownload(type) {
            let query = queryString.stringify({
                ...this.form,
            });
            window.open(`/${this.resource}/${type}/?${query}`, "_blank");
        },
        initForm() {
            this.form = {
                dispatcher_id: null,
                date_start: moment().format("YYYY-MM-DD"),
                date_end: moment().format("YYYY-MM-DD"),
            };
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        async getRecordsByFilter() {
        
            if (!this.form.dispatcher_id) {
                this.$message.error("Debe seleccionar un transportista");
                return;
            }
            this.loading_submit = await true;
            await this.getRecords();
            this.loading_submit = await false;
        },
        getRecords() {
            return this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then((response) => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                    this.loading_submit = false;
                })
                .catch((error) => {
                    console.log("🚀 ~ returnthis.$http.get ~ error:", error);
                    this.$message.error("Error al cargar los registros");
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
