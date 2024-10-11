<template>
    <el-dialog
        :visible="showDialog"
        width="80%"
        title="Detalle de reserva"
        @open="open"
        @open="close"
        append-to-body
        v-loading="loading"
    >
        <div class="row mt-2"></div>
    </el-dialog>
</template>

<script>
export default {
    props: ["showDialog", "recordId"],
    data() {
        return {
            form: {},
            loading: false,
        };
    },
    methods: {
        getRecord() {
            this.loading = true;
            this.$http.get(`/canchas/detail/${this.recordId}`).then((response) => {
                this.form = response.data;
                this.loading = false;
            });
        },
        open() {
            this.loading = true;
        
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>
