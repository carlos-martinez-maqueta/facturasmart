<template>
    <div class="col-md-12 col-lg-6 col-sm-12 col-12 p-2">
        <div class="question-container text-center">
            <h5 class="question-title text-center">
                {{ question.question_text }}
                <template v-if="question.is_required === 1">
                    <span class="text-danger">*</span>
                </template>
            </h5>
            <el-input
                v-if="question.question_type === 'text'"
                v-model="form.answer"
                class="w-100 question-input"
                @input="inputSaveAnswer"
                placeholder="Respuesta"
            ></el-input>

            <el-input-number
                v-if="question.question_type === 'number'"
                v-model="form.answer"
                class="w-100 question-input"
                @change="inputSaveAnswer"
                :min="0"
            ></el-input-number>

            <el-date-picker
                v-if="question.question_type === 'date'"
                v-model="form.answer"
                class="w-100 question-input"
                @change="saveAnswer"
                type="date"
                placeholder="Seleccione fecha"
            ></el-date-picker>

            <el-select
                v-if="question.question_type === 'single_choice'"
                v-model="form.answer"
                @change="saveAnswer"
                :clearable="question.allow_custom_option == 1"
                class="w-100 question-input"
            >
                <el-option
                    v-for="(option, idx) in question.options"
                    :key="idx"
                    :label="option.option_text"
                    :value="option.id"
                ></el-option>
            </el-select>
            <el-select
                v-if="question.question_type === 'multiple_choice'"
                v-model="multiple_choice_answer"
                @change="saveAnswer"
                class="w-100 question-input"
                multiple
                collapse-tags
            >
                <el-option
                    v-for="(option, idx) in question.options"
                    :key="idx"
                    :label="option.option_text"
                    :value="option.id"
                ></el-option>
            </el-select>
        
            <div class="w-100 text-center">
                <el-switch
                    v-if="question.question_type === 'boolean'"
                    v-model="form.answer"
                    @change="saveAnswer"
                    active-text="Sí"
                    inactive-text="No"
                    class="custom-switch"
                >
                </el-switch>
            </div>

            <template v-if="question.question_type === 'interval'">
                <div class="d-flex justify-content-center">
                    <el-select
                        v-model="timeRange.years"
                        class="question-input"
                        @change="saveAnswer"
                        width="70px"
                        placeholder="Años"
                    >
                        <el-option
                            v-for="year in years"
                            :key="year"
                            :label="`${year} ${year !== 1 ? ' años' : ' año'}`"
                            :value="year"
                        ></el-option>
                    </el-select>
                    <el-select
                        v-model="timeRange.months"
                        class="question-input"
                        @change="saveAnswer"
                        width="70px"
                        placeholder="Meses"
                    >
                        <el-option
                            v-for="month in months"
                            :key="month"
                            :value="month"
                            :label="`${month} ${
                                month !== 1 ? ' meses' : ' mes'
                            }`"
                        ></el-option>
                    </el-select>
                    <el-select
                        v-model="timeRange.days"
                        class="question-input"
                        @change="saveAnswer"
                        style="width: 70px"
                        placeholder="Días"
                    >
                        <el-option
                            v-for="day in days"
                            :key="day"
                            :label="`${day} ${day !== 1 ? ' días' : ' día'} `"
                            :value="day"
                        ></el-option>
                    </el-select>
                </div>
            </template>
            <el-input
                style="margin-top: 3px"
                v-if="question.allow_custom_option"
                v-model="form.custom_option"
                class="w-100 question-input"
                @input="inputSaveAnswer"
                placeholder="Otros.."
            ></el-input>
        </div>
    </div>
</template>
<style>
.question-container .el-switch__label.is-active {
    color: #333 !important; /* Cambia el color del texto cuando el switch está activo */
}
.question-container {
    padding: 20px;
    background: #fff; /* Fondo blanco */
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    height: auto; /* Asegura que la altura sea automática */
    transition: transform 0.3s, box-shadow 0.3s;
}

.question-container:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.question-title {
    font-size: 1.2em;
    margin-bottom: 20px;
    color: #666; /* Color de texto más claro */
    font-weight: 400;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.question-input {
    margin-bottom: 15px;
    width: 100%;
}

.el-switch__label.el-switch__label--right.is-active {
    color: #333 !important; /* Color de texto más oscuro */
}

.el-input input,
.el-input-number input,
.el-date-picker input,
.el-select input,
.el-radio-group input,
.el-switch input,
.el-slider input {
    border-radius: 5px;
    border: 1px solid #ccc; /* Color de borde más claro */
    padding: 10px;
    transition: border-color 0.3s, box-shadow 0.3s;
    background-color: #f9f9f9; /* Fondo más claro */
}

.el-input input:focus,
.el-input-number input:focus,
.el-date-picker input:focus,
.el-select input:focus,
.el-radio-group input:focus,
.el-switch input:focus,
.el-slider input:focus {
    border-color: #999; /* Color de borde más vibrante */
    box-shadow: 0 0 5px rgba(153, 153, 153, 0.5); /* Sombra más vibrante */
    background-color: #fff; /* Fondo más claro al enfocar */
}
</style>
<script>
export default {
    props: ["question", "uuid", "sectionId", "answer"],
    data() {
        return {
            recordId: null,
            loading: false,
            showDialog: false,
            showDialogPassword: false,
            multiple_choice_answer: [],
            sections: [],
            form: {
                answer: "",
            },
            answers: [],
            timer: null,
            timeRange: {
                years: 0,
                months: 0,
                days: 0,
            },
            years: Array.from({ length: 10 }, (_, i) => i), // 0 to 9 years
            months: Array.from({ length: 12 }, (_, i) => i), // 0 to 11 months
            days: Array.from({ length: 31 }, (_, i) => i),
        };
    },
    watch: {
        answer(newVal, _) {
            this.formatAnswer(newVal);
        },
    },
    methods: {
        formatAnswer(answer) {
            if (answer.answer_text === undefined && answer.custom_optional === undefined) return;
            
            let { question_type } = this.question;
            let { answer_text } = answer;

            if (question_type === "interval") {
                let { years, months, days } = JSON.parse(answer_text);
                this.timeRange = { years, months, days };
            } else if (question_type === "multiple_choice") {
                console.log(
                    "🚀 ~ formatAnswer ~ this.form.answer:",
                    this.form.answer
                );
                this.multiple_choice_answer = answer_text
                    .split("|")
                    .map(Number);
                console.log(
                    "🚀 ~ formatAnswer ~ this.multiple_choice_answer:",
                    this.multiple_choice_answer
                );
            } else if (question_type === "single_choice") {
                this.form.answer = parseInt(answer_text);
            } else if (question_type === "boolean") {
                this.form.answer = answer_text === "1";
            } else {
                this.form.answer = answer_text;
            }
        },
        validAndTransformAnswer() {
            let answer = this.form.answer;
            if (
                answer === undefined &&
                this.timeRange == undefined &&
                this.multiple_choice_answer == undefined
            )
                return;
            let { question_type } = this.question;
            if (question_type === "interval") {
                this.form.answer = JSON.stringify(this.timeRange);
            }
            if (question_type === "multiple_choice") {
                console.log(
                    "🚀 ~ validAndTransformAnswer ~ this.multiple_choice_answer:",
                    this.multiple_choice_answer
                );
                this.form.answer = this.multiple_choice_answer.join("|");
            }
        },
        saveAnswer() {
            this.validAndTransformAnswer();
            let questionId = this.question.id;
            let answer = this.form.answer;
            let customOption = this.form.custom_option;

            if (answer !== undefined && questionId !== undefined) {
                this.$http
                    .post(`/resolve/answer/${this.uuid}`, {
                        question_id: questionId,
                        answer: answer,
                        custom_option: customOption,
                    })
                    .then((response) => {
                        console.log(response);
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            }
        },
        inputSaveAnswer() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                this.saveAnswer();
            }, 600);
        },
        initForm() {
            this.form = {
                answer: "",
                survey_question_id: this.question.id,
                survey_section_id: this.sectionId,
                uuid: this.uuid,
            };
        },
    },
};
</script>

<style></style>
