<template>
    <el-dialog
        :visible="showDialog"
        @open="open"
        @close="close"
        :title="title"
        v-loading="loading"
        append-to-body
        width="30%"
    >
        <div class="row" v-if="currentRecord">
            <div
                class="col-md-8"
                v-for="(state, index) in states"
                :key="index"
                @click="changeState(state.id)"
            >
                <el-button
                    :class="`${
                        currentRecord.state_id == state.id
                            ? 'state_' + state.id
                            : ''
                    }`"
                >
                    {{ state.description }}
                </el-button>
            </div>
        </div>
    </el-dialog>
</template>

<script>
export default {
    props: ["showDialog", "currentRecord", "states"],
    data() {
        return {
            loading: false,
            title: "Cambiar estado",
        };
    },
    methods: {
        open() {},
        async changeState(stateId) {
            try {
                this.loading = true;
                await this.$confirm(
                    `¿Está seguro de cambiar el estado a ${this.states.find(
                        (state) => state.id == stateId
                    ).description.toUpperCase()}?`,
                    "Confirmar",
                    {
                        confirmButtonText: "Aceptar",
                        cancelButtonText: "Cancelar",
                        type: "warning",
                    }
                );
                const response = await this.$http(
                    `/dispatch-order/change-state/${this.currentRecord.id}/${stateId}`
                );
                if (response.status == 200) {
                    this.$message.success(response.data.message);
                    this.$emit("getRecords");
                    this.$emit("update:showDialog", false);
                }
            } catch (e) {
                console.log("🚀 ~ changeState ~ e:", e);
                return;
            } finally {
                this.loading = false;
            }
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>

<style></style>
