<template>
    <el-dialog
        :visible="showDialog"
        title="Crear Tipo de Reservar"
        :close-on-click-modal="false"
        @open="open"
        @close="close"
        v-loading="loading"
    >
        <div class="row mt-2">
        
            <div class="col-md-6">
                <label for="date">Nombre</label>
                <el-input v-model="form.nombre"></el-input>
                
            </div>
            <div class="col-md-6">
                <label for="time">Ubicación</label>
                <el-input v-model="form.ubicacion"></el-input>
            
            </div>
                <div class="col-12">
                <label for="date">Descripción</label>
                <el-input 
                type="textarea"

                v-model="form.description"></el-input>
                
            </div>
            <!-- <div class="col-md-4">
                <label for="duration" class="w-100">Capacidad</label>
                <el-input-number
                    class="w-100"
                    v-model="form.capacidad"
                    :min="1"
                    :max="24"
                    label="Horas"
                ></el-input-number>
            </div> -->
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="save">Guardar</el-button>
        </div>
    </el-dialog>
</template>

<script>
export default {
    props: ["showDialog", "recordId"],
    data() {
        return {
            form: {},
            customers: [],
            loading_search: false,
            timer: null,
            types: [],
            resource:"/canchas/types",
            loading:false,
        };
    },
    created() {
    
    },
    methods: {
    
        initForm() {
            this.form = {
                nombre:null,
                ubicacion:null,
                capacidad:0,
                description:null,
        
            };
        },
        open() {
            this.initForm();
            if (this.recordId) {
                this.getRecord();
            }
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        valid(){
            let {nombre, ubicacion, description} = this.form;
            if(!nombre || !ubicacion || !description){
                this.$message.error("Todos los campos son requeridos");
                return false;
            }
            return true;
        },
        getRecord() {
            this.$http.get(`${this.resource}/record/${this.recordId}`).then((response) => {
                this.form = response.data;
            });
        },
        save() {
            if(!this.valid()) return;
            this.loading = true;
            this.$http.post(this.resource, this.form).then((response) => {
                this.loading = false;
                this.$emit("getRecords");
                this.$emit("update:showDialog", false);
                this.$message.success("Registro guardado con éxito");
            }).catch((error) => {
                console.log("🚀 ~ this.$http.post ~ error:", error)
                this.loading = false;
                this.$message.error("Error al guardar el registro");
            });
        },
    },
};
</script>

<style></style>
