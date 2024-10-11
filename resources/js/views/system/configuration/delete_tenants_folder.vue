<template>
    <el-dialog
        :visible="showDialog"
        append-to-body
        @open="open"
        @close="close"
        title="Eliminar carpetas sin clientes activos"
        top="10vh"
    >
        <div class="row mt-2">
            <div
                class="col-12 table-responsive scrollable-table"
                v-loading="loading"
            >
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>
                                <el-checkbox
                                    v-model="allSelected"
                                    @change="selectAll"
                                ></el-checkbox>
                            </th>
                            <th>Nombre</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(folder, idx) in folders" :key="idx">
                            <td>
                                <el-checkbox
                                    v-model="folder.selected"
                                ></el-checkbox>
                            </td>
                            <td>{{ folder.name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="deleteFolders"
                >Eliminar</el-button
            >
        </span>
    </el-dialog>
</template>
<style scoped>
.scrollable-table {
    height: 500px;
    overflow-y: auto;
}
</style>
<script>
export default {
    props: ["showDialog"],
    data() {
        return {
            folders: [],
            allSelected: false,
            loading: false,
        };
    },
    methods: {
        selectAll() {
            this.folders.forEach((folder) => {
                folder.selected = this.allSelected;
            });
        },
        async deleteFolders() {
            let folders = this.folders.filter((folder) => folder.selected);
            let data = {
                folders: folders.map((folder) => folder.name),
            };
            try {
                await this.$confirm(
                    "¿Está seguro de eliminar las carpetas seleccionadas?",
                    "Eliminar carpetas",
                    {
                        confirmButtonText: "Eliminar",
                        cancelButtonText: "Cancelar",
                        type: "warning",
                    }
                );
                this.loading = true;
                console.log("🚀 ~ deleteFolders ~ this.loading:", this.loading)
                this.$http
                    .post("/delete_folders", data)
                    .then((response) => {
                        this.$message({
                            message: "Carpetas eliminadas",
                            type: "success",
                        });
                        this.getFolders();
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                    .finally(() => {
                        this.loading = false;
                        console.log("🚀 ~ .finally ~ this.loading:", this.loading)
                    });
            } catch (error) {
                console.log(error);
            }
        },
        getFolders() {
            this.loading = true;
            this.$http("/clients_uuids")
                .then((response) => {
                    let data = response.data;
                    this.folders = data.directories_to_delete;
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        open() {
            this.getFolders();
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>
