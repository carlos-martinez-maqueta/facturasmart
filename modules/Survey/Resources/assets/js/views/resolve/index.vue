<template>
    <div class="d-flex flex-column">
        <br />
        <div class="col text-end p-2" v-if="has_location">
            <el-button type="primary" size="small" @click="viewSections">
                <i class="el-icon-s-grid"></i>
            </el-button>
        </div>
        <div class="col-md-12">
            <template v-if="!has_location">
                <div class="card" style="min-height: 500px">
                    <div class="card-header">
                        <h1 class="card-title text-center">
                            Lugar de nacimiento
                        </h1>
                    </div>
                    <div class="card-body">
                        <ubigeo
                            :uuid="uuid"
                            :locations="locations"
                            :user_email="user_email"
                        ></ubigeo>
                    </div>
                </div>
            </template>
            <template v-else>
                <el-carousel
                    ref="carousel"
                    class="carousel-smart"
                    :interval="0"
                    :loop="false"
                    indicator-position="none"
                    @change="onChange"
                    arrow="always"
                >
                    <el-carousel-item
                        v-for="(section, idx) in sections"
                        :key="idx"
                    >
                        <div class="card">
                            <div class="card-header">
                                <h1 class="card-title text-center">
                                    {{ section.title }}
                                </h1>
                            </div>
                            <div
                                class="row d-flex justify-content-center card-body p-1"
                            >
                                <question
                                    v-for="(question, idx) in section.questions"
                                    :key="idx"
                                    :question="question"
                                    :uuid="uuid"
                                    :sectionId="section.id"
                                    :answer="answers[question.id] || {}"
                                ></question>

                                <el-divider></el-divider>
                            </div>
                        </div>
                    </el-carousel-item>
                </el-carousel>
                <div
                    class="custom-arrow left-arrow"
                    @click="goBack"
                    v-if="currentSection != 0"
                >
                    <!-- ⬅ -->
                    <i class="el-icon-d-arrow-left"></i>
                </div>
                <div
                    class="custom-arrow right-arrow"
                    @click="goForward"
                    v-if="currentSection != sections.length - 1"
                >
                    <!-- ➡ -->
                    <i class="el-icon-d-arrow-right"></i>
                </div>
                <div
                    class="custom-arrow right-arrow"
                    @click="checkAllAnsweredAllSections"
                    v-else
                >
                    <i class="el-icon-position"></i>
                </div>
            </template>
        </div>
        <modal-sections
            :showDialog.sync="showDialogSections"
            @goto="setActive"
            :uuid="uuid"
        ></modal-sections>
    </div>
</template>
<style>
@media only screen and (max-width: 767px) {
    #main-wrapper {
        padding-top: 0px !important;
    }
}
.carousel-smart .el-carousel__container .el-carousel__item,
.el-carousel__mask {
    /* position: static; */
    overflow-y: auto;
}
.carousel-smart .el-carousel__container {
    overflow-y: auto;
    /* min-height: 300px; */
    height: 95vh;
    position: relative;
    overflow-x: hidden;
}

.el-carousel__arrow {
    display: none;
}

.el-carousel-item {
    position: relative; /* Asegura que los items sean relativos */
    /* Otros estilos para los items */
}

/* Custom arrows styling */
.custom-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background-color: rgba(0, 0, 0, 0.5); /* Semitransparente */
    border-radius: 50%; /* Hace que sea un círculo */
    display: flex;
    align-items: center;
    justify-content: center;
    color: white; /* Color del icono */
    font-size: 20px; /* Tamaño del icono */
    cursor: pointer;
    z-index: 1000;
}

.left-arrow {
    left: 10px;
}

.right-arrow {
    right: 10px;
}
</style>
<script>
import Question from "./question.vue";
import Ubigeo from "./partials/ubigeo";
import ModalSections from "./partials/modal_sections.vue";
export default {
    props: ["configuration", "uuid", "user_name", "user_email", "has_location"],
    components: {
        Question,
        Ubigeo,
        ModalSections,
    },
    data() {
        return {
            loadingSection: false,
            showDialogSections: false,
            currentSection: 0,
            recordId: null,
            loading: false,
            resource: "resolve",
            showDialog: false,
            showDialogPassword: false,
            sections: [],
            departments: [],
            activeName: "0",
            provinces: [],
            districts: [],
            all_departments: [],
            all_provinces: [],
            all_districts: [],
            locations: [],
            answers: {},
        };
    },
    created() {},
    async mounted() {
        if (!this.has_location) {
            this.getTables();
        }
        this.getSections();
        // console.log(this.has_location);

        // this.getAnswers();
        // setTimeout(() => {
        //     this.$refs.carousel.setActiveItem(1);
        // }, 1000);
    },
    methods: {
        viewSections() {
            this.showDialogSections = true;
        },
        getAnswers() {
            console.log("🚀 ~ getAnswers ~ this.sections:", this.sections);
            let sectionId = this.sections[this.currentSection].id;
            this.$http
                .get(`/resolve/answers/${this.uuid}/${sectionId}`)
                .then((response) => {
                    this.answers = response.data.reduce((acc, answer) => {
                        acc[answer.question_id] = answer;
                        return acc;
                    }, {});
                })
                .catch((error) => {
                    this.$message.error("Error al obtener las respuestas");
                });
        },
        async checkAllAnsweredAllSections() {
            this.$confirm("¿Desea finalizar la encuesta?", "Finalizar", {
                confirmButtonText: "Sí",
                cancelButtonText: "No",
                type: "warning",
            })
                .then(async () => {
                    const loading = this.$loading({
                        lock: true,
                        text: "Verificando respuestas obligatorias...",
                        spinner: "el-icon-loading",
                        background: "rgba(0, 0, 0, 0.7)",
                    });
                    try {
                        const response = await this.$http.post(
                            `/resolve/check-all/${this.uuid}`
                        );

                        if (response.status == 200) {
                            let { success } = response.data;
                            if (success) {
                                window.location.href = `/survey-resolve/resolve/${this.uuid}`;
                            }
                        }
                    } catch (error) {
                        console.log(error);

                        return false;
                    } finally {
                        loading.close();
                        this.loadingSection = false;
                    }
                })
                .catch(() => {
                    this.$message({
                        type: "info",
                        message: "Operación cancelada",
                    });
                });
        },
        async checkAllAnswered() {
            let section = this.sections[this.currentSection];
            let questions = section.questions;
            let requiredQuestions = questions
                .filter((question) => question.is_required == 1)
                .map((question) => question.id);

            if (requiredQuestions.length == 0) {
                return;
            }
            const loading = this.$loading({
                lock: true,
                text: "Verificando respuestas obligatorias...",
                spinner: "el-icon-loading",
                background: "rgba(0, 0, 0, 0.7)",
            });
            try {
                const response = await this.$http.post(
                    `/resolve/check/${this.uuid}`,
                    {
                        questions_ids: requiredQuestions,
                    }
                );

                if (response.status == 200) {
                    let { success } = response.data;
                    return success;
                }

                return false;
            } catch (error) {
                console.log(error);

                return false;
            } finally {
                loading.close();
                this.loadingSection = false;
            }
        },
        setActive(index) {
            this.$refs.carousel.setActiveItem(index);
        },
        onChange(act, prev) {
            this.currentSection = act;
        },
        setLocations() {},
        getTables() {
            this.$http
                .get(`/resolve/tables/${this.uuid}`)
                .then((response) => {
                    this.locations = response.data.locations;
                })
                .catch((error) => {
                    this.$message.error("Error al obtener las tablas");
                });
        },
        async goForward() {
            // let pass = await this.checkAllAnswered();
            // if (!pass) {
            //     this.$message.error(
            //         "Por favor, responda todas las preguntas obligatorias"
            //     );
            //     return;
            // }
            this.$refs.carousel.next();
        },
        goBack() {
            if (this.currentSection == 0) {
                return;
            }
            this.$refs.carousel.prev();
        },
        async getSections() {
            this.loading = true;
            this.$http
                .get(`/resolve/sections/${this.uuid}`)
                .then((response) => {
                    this.sections = response.data;
                    this.loading = false;
                    this.getAnswers();
                })
                .catch((error) => {
                    this.loading = false;
                    this.$message.error("Error al obtener las secciones");
                });
        },
    },
};
</script>
