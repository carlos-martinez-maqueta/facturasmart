<template>
    <div v-loading="loading">
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active">
                    <span>Secciones / {{ survey.title }} </span>
                </li>
            </ol>
            <div class="right-wrapper pull-right">
                <a
                    href="#"
                    @click.prevent="clickCreate()"
                    class="btn btn-custom btn-sm mt-2 mr-2"
                    ><i class="fa fa-plus-circle"></i> Nuevo</a
                >
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <template v-if="sections.length > 0">
                    <el-collapse
                        v-model="activeName"
                        accordion
                        @change="getQuestions"
                    >
                        <el-collapse-item
                            :title="section.title"
                            v-for="(section, index) in sections"
                            :name="section.id"
                            :key="index"
                        >
                            <div
                                class="table-responsive"
                                v-loading="loadingQuestions"
                            >
                                <template v-if="questions.length > 0">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Pregunta</th>
                                                <th>Tipo de respuesta</th>
                                                <th>Opción personalizada</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    question, index
                                                ) in questions"
                                                :key="index"
                                            >
                                                <td>{{ index + 1 }}</td>
                                                <td>
                                                    {{ question.question_text }}
                                                </td>
                                                <td>
                                                    {{ question.question_type }}
                                                </td>
                                                <td>
                                                    {{
                                                        question.allow_custom_option
                                                            ? "Sí"
                                                            : "No"
                                                    }}
                                                </td>
                                                <td>
                                                    <el-button
                                                        type="primary"
                                                        icon="el-icon-edit"
                                                        size="mini"
                                                        @click="
                                                            clickCreateQuestion(
                                                                section.id,
                                                                question.id
                                                            )
                                                        "
                                                    ></el-button>
                                                    <el-button
                                                        type="danger"
                                                        icon="el-icon-delete"
                                                        size="mini"
                                                        @click="
                                                            clickRemove(
                                                                question.id
                                                            )
                                                        "
                                                    ></el-button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5">
                                                    <a
                                                        href="#"
                                                        @click.prevent="
                                                            clickCreateQuestion(
                                                                section.id
                                                            )
                                                        "
                                                        class="btn btn-sm btn-primary"
                                                        ><i
                                                            class="fa fa-plus"
                                                        ></i>
                                                        Agregar pregunta</a
                                                    >
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </template>
                                <template v-else>
                                    <div class="alert alert-danger">
                                        No hay preguntas registradas

                                        <a
                                            href="#"
                                            @click.prevent="
                                                clickCreateQuestion(section.id)
                                            "
                                            class="btn btn-sm btn-primary"
                                            ><i class="fa fa-plus"></i> Agregar
                                            pregunta</a
                                        >
                                    </div>
                                </template>
                            </div>
                        </el-collapse-item>
                    </el-collapse>
                </template>
                <template v-else>
                    <div class="alert alert-info">
                        No hay secciones registradas
                    </div>
                </template>
            </div>
        </div>
        <form-section
            :showDialog.sync="showDialog"
            :resource="resource"
            :recordId="recordId"
            @reloadData="getRecords"
            :survey="survey"
        ></form-section>
        <question-form
            :showDialog.sync="showDialogQuestion"
            :recordId="recordQuestionId"
            :recordSectionId="recordSectionId"
            @reloadData="getQuestions"
            :questionId.sync="activeName"
        ></question-form>
    </div>
</template>

<script>
import FormSection from "./form.vue";
import QuestionForm from "./question_form.vue";
export default {
    props: ["configuration", "typeUser", "survey"],
    components: {
        FormSection,
        QuestionForm,
    },
    data() {
        return {
            recordSectionId: null,
            loading: false,
            title: null,
            resource: "survey/section",
            recordId: null,
            recordQuestionId: null,
            showDialogQuestion: false,
            showDialog: false,
            sections: [],
            activeName: null,
            questions: [],
            loadingQuestions: false,
        };
    },
    created() {
        console.log(this.survey);
    },
    mounted() {
        this.getRecords();
    },
    methods: {
        clickRemove(id) {
            this.$confirm("¿Está seguro de eliminar el registro?", "Eliminar", {
                confirmButtonText: "Eliminar",
                cancelButtonText: "Cancelar",
                type: "warning",
            })
                .then(() => {
                    this.$http
                        .delete(`/survey/question/${id}`)
                        .then((response) => {
                            this.getQuestions();
                        });
                })
                .catch(() => {
                    this.$message({
                        type: "info",
                        message: "Eliminación cancelada",
                    });
                });
        },
        clickCreateQuestion(id = null, questionId = null) {
            this.recordSectionId = id;
            this.recordQuestionId = questionId;
            this.showDialogQuestion = true;
        },
        getQuestions() {
            if (this.activeName) {
                this.loadingQuestions = true;
                this.$http
                    .get(`/survey/question/records/${this.activeName}`)
                    .then((response) => {
                        this.questions = response.data.data;
                    })
                    .finally(() => {
                        this.loadingQuestions = false;
                    });
            }
        },
        clickCreate(id = null) {
            this.recordId = id;
            this.showDialog = true;
        },
        getRecords() {
            this.loading = true;
            this.$http
                .get(`/${this.resource}/records/${this.survey.uuid}`)
                .then((response) => {
                    console.log("🚀 ~ .then ~ response:", response);
                    this.sections = response.data.data;
                    this.loading = false;
                });
        },
    },
};
</script>

<style></style>
