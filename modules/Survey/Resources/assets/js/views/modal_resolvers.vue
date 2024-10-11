<template>
    <el-dialog
        title="Participantes"
        :visible="showDialog"
        width="80%"
        @close="close"
        @open="open"
        append-to-body
    >
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Telefono</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="record in records" :key="record.id">
                                <td>{{ record.name }}</td>
                                <td>{{ record.email }}</td>
                                <td>{{ record.phone }}</td>
                                <td>
                                    <el-tooltip
                                        class="item"
                                        effect="dark"
                                        content="Ver respuestas"
                                        placement="top"
                                    >
                                        <el-button
                                            type="primary"
                                            icon="el-icon-tickets"
                                            @click="clickResponses(record.id)"
                                            size="mini"
                                        ></el-button>
                                    </el-tooltip>
                                    <el-button
                                        type="danger"
                                        icon="el-icon-delete"
                                        size="mini"
                                    ></el-button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <el-pagination
            @current-change="getRecords"
            layout="total, prev, pager, next"
            :total="pagination.total"
            :current-page.sync="pagination.current_page"
            :page-size="pagination.per_page"
        >
        </el-pagination>
    </el-dialog>
</template>

<script>
export default {
    props: ["id", "showDialog"],
    data() {
        return {
            records: [],
            pagination: {},
        };
    },
    methods: {
        clickResponses(responseId) {
            // console.log(this.id, responseId);
            window.open(`/survey/answers/${responseId}/${this.id}`, "_blank");
        },
        getRecords() {
            this.$http
                .get(`/survey/respondet/list/${this.id}`)
                .then((response) => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                });
        },
        open() {
            this.getRecords();
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>

<style></style>
