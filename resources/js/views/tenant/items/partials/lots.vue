<template>
    <el-dialog
        :title="titleDialog"
        width="40%"
        :visible="showDialog"
        @open="create"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        append-to-body
        :show-close="false"
    >
        <div class="form-body" v-if="lots">
            <div class="row">
                <div class="col-12">
                    <el-checkbox
                        v-model="barcode"
                        label="barcode"
                        @change="changeBarcode"
                    >
                        Ingresar con lector de código de barras
                    </el-checkbox>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label class="control-label">Inicio de rango</label>
                    <el-input
                        v-model="start_range"
                        placeholder="Inicio del rango"
                    ></el-input>
                </div>
                <div class="col-md-4">
                    <label class="control-label">Fin de rango</label>
                    <el-input
                        v-model="end_range"
                        placeholder="Fin del rango"
                    ></el-input>
                </div>
                <div class="col-md-2">
                    <br />
                    <el-tooltip
                        effect="dark"
                        content="Agregar multiples rangos de series"
                        placement="top"
                    >
                        <el-button
                            size="mini"
                            type="success"
                            @click.prevent="clickMultiRange"
                            class="mt-4"
                        >
                            <i class="fa fa-tasks" aria-hidden="true"></i>
                        </el-button>
                    </el-tooltip>
                </div>
                <div class="col-md-2">
                    <br />
                    <el-tooltip
                        effect="dark"
                        content="Agregar rango de series"
                        placement="top"
                    >
                        <el-button
                            size="mini"
                            type="primary"
                            @click.prevent="addRange"
                            class="mt-4"
                        >
                            <i class="fa fa-plus"></i>
                        </el-button>
                    </el-tooltip>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <table width="100%">
                        <thead>
                            <tr width="100%">
                                <th v-if="lots.length > 0">Serie</th>
                                <th v-if="lots.length > 0">Estado</th>
                                <th v-if="lots.length > 0">Fecha</th>
                                <th width="15%">
                                    <a
                                        href="#"
                                        @click.prevent="clickAddLot"
                                        class="text-center font-weight-bold text-info"
                                    >
                                        <small>[+ Agregar]</small>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, index) in paginatedLots"
                                :key="index"
                                width="100%"
                            >
                                <td>
                                    <div class="form-group mb-2 mr-2">
                                        <el-input
                                            :ref="`serie_${index}`"
                                            @blur="
                                                duplicateSerie(
                                                    row.series,
                                                    index
                                                )
                                            "
                                            @input="insertSerie"
                                            v-model="row.series"
                                        ></el-input>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group mb-2 mr-2">
                                        <el-select v-model="row.state">
                                            <el-option
                                                v-for="(
                                                    option, index
                                                ) in states"
                                                :key="index"
                                                :value="option"
                                                :label="option"
                                            ></el-option>
                                        </el-select>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group mb-2 mr-2">
                                        <el-date-picker
                                            v-model="row.date"
                                            type="date"
                                            value-format="yyyy-MM-dd"
                                            :clearable="false"
                                        ></el-date-picker>
                                    </div>
                                </td>
                                <td class="series-table-actions text-center">
                                    <button
                                        type="button"
                                        class="btn waves-effect waves-light btn-sm btn-danger"
                                        @click.prevent="clickCancel(index)"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                <br />
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">
                                    <el-pagination
                                        @current-change="handleCurrentChange"
                                        layout="total, prev, pager, next"
                                        :total="lots.length"
                                        :current-page.sync="currentPage"
                                        :page-size="itemsPerPage"
                                    >
                                    </el-pagination>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="form-actions text-end pt-2">
            <el-button @click.prevent="clickCancelSubmit()">Cancelar</el-button>
            <el-button type="primary" @click="submit">Guardar</el-button>
        </div>
        <modal-range-series-vue
            :show-dialog.sync="showMultiRange"
            :to-attend="toAttend"
            @setRange="setRange"
        ></modal-range-series-vue>
    </el-dialog>
</template>

<script>
import ModalRangeSeriesVue from "@components/ModalRangeSeries.vue";
import { set } from "vue";
export default {
    components: {
        ModalRangeSeriesVue,
    },
    props: ["showDialog", "lots", "stock", "recordId"],
    data() {
        return {
            barcode: false,
            start_range: "",
            end_range: "",
            currentPage: 1,
            itemsPerPage: 20,
            titleDialog: "Series",
            loading: false,
            errors: {},
            toAttend: 0,
            form: {},
            states: ["Activo", "Inactivo", "Desactivado", "Voz", "M2m"],
            showMultiRange: false,
        };
    },
    async created() {
        this.barcode = localStorage.getItem("barcode") == "true" ? true : false;
        // await this.$http.get(`/pos/payment_tables`)
        //     .then(response => {
        //         this.payment_method_types = response.data.payment_method_types
        //         this.cards_brand = response.data.cards_brand
        //         this.clickAddLot()
        //     })
    },
    computed: {
        paginatedLots() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.lots.slice(start, end);
        },
    },
    methods: {
        insertSerie(input) {
            if (this.barcode) {
                let hasDuplicates = this.lots.filter(
                    (lot) => lot.series == input
                ).length;
                if (hasDuplicates > 1) {
                    this.$message.error(
                        "La serie ya ha sido ingresada, por favor verifique"
                    );
                    //obten el index de la serie duplicada, siendo esta la ultima de las repetidas
                    let index = this.lots
                        .map((lot) => lot.series)
                        .lastIndexOf(input);
                    this.lots[index].series = "";
                    return;
                }
                setTimeout(() => {
                    let index = this.lots.findIndex(
                        (lot) => lot.series == input
                    );
                    if (index > -1) {
                        if (this.lots[index + 1]) {
                            this.$refs[`serie_${index + 1}`][0].focus();
                        }
                    }
                }, 300);
            }
        },
        changeBarcode() {
            localStorage.setItem("barcode", this.barcode);
        },
        clickMultiRange() {
            this.toAttend =
                this.stock -
                this.lots.filter((lot) => lot.series != null).length;
            this.showMultiRange = true;
        },
        setRange(series) {
            let localLots = [...this.lots.filter((lot) => lot.series != null)];

            for (let i = 0; i < series.length; i++) {
                let serie = series[i];
                localLots.push({
                    id: null,
                    item_id: null,
                    series: serie,
                    date: moment().format("YYYY-MM-DD"),
                    state: "Activo",
                });
            }
            this.$emit("update:lots", localLots);
            this.start_range = "";
            this.end_range = "";

            this.currentPage = 1;
        },
        addRange() {
            //verificar si el rango es valido
            if (this.start_range == "" || this.end_range == "") {
                return this.$message.error("Ingrese el rango de series");
            }

            if (BigInt(this.start_range) > BigInt(this.end_range)) {
                return this.$message.error("El rango de series no es valido");
            }
            let prefixLength =
                this.start_range.length > 5 ? this.start_range.length - 5 : 0;
            let prefix = this.start_range.slice(0, prefixLength);
            let start = parseInt(this.start_range.slice(prefixLength));
            let end = parseInt(this.end_range.slice(prefixLength));
            let quantity = end - start + 1;
            let lotWithSerie = this.lots.filter((lot) => lot.series != null);
            if (quantity + lotWithSerie.length != this.stock) {
                return this.$message.error(
                    "La cantidad de series no coincide con el stock"
                );
            }
            let localLots = [...lotWithSerie];

            for (let i = 0; i < quantity; i++) {
                localLots.push({
                    id: null,
                    item_id: null,
                    series: `${prefix}${(start + i)
                        .toString()
                        .padStart(
                            this.start_range.length - prefix.length,
                            "0"
                        )}`,
                    date: moment().format("YYYY-MM-DD"),
                    state: "Activo",
                });
            }
            this.$emit("update:lots", localLots);
            this.start_range = "";
            this.end_range = "";

            this.currentPage = 1;
        },
        handleCurrentChange(val) {
            this.currentPage = val;
        },
        async duplicateSerie(data, index) {
            let duplicates = await _.filter(this.lots, { series: data });
            if (duplicates.length > 1) {
                this.lots[index].series = "";
            }
        },
        create() {
            this.start_range = "";
            this.end_range = "";
            this.currentPage = 1;
            if (!this.recordId) {
                if (this.lots.length == 0) {
                    this.addMoreLots(this.stock);
                } else {
                    let quantity = this.stock - this.lots.length;
                    if (quantity > 0) {
                        this.addMoreLots(quantity);
                    }
                }
            }
        },
        addMoreLots(number) {
            for (let i = 0; i < number; i++) {
                this.clickAddLot();
            }
        },
        deleteMoreLots(number) {
            for (let i = 0; i < number; i++) {
                this.lots.pop();
                this.$emit("addRowLot", this.lots);
            }
        },
        async validateLots() {
            let error = 0;

            await this.lots.forEach((element) => {
                if (element.series == null) {
                    error++;
                }
            });

            if (error > 0)
                return {
                    success: false,
                    message: "El campo serie es obligatorio",
                };

            return { success: true };
        },
        async submit() {
            let val_lots = await this.validateLots();
            if (!val_lots.success) return this.$message.error(val_lots.message);

            await this.$emit("addRowLot", this.lots);
            await this.$emit("update:showDialog", false);
        },
        clickAddLot() {
            if (!this.recordId) {
                if (this.lots.length >= this.stock)
                    return this.$message.error(
                        "La cantidad de registros es superior al stock o cantidad"
                    );
            }

            this.lots.push({
                id: null,
                item_id: null,
                series: null,
                date: moment().format("YYYY-MM-DD"),
                state: "Activo",
            });

            this.$emit("addRowLot", this.lots);
        },

        close() {
            this.$emit("update:showDialog", false);
            this.$emit("addRowLot", this.lots);
        },
        clickCancel(index) {
            this.lots.splice(index, 1);
            // item.deleted = true
            this.$emit("addRowLot", this.lots);
        },

        async clickCancelSubmit() {
            this.$emit("addRowLot", []);
            await this.$emit("update:showDialog", false);
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>
