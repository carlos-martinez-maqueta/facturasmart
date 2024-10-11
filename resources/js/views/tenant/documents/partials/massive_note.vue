<template>
    <el-dialog
        title="Notas masivas"
        :visible="showDialog"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        @open="open"
        @close="close"
        append-to-body
    >
        <div class="row mt-2">
            <div class="col-md-6 col-lg-6 col-12">
                <label for="customer_id">Cliente</label>
                <el-select
                    v-model="form.customer_id"
                    filterable
                    remote
                    popper-class="el-select-customers"
                    clearable
                    placeholder="Nombre o número de documento"
                    :remote-method="searchRemotePersons"
                    :loading="loading_search"
                    @change="getRecords"
                >
                    <el-option
                        v-for="option in persons"
                        :key="option.id"
                        :value="option.id"
                        :label="option.description"
                    ></el-option>
                </el-select>
            </div>
            <div class="col-md-3 col-lg-3 col-12">
                <label for="start_date">Fecha de inicio</label>
                <el-date-picker
                    v-model="form.start_date"
                    type="date"
                    placeholder="Fecha de inicio"
                    value-format="yyyy-MM-dd"
                    format="yyyy-MM-dd"
                    @change="getRecords"
                    clearable
                ></el-date-picker>
            </div>
            <div class="col-md-3 col-lg-3 col-12">
                <label for="end_date">Fecha de fin</label>
                <el-date-picker
                    v-model="form.end_date"
                    type="date"
                    placeholder="Fecha de fin"
                    value-format="yyyy-MM-dd"
                    format="yyyy-MM-dd"
                    @change="getRecords"
                    clearable
                ></el-date-picker>
            </div>
        </div>
        <div class="row mt-2" v-loading="loading">
            <div class="table-responsive" v-if="records.length > 0">
                <table
                    class="table table-bordered table-striped table-hover table-sm"
                >
                    <thead>
                        <tr>
                            <th>
                                <el-tooltip
                                    :content="`Seleccionar la página (${records.length})`"
                                    placement="top"
                                >
                                    <el-checkbox
                                        v-model="checkAll"
                                        @change="handleCheckAll"
                                    ></el-checkbox>
                                </el-tooltip>
                            </th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th class="text-end">Documento</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in records" :key="record.id">
                            <td>
                                <el-checkbox
                                    @change="addDocumentId(record)"
                                    v-model="record.checked"
                                ></el-checkbox>
                            </td>
                            <td>{{ record.date_of_issue }}</td>
                            <td>
                                {{ record.customer_name }}
                                <br />
                                <small>
                                    {{ record.customer_number }}
                                </small>
                            </td>
                            <td class="text-end">{{ record.number_full }}</td>
                            <td class="text-end">{{ record.total }}</td>
                        </tr>
                    </tbody>
                </table>
                <el-pagination
                    @current-change="changePage"
                    layout="total, prev, pager, next"
                    :total="pagination.total"
                    :current-page.sync="pagination.current_page"
                    :page-size="pagination.per_page"
                >
                </el-pagination>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="send"
            :disabled="selecteds.length === 0"
            >Enviar</el-button>
        </span>
        <massive-note-detail
            :showDialog.sync="showDetail"
            :documents="selecteds"
            @reloadRecords="realoadRecords"
        >
        </massive-note-detail>
    </el-dialog>
</template>

<script>
import MassiveNoteDetail from "./massive_note_detail.vue";
import queryString from "query-string";

import moment from "moment";
export default {
    props: ["showDialog", "configuration"],
    components: {
        MassiveNoteDetail,
    },
    data() {
        return {
            selecteds: [],
            showDetail: false,
            checkAll: false,
            loading: false,
            loading_search: false,
            timer: null,
            form: {
                customer_id: null,
                start_date: null,
                end_date: null,
            },
            persons: [],
            records: [],
            pagination: {
                current_page: 1,
                total: 0,
                per_page: 10,
            },
        };
    },
    methods: {
        realoadRecords() {
            this.getRecords();
            this.$eventHub.$emit("reloadData");
        },
        addDocumentId(row) {
            if (row.checked) {
                this.selecteds.push({
                    id: row.id,
                    number_full: row.number_full,
                });
            } else {
                this.selecteds = this.selecteds.filter(
                    (selected) => selected.id !== row.id
                );
            }
        },
        changePage() {
            this.checkAll = false;
            this.handleCheckAll();
            this.getRecords();
        },
        handleCheckAll() {
            this.selecteds = [];
            this.records.forEach((record) => {
                record.checked = this.checkAll;
                if (this.checkAll) {
                    this.selecteds.push({
                        id: record.id,
                        number_full: record.number_full,
                    });
                }
            });
        },
        send() {
            this.showDetail = true;
        },
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form,
            });
        },
        getRecords() {
            this.loading = true;
            this.$http
                .get(
                    `/documents/massive-note/documents?${this.getQueryParameters()}`
                )
                .then((response) => {
                    console.log("🚀 ~ .then ~ response:", response);
                    this.records = response.data.data;
                    this.pagination = {
                        ...response.data.meta,
                        per_page: Number(response.data.meta.per_page),
                    };
                })
                .catch((error) => {
                    this.$message.error("Ocurrió un error al cargar los registros");
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        searchRemotePersons(input) {
            if (this.timer) {
                clearTimeout(this.timer);
            }
            this.timer = setTimeout(() => {
                if (input.length > 2) {
                    this.loading_search = true;
                    let parameters = `input=${input}`;
                    this.$http
                        .get(
                            `/reports/data-table/persons/customers?${parameters}`
                        )
                        .then((response) => {
                            this.persons = response.data.persons;
                        })
                        .catch((error) => {
                            console.log(error);
                        })
                        .finally(() => {
                            this.loading_search = false;
                        });
                }
            }, 500);
        },
        initForm() {
            this.form = {
                customer_id: null,
                start_date: moment().format("YYYY-MM-DD"),
                end_date: moment().format("YYYY-MM-DD"),
            };
        },
        open() {
            this.initForm();
            this.records = [];
            // this.getRecords();
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>
