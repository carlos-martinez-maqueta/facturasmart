<template>
    <div>
        <template v-if="record">
            <div class="respondet-container">
                <el-card class="respondet-card">
                    <div class="card-header">
                        <span class="respondet-name">{{ respondet.name }}</span>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>Documento:</strong> {{ respondet.number }}
                        </p>
                        <p><strong>Email:</strong> {{ respondet.email }}</p>
                        <p><strong>Celular:</strong> {{ respondet.phone }}</p>
                        <p>
                            <strong>Dirección:</strong> {{ respondet.address }}
                            {{ respondet.location }}
                        </p>
                    </div>
                </el-card>
            </div>
            <div class="answers-container">
                <el-row
                    :gutter="20"
                    v-for="(answers, section) in answers"
                    :key="section"
                >
                    <el-col :span="24">
                        <h2 class="section-title">{{ section }}</h2>
                    </el-col>
                    <el-col
                        v-for="(item, index) in answers"
                        :key="index"
                        :span="24"
                        :md="12"
                        :lg="8"
                    >
                        <el-card class="answer-card">
                            <div class="card-header">
                                <span class="question">{{
                                    item.description
                                }}</span>
                            </div>
                            <div class="card-body">
                                <span class="answer">{{ item.answer }}</span>
                            </div>
                        </el-card>
                    </el-col>
                </el-row>
            </div>
        </template>
        <template v-else>
            <div class="alert alert-danger">Usuario/Encuesta no encontrada</div>
        </template>
    </div>
</template>

<style scoped>
.respondet-container {
    padding: 20px;
    margin-bottom: 20px;
}

.respondet-card {
    border-radius: 10px;
    background: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.respondet-card:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.card-header {
    font-weight: bold;
    font-size: 1.2em;
    color: #333;
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.card-body {
    padding: 10px;
    font-size: 1em;
    color: #666;
}

.answers-container {
    padding: 20px;
}

.section-title {
    font-size: 1.5em;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}

.answer-card {
    margin-bottom: 20px;
    border-radius: 10px;
    background: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.answer-card:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.question {
    display: block;
    margin-bottom: 5px;
}

.answer {
    display: block;
    color: #007bff;
}
</style>
<script>
export default {
    props: ["record"],
    data() {
        return {
            answers: [],
            respondet: {},
        };
    },
    mounted() {
        if (this.record) {
            this.getAnswer();
            this.getRespondet();
        }
    },
    methods: {
        getRespondet() {
            this.$http
                .get(`/survey/get-respondet/${this.record.id}`)
                .then((response) => {
                    console.log("🚀 ~ .then ~ response:", response);
                    this.respondet = response.data;
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        getAnswer() {
            this.$http
                .get(`/survey/get-answers/${this.record.id}`)
                .then((response) => {
                    console.log("🚀 ~ .then ~ response:", response);
                    const groupedAnswers = response.data.reduce(
                        (acc, answer) => {
                            if (!acc[answer.section]) {
                                acc[answer.section] = [];
                            }
                            acc[answer.section].push(answer);
                            return acc;
                        },
                        {}
                    );
                    this.answers = groupedAnswers;
                })
                .catch((error) => {
                    console.log(error);
                });
        },
    },
};
</script>

<style></style>
