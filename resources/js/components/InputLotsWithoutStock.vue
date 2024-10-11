<template>
    <el-dialog
        :title="titleDialog"
        width="70%"
        :visible="showDialog"
        @open="create"
        @opened="opened"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        append-to-body
        :show-close="false"
        v-loading="loadingVerify"
        loading-text="Verificando series..."
    >
        <div class="form-body">
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
                <div class="col-md-4 d-flex align-items-end">
                    <el-button
                        type="primary"
                        @click.prevent="addRange"
                        class="mt-4"
                    >
                        Ingresar
                    </el-button>
                </div>
            </div>
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
            <div class="row mt-2" v-loading="loading">
                <div class="col-md-12">
                    <el-button type="primary" @click.prevent="clickAddLot()"
                        >Agregar</el-button
                    >
                    <el-button type="success" @click.prevent="clickImport()"
                        >Importar</el-button
                    >
                </div>
                <div class="col-md-12 mt-2">
                    <data-tables
                        :data="lots"
                        :current-page.sync="currentPage"
                        :table-props="tableProps"
                        :pagination-props="{ pageSizes: [20] }"
                        style="width: 100%"
                    >
                        <el-table-column width="80" prop="index" label="#">
                            <template slot-scope="scope">
                                {{ scope.row.index + 1 }}
                            </template>
                        </el-table-column>

                        <el-table-column prop="series" label="Series">
                            <template slot-scope="scope">
                                <el-input
                                    :class="`${
                                        scope.row.isDuplicate
                                            ? 'is-invalid'
                                            : ''
                                    }`"
                                    @blur="
                                        duplicateSerie(
                                            scope.row.series,
                                            scope.row.index
                                        )
                                    "
                                    v-model="scope.row.series"
                                    :ref="`ref_series_${scope.row.index}`"
                                    @input="
                                        clearDuplicate(
                                            scope.row.index,
                                            scope.row.series
                                        )
                                    "
                                    @keyup.enter.native="
                                        keyupEnterSeries(
                                            scope.row.series,
                                            scope.row.index
                                        )
                                    "
                                ></el-input>
                            </template>
                        </el-table-column>

                        <el-table-column prop="state" label="Estado">
                            <template slot-scope="scope">
                                <el-select v-model="scope.row.state">
                                    <el-option
                                        v-for="(option, index) in states"
                                        :key="index"
                                        :value="option"
                                        :label="option"
                                    ></el-option>
                                </el-select>
                            </template>
                        </el-table-column>

                        <el-table-column prop="date" label="Fecha">
                            <template slot-scope="scope">
                                <el-date-picker
                                    v-model="scope.row.date"
                                    type="date"
                                    value-format="yyyy-MM-dd"
                                    :clearable="false"
                                ></el-date-picker>
                            </template>
                        </el-table-column>

                        <el-table-column
                            style="width: 10%"
                            width="80"
                            label="Acciones"
                        >
                            <template slot-scope="scope">
                                <button
                                    type="button"
                                    class="btn waves-effect waves-light btn-sm btn-danger"
                                    @click.prevent="
                                        clickCancel(scope.row.index)
                                    "
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                            </template>
                        </el-table-column>
                    </data-tables>
                </div>
            </div>

            <series-import :showDialog.sync="showImportDialog"></series-import>
        </div>

        <div class="form-actions text-end pt-2 mt-3">
            <el-button @click.prevent="clickCancelSubmit()">Cancelar</el-button>
            <el-button type="primary" @click="submit">Guardar</el-button>
        </div>
    </el-dialog>
</template>

<style>
.is-invalid .el-input__inner {
    border-color: #f5222d !important;
}
</style>
<script>
import { DataTables } from "vue-data-tables";
import SeriesImport from "@views/purchases/partials/import_series.vue";

export default {
    components: {
        DataTables,
        SeriesImport,
    },
    props: ["showDialog", "lots", "stock", "recordId", "item_id"],
    data() {
        return {
            barcode: false,
            start_range: "",
            end_range: "",
            hasDuplicate: false,
            loadingVerify: false,
            titleDialog: "Series",
            showImportDialog: false,
            loading: false,
            errors: {},
            form: {},
            states: ["Activo", "Inactivo", "Desactivado", "Voz", "M2m"],
            tableProps: {
                border: true,
            },
            currentPage: 1,
            per_page: 20,
        };
    },
    async created() {
        this.$eventHub.$on("responseImportSeries", (response) => {
            this.responseImportSeries(response);
        });
        this.barcode = localStorage.getItem("barcode") == "true" ? true : false;
    },
    computed: {},
    methods: {
        getIndexLots(){
            return this.lots.filter(l=>l.series).length;
        },
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
        addRange() {
            //verificar si el rango es valido
            if (this.start_range == "" || this.end_range == "") {
                return this.$message.error("Ingrese el rango de series");
            }

            if (this.start_range > this.end_range) {
                return this.$message.error(
                    "El inicio del rango no puede ser mayor al final del rango"
                );
            }
            let start = BigInt(this.start_range);
            let end = BigInt(this.end_range);
            let quantity = end - start + BigInt(1);
            quantity = parseInt(quantity);
            if (quantity > this.stock) {
                return this.$message.error(
                    "La cantidad de series es mayor al stock o cantidad"
                );
            }
            let localLots = [];

            for (let i = 0; i < quantity; i++) {
                localLots.push({
                    id: null,
                    item_id: null,
                    series: (start + BigInt(i)).toString(),
                    date: moment().format("YYYY-MM-DD"),
                    state: "Activo",
                    index: this.getIndexLots() + i,
                });
            }
            let emitLots = [...this.lots];
            for (let i = 0; i < localLots.length; i++) {
                let index = emitLots.findIndex((lot) => !lot.series);
                if (index !== -1) {
                    emitLots[index] = localLots[i];
                } else {
                    break;
                }
            }
            let page = emitLots.filter(f=>f.series).length / this.per_page;
            page = Math.ceil(page);
            this.currentPage = page;
            this.$emit("update:lots", emitLots);
            this.opened();
            this.start_range = "";
            this.end_range = "";

            // this.paginatedLots = localLots;
            // this.currentPage = 1;
            console.log("🚀 ~ addRange ~ lots:", this.lots);
        },
        async responseImportSeries(response) {
            // console.log(response.data.news_rows)
            let lots_import = response.data.news_rows;

            try {
                for (let index = 0; index < this.lots.length; index++) {
                    this.lots[index].series = lots_import[index].series;
                    this.lots[index].date = lots_import[index].date;
                    this.lots[index].state = lots_import[index].state;
                }
            } catch (error) {}

            if (response.data.news_rows.length != this.lots.length) {
                this.$notify({
                    title: "",
                    message:
                        "La cantidad de registros del archivo importado, es diferente a la cantidad ingresada",
                    type: "error",
                    duration: 4000,
                });
            }

            this.$emit("addRowLot", this.lots);
        },
        clickImport() {
            this.showImportDialog = true;
        },
        getMaxItems(index) {
            if (this.currentPage > 1) {
                index = index - this.per_page;
            }

            return this.per_page * (this.currentPage - 1) + index + 1;
        },
        async keyupEnterSeries(series, index) {
            // console.log(series, index, this.getIndex())
            // console.log(this.$refs)

            if (index == this.getIndex() - 1) {
                return;
            }

            try {
                await this.changeFocus(index);
            } catch (e) {
                await this.nextPage();

                await this.$nextTick(() => {
                    this.changeFocus(index);
                });
            }
        },
        changeFocus(index) {
            let lastIndex = index +1 ;
            if(lastIndex % 20 == 0){
                
                this.currentPage++;
                setTimeout(() => {
                    this.$refs[`ref_series_${index + 1}`].$el
                    .getElementsByTagName("input")[0]
                    .focus();
                }, 300);
            }else{
                this.$refs[`ref_series_${index + 1}`].$el
                .getElementsByTagName("input")[0]
                .focus();
            }
            // console.log("🚀 ~ changeFocus ~ `ref_series_${index + 1}`:", `ref_series_${index + 1}`)
            
        },
        nextPage() {
            this.currentPage++;
        },
        async duplicateSerie(data, index) {
            // console.log(data, index)
            if (data) {
                let duplicates = await _.filter(this.lots, { series: data });
                if (duplicates.length > 1) {
                    this.$message.error("Ingresó una serie duplicada");
                    this.lots[index].series = "";
                }
            }
        },
        create() {
            this.loading = true;
        },
        opened() {
            this.hasDuplicate = false;
            if (!this.recordId) {
                if (this.lots.length == 0) {
                    this.addMoreLots(this.stock);
                } else {
                    let quantity = this.stock - this.lots.length;
                    if (quantity > 0) {
                        this.addMoreLots(quantity);
                    }
                    // else{
                    //     this.deleteMoreLots(quantity)
                    // }
                }
            }

            this.loading = false;
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
        clearDuplicate(index, series) {
            let lot = this.lots[index];
            if (lot.isDuplicate) {
                let lot_find = this.lots.find(
                    (lot_find) => lot_find.series == series
                );
                if (lot_find) {
                    lot_find.isDuplicate = false;
                }
                let lots = this.lots;
                this.$emit("addRowLot", lots);
            } else {
                if (this.barcode) {
                    setTimeout(() => {
                        this.changeFocus(index);
                    }, 300);
                }
            }
        },
        async check() {
            let pass = true;
            try {
                this.loadingVerify = true;
                const response = await this.$http.post("/item-lots/check", {
                    lots: this.lots,
                    item_id: this.item_id,
                });
                const { data } = response;
                if (data.length > 0) {
                    this.$message.error(
                        "Se encontraron series duplicadas, por favor verifique"
                    );
                    pass = false;
                    let lots = this.lots.map((lot) => {
                        let lot_find = data.find(
                            (lot_find) => lot_find == lot.series
                        );
                        if (lot_find) {
                            lot.isDuplicate = true;
                        }
                        return lot;
                    });
                    this.hasDuplicate = true;
                    this.$emit("addRowLot", lots);
                }
            } catch (e) {
                console.log(e);
            } finally {
                this.loadingVerify = false;
            }
            return pass;
        },
        async submit() {
            let pass = await this.check();
            if (!pass) return;
            let val_lots = await this.validateLots();
            if (!val_lots.success) return this.$message.error(val_lots.message);

            await this.$emit("addRowLot", this.lots);
            await this.$emit("update:showDialog", false);
        },
        async clickAddLot() {
            if (!this.recordId) {
                if (this.lots.length >= this.stock)
                    return this.$message.error(
                        "La cantidad de registros es superior al stock o cantidad"
                    );
            }

            // let _index = index ? index :  this.getIndex()

            await this.lots.push({
                id: null,
                item_id: null,
                series: null,
                isDuplicate: false,
                date: moment().format("YYYY-MM-DD"),
                state: "Activo",
                index: this.getIndex(),
            });

            this.$emit("addRowLot", this.lots);
        },
        getIndex() {
            return this.lots.length;
        },
        close() {
            this.$emit("update:showDialog", false);
            this.$emit("addRowLot", this.lots);
        },
        async clickCancel(index) {
            await this.lots.splice(index, 1);
            await this.renewIndexes();
            await this.$emit("addRowLot", this.lots);
        },
        async renewIndexes() {
            await this.lots.forEach((row, index) => {
                row.index = index;
            });
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
