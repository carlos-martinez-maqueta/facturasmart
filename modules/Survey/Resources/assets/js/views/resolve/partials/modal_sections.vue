<template>
    <el-dialog
        :visible="showDialog"
        width="90%"
        title="Secciones"
        @open="open"
        @close="close"
        :close-on-click-modal="false"
        append-to-body
    >
        <div class="sections-container">
            <el-row :gutter="20">
                <el-col
                    v-for="(section, idx) in sections"
                    :key="section.id"
                    :span="8"
                >
                    <div @click="handleCardClick(idx)">
                        <el-card
                            class="section-card"
                            :class="{ resolved: section.resolved }"
                        >
                            <div class="card-header">
                                <span>{{ section.title }}</span>
                                <span>{{ section.percentage }}</span>
                            </div>
                            <div class="card-body">
                                <p>
                                    {{
                                        section.description ||
                                        "Sin descripción disponible"
                                    }}
                                </p>
                            </div>
                        </el-card>
                    </div>
                </el-col>
            </el-row>
        </div>
    </el-dialog>
</template>
<style scoped>
.sections-container {
    padding: 20px;
}

.section-card {
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
    border-radius: 10px;
    background: #fff;
    color: black;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.section-card:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.section-card.resolved {
    border: 2px solid rgb(123, 171, 248);
    background:#2499e3;
    color: white;
}

.card-header {
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 1.2em;
}

.card-body {
    margin-top: 10px;
    font-size: 1em;
}
</style>
<script>
export default {
    props: ["uuid", "showDialog"],
    data() {
        return {
            sections: [],
        };
    },
    methods: {
        handleCardClick(idx) {
            console.log("🚀 ~ handleCardClick ~ idx:", idx);
            this.$emit("goto", idx);
            this.close();
        },
        getSections() {
            this.$http
                .get(`/resolve/sections-resolve/${this.uuid}`)
                .then((response) => {
                    console.log("🚀 ~ .then ~ response:", response);
                    this.sections = response.data;
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        open() {
            this.getSections();
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>

<style></style>
