<template>
    <el-dialog
        title="Historial de reemplazos"
        :visible="dialogVisible"
        width="80%"
        append-to-body
        @close="close"
        @open="getHistory"
        v-loading="loading"
    >
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Producto</th>
                    <th>Reemplazo</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(record, index) in records" :key="index">
                    <td>{{ customIndex(index) }}</td>
                    <td>{{ record.date }}</td>
                    <td>{{ record.user_name }}</td>
                    <td>{{ record.full_description }}</td>
                    <td>{{ record.full_description_replace }}</td>
                    <td>{{ record.quantity }}</td>
                </tr>
            </tbody>
        </table>
        <div>
            <el-pagination
                @current-change="getHistory"
                layout="total, prev, pager, next"
                :total="pagination.total"
                :current-page.sync="pagination.current_page"
                :page-size="pagination.per_page"
            >
            </el-pagination>
        </div>
    </el-dialog>
</template>

<script>
import queryString from "query-string";
export default {
    props: ["dialogVisible"],
    data() {
        return {
            records: [],
            pagination: {},
            form: {},
            loading: false,
        };
    },

    methods: {
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form,
            });
        },
        getHistory() {
            this.loading = true;
            return this.$http
                .get(`/item-sets/history?${this.getQueryParameters()}`)
                .then((response) => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                    this.$eventHub.$emit("recordsSkeletonLoader", false);
                })
                .catch((error) => {
                    this.$message.error(
                        "Error al cargar el historial de reemplazos"
                    );
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
        },
        open() {
            this.getHistory();
        },
        close() {
            this.$emit("update:dialogVisible", false);
        },
    },
};
</script>

<style></style>
