<template>
    <el-dialog
        :visible="showDialog"
        title="Detalle de Nota Masiva"
        @close="close"
        @open="open"
        append-to-body
        :close-on-click-modal="false"
        :close-on-press-escape="false"
    >
        <div class="row mt-2">
            <div class="col-12">
                <label for="document_type_id"
                    >Tipo de Documento
                    <el-tooltip
                        content="Las series se colocarán según el tipo de documento"
                        placement="top"
                    >
                        <i class="el-icon-info"></i>
                    </el-tooltip>
                </label>
                <el-select
                    v-model="formDocument.document_type_id"
                    @change="setSeries"
                    filterable
                    disabled
                    placeholder="Seleccione"
                >
                    <el-option
                        v-for="documentType in documentTypesNote"
                        :key="documentType.id"
                        :value="documentType.id"
                        :label="documentType.description"
                    ></el-option>
                </el-select>
            </div>
            <div class="col-12">
                <label for="document_type_id"
                    >Tipo de nota de
                    {{
                        formDocument.document_type_id === "07"
                            ? "Crédito"
                            : "Débito"
                    }}
                </label>
                <el-select
                    disabled
                    v-model="formDocument.note_credit_or_debit_type_id"
                    filterable
                    placeholder="Seleccione"
                >
                    <el-option
                        v-for="noteType in formDocument.document_type_id ===
                        '07'
                            ? noteCreditTypes
                            : noteDebitTypes"
                        :key="noteType.id"
                        :value="noteType.id"
                        :label="noteType.description"
                    ></el-option>
                </el-select>
            </div>
            <div class="col-12">
                <label for="note_description">Descripción de la nota</label>
                <el-input
                    v-model="formDocument.note_description"
                    placeholder="Descripción de la nota"
                ></el-input>
            </div>
            <!-- <div class="col-md-6 col-lg-6 col-12">
                <label for="series_id">Serie</label>
                <el-select
                    v-model="form.series_id"
                    filterable
                    clearable
                    placeholder="Seleccione"
                >
                    <el-option
                        v-for="serie in series"
                        :key="serie.id"
                        :value="serie.id"
                        :label="serie.number"
                    ></el-option>
                </el-select>
            </div> -->
        </div>
        <div class="row mt-2">
            <el-progress
                :text-inside="true"
                :stroke-width="26"
                :percentage="percentage.toFixed(2)"
            ></el-progress>
        </div>

        <span slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button
                type="primary"
                @click="emit"
                :disabled="sending"
                v-loading="sending"
                >Emitir notas de crédito</el-button
            >
        </span>
    </el-dialog>
</template>
<script>
import { exchangeRate } from "../../../../mixins/functions";
import moment from "moment";
export default {
    mixins: [exchangeRate],
    props: ["showDialog", "documents"],
    data() {
        return {
            exchange_rate_sale: 1,
            percentage: 0,
            formDocument: {
                document_type_id: null,
                series_id: null,
                note_credit_or_debit_type_id: "01",
                note_description: "Anulación de la operación",
            },
            documentTypesNote: [],
            series: [],
            noteCreditTypes: [],
            noteDebitTypes: [],
            allSeries: [],
            sending: false,
        };
    },
    created() {
        this.getTables();
    },
    methods: {
        async getDocument(id) {
            try {
                const response = await this.$http.get(
                    `/documents/get-document/${id}`
                );
                return response.data;
            } catch (error) {
                console.error(error);
                return null;
            }
        },
        seriesId(number_full) {
            let firstChar = number_full.charAt(0);
            let series = this.allSeries.find(
                (serie) => serie.number.charAt(0) === firstChar
            );
            if (series) {
                return series.id;
            }
            return null;
        },
        async emit() {
            let countSuccess = 0;

            try {
                this.sending = true;
                let total = this.documents.length;
                let byEach = 100 / total;
                for (const documentSelected of this.documents) {
                    if (!this.sending) {
                        this.$message.warning("La operación fue cancelada");
                        break;
                    }
                    let { id, number_full } = documentSelected;
                    let document = await this.getDocument(id);
                    if (!document) {
                        this.$message.error(
                            `El documento ${number_full} no se pudo recuperar`
                        );
                        continue;
                    }
                    let series_id = this.seriesId(number_full);
                    if (!series_id) {
                        this.$message.error(
                            `No se encontró la serie de la nota para el documento ${number_full}`
                        );
                        continue;
                    }
                    let form = await this.initDocumentForm(document);
                    form.series_id = series_id;
                    form.exchange_rate_sale = this.exchange_rate_sale;

                    try {
                        const response = await this.$http.post(
                            `/documents`,
                            form
                        );
                        if (response.data.success) {
                            countSuccess++;
                            this.percentage += byEach;
                        } else {
                            this.$message.error(response.data.message);
                        }
                    } catch (error) {
                        this.$message.error(error);
                    }
                }
            } catch (error) {
                console.error(error);
            } finally {
                this.$emit("reloadRecords");
                this.sending = false;
                this.$message.success(
                    `Se emitieron ${countSuccess} notas de crédito`
                );
                this.close();
            }
        },
        async initDocumentForm(document) {
            this.errors = {};
            let form = {
                establishment_id: document.establishment_id,
                document_type_id: this.formDocument.document_type_id,
                series_id: null,
                number: "#",
                date_of_issue: moment().format("YYYY-MM-DD"),
                time_of_issue: moment().format("HH:mm:ss"),
                customer_id: document.customer_id,
                currency_type_id: document.currency_type_id,
                purchase_order: null,
                exchange_rate_sale: 0,
                total_prepayment: document.total_prepayment,
                total_charge: document.total_charge,
                // total_discount: this.document.total_discount,
                total_exportation: document.total_exportation,
                total_free: document.total_free,
                total_taxed: document.total_taxed,
                total_unaffected: document.total_unaffected,
                total_exonerated: document.total_exonerated,
                total_igv: document.total_igv,
                total_base_isc: document.total_base_isc,
                total_isc: document.total_isc,
                total_base_other_taxes: document.total_base_other_taxes,
                total_other_taxes: document.total_other_taxes,
                total_plastic_bag_taxes: document.total_plastic_bag_taxes,
                total_taxes: document.total_taxes,
                total_value: document.total_value,
                total: document.total,
                items: document.items,
                affected_document_id: document.id,
                note_credit_or_debit_type_id:
                    this.formDocument.note_credit_or_debit_type_id,
                note_description: this.formDocument.note_description,
                actions: {
                    format_pdf: "a4",
                },
                // operation_type_id: null,
                operation_type_id: document.invoice.operation_type_id, //se asigna el t. operacion del documento relacionado para filtrar en form item el tipo de afectacion
                hotel: {},
                charges: document.charges
                    ? Object.values(document.charges)
                    : null,
                payment_condition_id: null,
                fee: [],
            };

            await form.items.forEach((item) => {
                item.input_unit_price_value = item.unit_price;
                item.additional_information = null;
                item.IdLoteSelected = item.item.IdLoteSelected;
            });

            return form;

            // this.temp_total = this.form.total;
        },
        setSeries() {
            this.series = this.allSeries.filter(
                (serie) =>
                    serie.document_type_id ===
                    this.formDocument.document_type_id
            );
        },

        initForm() {
            this.formDocument = {
                document_type_id: null,
                note_credit_or_debit_type_id: "01",
                note_description: "Anulación de la operación",
            };
        },
        getTables() {
            this.$http.get("/documents/tables").then((response) => {
                this.documentTypesNote = response.data.document_types_note;
                this.allSeries = response.data.series.filter(
                    (serie) =>
                        serie.document_type_id === "07" ||
                        serie.document_type_id === "08"
                );

                this.noteCreditTypes = response.data.note_credit_types;
                this.noteDebitTypes = response.data.note_debit_types;
            });
        },
        open() {
            this.initForm();
            if (this.documentTypesNote.length > 0) {
                this.formDocument.document_type_id =
                    this.documentTypesNote[0].id;
                this.setSeries();
            }
            let date_of_issue = moment().format("YYYY-MM-DD");
            this.searchExchangeRateByDate(date_of_issue).then((response) => {
                this.exchange_rate_sale = response;
            });
            this.percentage = 0;
        },
        close() {
            if (this.sending) {
                this.sending = false;
            } else {
                this.$emit("update:showDialog", false);
            }
        },
    },
};
</script>
