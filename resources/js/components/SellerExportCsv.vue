<template>
    <el-dialog
        :visible="showDialog"
        width="30%"
        title="Exportar a CSV"
        @close="close"
        @open="open"
        append-to-body
    >
        
        <div class="row mt-2">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td>
                            <el-checkbox v-model="user.selected"></el-checkbox>
                        </td>
                        <td>{{ user.name }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="exportCsvSellers">Exportar</el-button>
        </span>
    </el-dialog>
</template>

<script>

export default
  {
    props: ["showDialog"],
    data() {
        return {
            form: {},
            resource: "mall",
            users: [],
        };
    },
    methods: {
        getUsers(){
            this.$http.get(`/mall/users`)
            .then((response) => {
                let {data:{data}} = response;
                this.users = data;
                console.log("🚀 ~ .then ~ this.users:", this.users)
            })

        },
        exportCsvSellers(){
            let selected = this.users.filter(user => user.selected);
            if(selected.length == 0){
                this.$message.error("Seleccione al menos un usuario");
                return;
            }
            let ids = selected.map(user => user.id);
            let url = `/mall/export-csv-sellers-by-id?ids=${ids.join(",")}`;
            window.open(url, "_blank");
        },
        open() {
            this.getUsers();
        },
        close() {
            this.$emit("update:showDialog", false);
        },
    },
};
</script>
