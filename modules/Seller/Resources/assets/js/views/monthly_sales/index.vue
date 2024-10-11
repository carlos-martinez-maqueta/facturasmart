<template>
    <div v-loading="loading">
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Ventas mensuales</span></li>
            </ol>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <div class="row pb-2">
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="row">
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <label for="month">Mes</label>
                                <el-date-picker
                                    v-model="form.month"
                                    type="month"
                                    placeholder="Seleccione un mes"
                                    value-format="yyyy-MM"
                                    format="yyyy-MM"
                                    :picker-options="pickerOptions"
                                    @change="getRecords"
                                ></el-date-picker>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vendedor</th>
                                    <th>Venta máxima</th>
                                    <th class="text-end">N° documentos</th>
                                    <th class="text-end">Ventas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(record, index) in records"
                                    :key="index"
                                >
                                    <td>{{ index  }}</td>
                                    <td>{{ record.seller_name }}</td>
                                    <td>{{ record.max_sale }}</td>
                                    <td class="text-end">{{ record.total_documents }}</td>
                                    <td class="text-end">{{ record.total_sales.toFixed(2) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3">TOTALES</td>
                                    <td class="text-end">{{ total.total_documents }}</td>
                                    <td class="text-end">{{ total.total_sales.toFixed(2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import queryString from "query-string";
import moment from "moment";
export default {
    props: ["configuration"],
    data() {
        return {
            resource: "/seller/monthly-sales",
            loading: false,
            form: {},
            pickerOptions: {},
            records: [],
            total: {
                total_documents: 0,
                total_sales: 0,
            },
        };
    },

    mounted() {
        this.initForm();
        this.getRecords();
    },
    methods: {
        getQueryParameters() {
            return queryString.stringify({
                ...this.form,
            });
        },
        getRecords() {
            this.loading = true;
            this.$http
                .get(`${this.resource}/records?${this.getQueryParameters()}`)
                .then(
                    (response) => {
                        this.loading = false;
                        let { data } = response;
                        let { records, total_sales, total_documents } = data;
                        this.records = records;
                        this.total.total_sales = total_sales;
                        this.total.total_documents = total_documents;
                    },
                    (error) => {
                        console.log("🚀 ~ getTables ~ error:", error);
                        this.loading = false;
                        this.$message.error(
                            "Ocurrió un error al cargar los datos"
                        );
                    }
                )
                .catch((error) => {
                    console.log("🚀 ~ getTables ~ error:", error);
                    this.loading = false;
                    this.$message.error("Ocurrió un error al cargar los datos");
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        initForm() {
            this.form = {
                month: moment().format("YYYY-MM"),
            };
        },
    },
};
</script>

<style></style>
