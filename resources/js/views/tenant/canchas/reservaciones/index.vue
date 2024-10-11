<template>
    <div class="row m-2">
        <br /><br />
        <div class="card" v-loading="loading">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <h3>Nueva reservación</h3>
                </div>
            </div>
            <div class="row card-body">
                <!-- <div class="col-lg-4 col-md-6 col-sm-6">
                    <label for="name">Nombre de reserva</label>
                    <el-input type="text" v-model="form.name"> </el-input>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <label for="location">Ubicación</label>
                    <el-input type="text" v-model="form.location"></el-input>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <label for="description">Descripción</label>
                    <el-input type="text" v-model="form.description"></el-input>
                </div> -->
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <label for="name_client"> Nombre del cliente </label>
                    <el-input type="text" v-model="form.name_client"></el-input>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <label for="phone">Apellido del cliente</label>
                    <el-input
                        type="text"
                        v-model="form.last_name_client"
                    ></el-input>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <label for="phone">Teléfono del cliente</label>
                    <el-input type="text" v-model="form.phone"></el-input>
                </div>
                    <div class="col-lg-4 col-md-6 col-sm-6">
                <label>Reserva</label>
                <el-select
                    v-model="form.type_id"
                    clearable
                    placeholder="Seleccione"
                >
                    <el-tooltip
                        effect="dark"
                        v-for="(type, idx) in types"
                        placement="left"
                        :key="idx"
                    >
                        <div slot="content">
                            <p>
                                Descripción: {{ type.description }}
                            </p>
                            <p>
                                Ubicación: {{ type.location }}
                            </p>
                        </div>
                        <el-option :value="type.id" :label="type.name">
                        </el-option>
                    </el-tooltip>
                </el-select>
            </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <label for="date">Fecha de reserva</label>
                    <el-date-picker
                        v-model="form.date"
                        type="date"
                        format="yyyy-MM-dd"
                        value-format="yyyy-MM-dd"
                        placeholder="Seleccionar fecha"
                    ></el-date-picker>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <label for="time">Hora de reserva</label>
                    <el-time-picker
                    format="HH:mm"
                    value-format="HH:mm"
                    placeholder="Seleccionar hora"
                     v-model="form.time"></el-time-picker>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6">
                    <label for="duration">Duración de la reserva</label>
                    <el-input type="number" v-model="form.duration"></el-input>
                </div>
            </div>
            <div class="row card-footer">
                <div class="col-lg-12 col-md-12 col-sm-12 text-end">
                    <el-button @click="clean">Limpiar</el-button>
                    <el-button type="primary" @click="submit"
                        >Guardar</el-button
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["types"],
    data() {
        return {
            form: {},
            loading:false,
            errorMessages: [
                // {
                //     name: "name",
                //     message: "El nombre de la reserva es requerido",
                // },
                // {
                //     name: "location",
                //     message: "La ubicación es requerida",
                // },
                // {
                //     name: "description",
                //     message: "La descripción es requerida",
                // },
                {
                    name: "type_id",
                    message: "La reserva es requerida",
                },
                {
                    name: "name_client",
                    message: "El nombre del cliente es requerido",
                },
                {
                    name: "last_name_client",
                    message: "El apellido del cliente es requerido",
                },
                {
                    name: "phone",
                    message: "El teléfono del cliente es requerido",
                },
                {
                    name: "date",
                    message: "La fecha de la reserva es requerida",
                },
                {
                    name: "time",
                    message: "La hora de la reserva es requerida",
                },
                {
                    name: "duration",
                    message: "La duración de la reserva es requerida",
                }
            ],
        };
    },
    mounted() {
    },
    methods: {
        clean() {
            this.initForm();
        },

        valid(){
            let pass = true;
            for (let i = 0; i < this.errorMessages.length; i++) {
                if (!this.form[this.errorMessages[i].name]) {
                    this.$message.error(this.errorMessages[i].message);
                    pass = false;
                    break;
                }
            }
            return pass;
        },
        submit(){
            if(this.valid()){
                this.loading = true;
                this.$http.post("/reservaciones/public", this.form).then(
                    (response) => {
                        console.log("🚀 ~ submit ~ response:", response)
                        let { data:{cancha} } = response;
                            this.$message.success("Reservación creada con éxito");
                        if(cancha){
                            let {id} = cancha;
                            let pdfUrl = `/canchas/reserva/${id}`;
                            window.open(pdfUrl, "_blank");
                        }
                        this.loading = false;
                        this.initForm();
                    },
                    (error) => {
                        this.loading = false;
                        this.$message.error("Error al crear la reservación");
                    }
                ).catch((error) => {
                    this.loading = false;
                    this.$message.error("Error al crear la reservación");
                });
            }
        },
        initForm() {
            this.form = {
                name: "",
                location: "",
                description: "",
                name_client: "",
                last_name_client: "",
                phone: "",
                date: "",
                time: "",
                duration: "",
            };
        },
    },
};
</script>

<style></style>
