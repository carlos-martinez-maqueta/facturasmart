<template>
    <el-dialog
        :title="title"
        :visible="showDialog"
        width="50%"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        v-loading="loading"
        @open="open"
        @close="close"
    >
        <div class="row" v-if="recordId">
            <span
                class="alert alert-danger text-center text-uppercase text-danger"
            >
                <i class="fas fa-exclamation-triangle"></i>
                <strong>¡Atención!</strong> Al editar una pregunta, se perderán
                las respuestas de los usuarios.
            </span>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="title">Pregunta</label>
                    <el-input
                        v-model="form.question_text"
                        placeholder="Título"
                        clearable
                    >
                    </el-input>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="description">Tipo de respuesta</label>
                    <el-select v-model="form.question_type">
                        <el-option
                            v-for="item in options"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value"
                        >
                        </el-option>
                    </el-select>
                </div>
                <template
                    v-if="
                        form.question_type == 'single_choice' ||
                        form.question_type == 'multiple_choice'
                    "
                >
                    <div class="form-group">
                        <label for="description">Opciones</label>
                        <el-input
                            v-model="option"
                            placeholder="Opciones"
                            clearable
                        >
                            <el-button
                                slot="append"
                                icon="el-icon-plus"
                                @keyup.enter.native="
                                    form.options.push(option);
                                    option = '';
                                "
                                @click="
                                    form.options.push(option);
                                    option = '';
                                "
                            ></el-button>
                        </el-input>
                    </div>
                    <div class="form-group">
                        <ul>
                            <li
                                v-for="(item, index) in form.options"
                                :key="index"
                            >
                                {{ item }}
                                <i
                                    role="button"
                                    class="fas fa-trash-alt text-danger"
                                    @click="form.options.splice(index, 1)"
                                ></i>
                            </li>
                        </ul>
                    </div>
                </template>
                <template v-if="form.question_type == 'interval'">
                    <div class="form-group">
                        <small class="text-muted">
                            Ejemplo: 1 año 6 meses 8 días
                        </small>
                    </div>
                </template>
                <div class="form-group" v-if="isOpcionalQuestion">
                    <label for="description"
                        >¿Permite respuesta personalizada?</label
                    >
                    <el-switch
                        v-model="form.allow_custom_option"
                        active-color="#13ce66"
                        inactive-color="#ff4949"
                    >
                    </el-switch>
                </div>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="save">Guardar</el-button>
        </span>
    </el-dialog>
</template>

<script>
export default {
    props: ["showDialog", "recordId", "questionId", "recordSectionId"],
    data() {
        return {
            form: {},
            option: "",
            resource: "survey/question",
            loading: false,
            title: "Nueva Encuesta",
            //'date','text','number','multiple_choice','boolean'
            options: [
                { value: "text", label: "Texto" },
                { value: "number", label: "Número" },
                { value: "date", label: "Fecha" },
                { value: "single_choice", label: "Selección simple" },
                {
                    value: "interval",
                    label: "Intervalo",
                    description: "Ejemplo: 1 año 6 meses 8 días",
                },
                { value: "multiple_choice", label: "Selección múltiple" },
                { value: "boolean", label: "Sí/No" },
            ],
        };
    },
    computed: {
        isOpcionalQuestion() {
            return (
                this.form.question_type == "single_choice" ||
                this.form.question_type == "multiple_choice"
            );
        },
    },
    methods: {
        initForm() {
            this.form = {
                question_text: "",
                question_type: null,
                options: [],
                allow_custom_option: false,
                survey_section_id: this.recordSectionId,
            };
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        getRecord() {
            this.$http
                .get(`/${this.resource}/record/${this.recordId}`)
                .then((response) => {
                    this.form = response.data;
                });
        },
        open() {
            this.initForm();
            console.log("🚀 ~ open ~ this.recordId:", this.recordId);
            if (this.recordId) {
                this.title = "Editar sección";
                this.getRecord();
            }
            console.log(this.form);
            // this.$emit("update:showDialog", true);
        },
        save() {
            if (!this.form.question_text) {
                this.$message.error("La pregunta es requerida");
                return;
            }
            if (!this.form.question_type) {
                this.$message.error("El tipo de respuesta es requerido");
                return;
            }
            if (
                (this.form.question_type == "multiple_choice" ||
                    this.form.question_type == "single_choice") &&
                (this.form.options.length == 0 || this.form.options.length == 1)
            ) {
                this.$message.error("Debe agregar al menos dos opciones");
                return;
            }
            this.loading = true;
            this.$http
                .post(`/${this.resource}`, this.form)
                .then((response) => {
                    this.$message.success("Sección guardada correctamente");

                    this.initForm();
                    this.$emit("reloadData");
                    if (this.recordId) {
                        this.$emit("update:showDialog", false);
                    }
                })
                .catch((error) => {
                    console.log("🚀 ~ save ~ error:", error);
                    this.$message.error("Error al guardar la sección");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
