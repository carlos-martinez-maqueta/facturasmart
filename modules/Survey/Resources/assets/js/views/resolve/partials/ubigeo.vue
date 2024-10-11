<template>
    <div class="d-flex flex-column align-items-center">
        <div class="col-6">
            <label for="address">Ubicación</label>
            <el-cascader
                v-model="form.location_id"
                :clearable="true"
                :options="locations"
                filterable
            ></el-cascader>
        </div>
        <!-- <div class="col-6">
            <label for="address">Dirección</label>
            <el-input v-model="form.address" placeholder="Dirección"></el-input>
        </div> -->
        <div class="col-6 mt-2 d-flex justify-content-center">
            <el-button type="primary" @click="submit">Guardar</el-button>
        </div>
    </div>
</template>

<script>
export default {
    props: [
        "showDialog",
        "departments",
        "provinces",
        "districts",
        "user_email",
        "locations",
        "uuid"
    ],
    data() {
        return {
            form: {},
            provincesFiltered: [],
        };
    },
    created() {
        this.initForm();
    },
    computed: {
        filteredProvinces() {
            if (!this.form.department) return this.provinces;
            return this.provinces.filter((province) => {
                return province.department_id === this.form.department;
            });
        },
    },
    methods: {
        initForm() {
            this.form = {
                address: "",
                location_id: [],
            };
        },
        formatId(inputString) {
            const resultArray = [];

            for (let i = 2; i <= inputString.length; i += 2) {
                const substring = inputString.substring(0, i);
                resultArray.push(substring);
            }
            return resultArray;
        },
        validate() {
            let { location_id } = this.form;
            if (!location_id) return false;
            if (location_id.length !== 3) return false;
            return true;
        },
        async submit() {
            if (!this.validate()) {
                this.$message.error("Debe seleccionar una ubicación válida");
                return;
            }
            this.$http
                .post(`/resolve/update-ubigeo/${this.uuid}`, {
                    location_id: this.form.location_id,
                    user_email: this.user_email,
                })
                .then((response) => {
                    if (response.status !== 200) {
                        this.$message.error("Error al actualizar el ubigeo");
                        return;
                    }
                    this.$message.success("Ubigeo actualizado correctamente");
                    window.location.reload();
                });
        },
        open() {
            this.initForm();
        },
        close() {},
    },
};
</script>
