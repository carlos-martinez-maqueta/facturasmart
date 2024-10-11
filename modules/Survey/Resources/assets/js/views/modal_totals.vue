<template>
    <el-dialog
        title="Resultados de la encuesta"
        :visible="showDialog"
        width="80%"
        @close="close"
        @open="open"
        append-to-body
    >
        <div class="row">
            <div class="col-6">
                <button class="btn btn-success" @click="exportToExcel">
                    <i class="fas fa-file-excel"></i>
                    Excel
                </button>
                    <button class="btn btn-success" @click="exportToExcelAll">
                    <i class="fas fa-file-excel"></i>
                    Excel Sabana
                </button>
                <button class="btn btn-danger" @click="exportToPDF">
                    <i class="fas fa-file-pdf"></i>
                    PDF
                </button>
            </div>
        </div>
        <div class="row">
            <div
                v-for="section in records"
                :key="section.section"
                class="section"
            >
                <h3>{{ section.section }}</h3>
                <div class="questions-grid">
                    <div
                        v-for="question in section.questions"
                        :key="question.question"
                        class="question"
                    >
                        <h4>{{ question.question }}</h4>
                        <p>Total de respuestas: {{ question.total }}</p>
                        <ul>
                            <li
                                v-for="answer in question.answers"
                                :key="answer.answer_text"
                            >
                                {{ answer.answer_text }} -
                                {{ answer.count }} ({{
                                    answer.percentage.toFixed(2)
                                }}%)
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </el-dialog>
</template>
<style scoped>
.section {
    margin-bottom: 20px;
}

.questions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.question {
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 5px;
    background-color: #f9f9f9;
}
</style>
<script>
export default {
    props: ["id", "showDialog"],
    data() {
        return {
            records: [],
        };
    },
    methods: {
        exportToExcelAll() {
            
            window.open(`/survey/export-excel-all/${this.id}`, "_blank");
        },
        exportToExcel() {
            window.open(`/survey/export-excel/${this.id}`, "_blank");
        },
        exportToPDF() {
            window.open(`/survey/export-pdf/${this.id}`, "_blank");
        },
        getTotals() {
            this.$http.get(`/survey/get-totals/${this.id}`).then((response) => {
                // console.log("🚀 ~ .then ~ response:", response);
                this.records = response.data.sections;
            });
        },
        open() {
            this.getTotals();
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>

<style></style>
