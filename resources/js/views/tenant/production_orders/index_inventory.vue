<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>{{ title }}</span>
                </li>
            </ol>
            <div class="right-wrapper pull-right"></div>
        </div>
        <div class="card mb-0" v-loading="loading">
            <div class="card-header">
                <h3 class="my-0">{{ title }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div
                            class="form-group"
                            :class="{ 'has-danger': errors.warehouse_id }"
                        >
                            <label
                                class="control-label font-weight-bold text-info"
                            >
                                Sucursal
                            </label>
                            <el-select
                                v-model="form.warehouse_id"
                                filterable
                                @change="changeWarehouse"
                            >
                                <el-option
                                    v-for="option in warehouses"
                                    :key="option.id"
                                    :value="option.id"
                                    :label="option.establishment_description"
                                ></el-option>
                            </el-select>
                            <small
                                class="text-danger"
                                v-if="errors.warehouse_id"
                                v-text="errors.warehouse_id[0]"
                            ></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div
                            class="form-group"
                            :class="{ 'has-danger': errors.category_id }"
                        >
                            <label
                                class="control-label font-weight-bold text-info"
                            >
                                Categoría
                            </label>
                            <el-select
                                v-model="form.category_id"
                                filterable
                                clearable
                            >
                                <el-option
                                    v-for="option in categories"
                                    :key="option.id"
                                    :value="option.id"
                                    :label="option.name"
                                ></el-option>
                            </el-select>
                            <small
                                class="text-danger"
                                v-if="errors.category_id"
                                v-text="errors.category_id[0]"
                            ></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div
                            class="form-group"
                            :class="{ 'has-danger': errors.category_id }"
                        >
                            <label
                                class="control-label font-weight-bold text-info"
                            >
                                Por Stock
                            </label>
                            <el-select
                                v-model="form.filter"
                                placeholder="Seleccionar filtro"
                            >
                                <el-option
                                    key="01"
                                    label="Todos"
                                    value="01"
                                ></el-option>
                                <el-option
                                    key="02"
                                    label="Stock < 0"
                                    value="02"
                                ></el-option>
                                <el-option
                                    key="03"
                                    label="Stock = 0"
                                    value="03"
                                ></el-option>
                                <el-option
                                    key="04"
                                    label="0 < Stock <= Stock mínimo"
                                    value="04"
                                ></el-option>
                                <el-option
                                    key="05"
                                    label="Stock > Stock mínimo"
                                    value="05"
                                ></el-option>
                            </el-select>
                        </div>
                    </div>
                </div>

                <div class="row mt-3" v-if="records.length > 0">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"> Producto </label>
                            <el-input
                                placeholder="Buscar por producto"
                                v-model="form.item_description"
                            >
                            </el-input>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <el-button
                            :loading="loading_submit"
                            class="submit"
                            icon="el-icon-search"
                            type="primary"
                            @click.prevent="getRecords"
                            >Buscar
                        </el-button>
                        <!--  -->

                        <template v-if="records.length > 0">
                            <el-button
                                icon="el-icon-tickets"
                                class="ml-3"
                                type="danger"
                                @click.prevent="clickDownload('pdf')"
                            >
                                Exportar PDF Vertical
                            </el-button>
                            <el-button
                                icon="el-icon-tickets"
                                class="ml-3"
                                type="danger"
                                @click.prevent="clickDownload('pdf-landscape')"
                            >
                                Exportar PDF Horizontal
                            </el-button>

                            <el-button
                                icon="el-icon-tickets"
                                class="ml-3"
                                type="danger"
                                @click.prevent="clickDownload('pdf-ticket')"
                            >
                                Exportar PDF Ticket
                            </el-button>

                            <el-button
                                class="ml-3"
                                type="success"
                                @click.prevent="clickDownload('xlsx')"
                                ><i class="fa fa-file-excel"></i> Exportar Excel
                            </el-button>
                            <el-button
                                class="ml-3"
                                type="success"
                                @click.prevent="clickPurchaseOrder"
                            >
                                <i class="fa fa-plus"></i>

                                Orden de compra
                            </el-button>
                        </template>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <template v-if="records.length > 0">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Producto</th>
                                                <th>Stock</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(row, index) in records"
                                                :key="index"
                                            >
                                                <td>
                                                    {{ customIndex(index) }}
                                                </td>
                                                <td>
                                                    {{ row.item_description }}
                                                </td>
                                                <td>{{ row.stock }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div>
                                        <el-pagination
                                            @current-change="getRecords"
                                            layout="total, prev, pager, next"
                                            :total="pagination.total"
                                            :current-page.sync="
                                                pagination.current_page
                                            "
                                            :page-size="pagination.per_page"
                                        >
                                        </el-pagination>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import queryString from "query-string";
import { DataTables } from "vue-data-tables";

export default {
    components: {
        DataTables,
    },
    props: ["typeUser"],
    data() {
        return {
            title: null,
            form: {},
            resource: "inventory-review",
            warehouses: [],
            categories: [],
            item_sizes: [],
            item_colors: [],
            records: [],
            errors: {},
            loading_submit: false,
            tableProps: {
                border: true,
            },
            currentPage: 1,
            per_page: 20,
            loading_review: false,
            input_search_barcode: null,
            init_review: false,
            loading: false,
            pagination: {
                total: 0,
                current_page: 1,
                per_page: 20,
            },
        };
    },
    created() {
        this.title = "Revisión de inventario";
        this.initForm();
        this.filters();
    },
    methods: {
        async clickPurchaseOrder(){
            await this.$http
                .get(
                    `/${
                        this.resource
                    }/records-paginate-items-id?${this.getQueryParameters()}`
                )
                .then((response) => {
                    console.log("🚀 ~ .then ~ response:", response)
                    let items_id = response.data
                    items_id = items_id.join('|')
                    localStorage.setItem('items_id_inventory', items_id);
                    window.open(
                        `/purchase-orders/create`, "_blank"
                    );
                    
                })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        async clickDownload(format) {
            window.open(
                `/${
                    this.resource
                }/download/${format}?${this.getQueryParameters()}`,
                "_blank"
            );
        },
        findItem(barcode) {
            return _.find(this.records, { item_barcode: barcode });
        },
        changeInputSearch() {
            const item = this.findItem(this.input_search_barcode);

            if (item) {
                item.input_stock = parseFloat(item.input_stock) + 1;
                this.input_search_barcode = null;
                this.$refs.inputSearchByBarcode.$el
                    .getElementsByTagName("input")[0]
                    .focus();

                this.$notify({
                    title: "",
                    message: "Cantidad incrementada!",
                    type: "success",
                    duration: 700,
                });
            } else {
                this.itemBarcodeNotFound();
            }
        },
        itemBarcodeNotFound() {
            this.input_search_barcode = null;
            this.$message.error("No se encontró el producto.");
        },
        setFocusInInputSearch() {},
        async reviewStock() {
            this.loading_review = true;
            this.init_review = true;

            await this.sleep(200);

            await this.records.forEach((row) => {
                row.difference = row.stock - parseFloat(row.input_stock);
            });

            this.loading_review = false;
        },
        sleep(ms) {
            return new Promise((resolve) => setTimeout(resolve, ms));
        },
        getQueryParameters() {
            return queryString.stringify({
                ...this.form,
                page: this.pagination.current_page,
            });
        },
        async getRecords() {
            // if(this.form.filter_by_variants)
            // {
            //     if(!this.form.item_color_id && !this.form.item_size_id) return this.$message.error('Debe seleccionar al menos un filtro de las variantes.')
            // }

            this.loading_submit = true;
            await this.$http
                .get(
                    `/${
                        this.resource
                    }/records-paginate?${this.getQueryParameters()}`
                )
                .then((response) => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                })
                .then(() => {
                    this.loading_submit = false;
                    this.setFocusInInputSearch();
                });
        },
        changeWarehouse() {
            const warehouse = _.find(this.warehouses, {
                id: this.form.warehouse_id,
            });
            this.form.establishment_id = warehouse.establishment_id;
        },
        initForm() {
            this.form = {
                filter: "01",
                item_description: null,
                warehouse_id: null,
                establishment_id: null,
                category_id: null,
                filter_by_variants: false,
                item_color_id: null,
                item_size_id: null,
            };
        },
        filters() {
            this.$http.get(`/${this.resource}/filters`).then((response) => {
                this.warehouses = response.data.warehouses;
                this.categories = response.data.categories;
                this.item_colors = response.data.item_colors;
                this.item_sizes = response.data.item_sizes;

                this.form.warehouse_id =
                    this.warehouses.length > 0 ? this.warehouses[0].id : null;
                this.changeWarehouse();
            });
        },
    },
};
</script>
