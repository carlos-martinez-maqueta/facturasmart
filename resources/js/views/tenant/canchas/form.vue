<template>
    <el-dialog
        :visible="showDialog"
        title="Crear Reservar"
        :close-on-click-modal="false"
        @open="open"
        @close="close"
    >
        <div class="row mt-2">
            <div class="col-md-6">
                <label
                    >Cliente

                    <a href="#" @click.prevent="showDialogNewPerson = true"
                        >[+ Nuevo]</a
                    >
                </label>
                <el-select
                    v-model="form.customer_id"
                    filterable
                    remote
                    popper-class="el-select-customers"
                    clearable
                    placeholder="Nombre o número de documento"
                    :remote-method="searchRemoteCustomers"
                    :loading="loading_search"
                >
                    <el-option
                        v-for="option in customers"
                        :key="option.id"
                        :value="option.id"
                        :label="option.description"
                    ></el-option>
                </el-select>
            </div>
            <!-- <div class="col-md-6">
                <label for="name">Nombre de reserva</label>
                <el-input type="text" v-model="form.name"> </el-input>
            </div>
            <div class="col-md-6">
                <label for="location">Ubicación</label>
                <el-input type="text" v-model="form.location"></el-input>
            </div>
            <div class="col-md-6">
                <label for="description">Descripción</label>
                <el-input type="text" v-model="form.description"></el-input>
            </div> -->
            <div class="col-md-6">
                <label>Nombre de reserva</label>
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
            <div class="col-md-4">
                <label for="date">Fecha</label>
                <el-date-picker
                    v-model="form.date"
                    type="date"
                    placeholder="Seleccione"
                    value-format="yyyy-MM-dd"
                    format="yyyy-MM-dd"
                ></el-date-picker>
            </div>
            <div class="col-md-4">
                <label for="time">Hora</label>
                <el-time-picker
                    v-model="form.time"
                    placeholder="Seleccione"
                    value-format="HH:mm"
                    format="HH:mm"
                ></el-time-picker>
            </div>
            <div class="col-md-4">
                <label for="duration" class="w-100">Duración</label>
                <el-input-number
                    class="w-100"
                    v-model="form.duration"
                    :min="1"
                    :max="24"
                    label="Horas"
                ></el-input-number>
            </div>
        </div>
        <div slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="save">Guardar</el-button>
        </div>
        <person-form
            document_type_id="03"
            :external="true"
            :input_person="input_person"
            :showDialog.sync="showDialogNewPerson"
            type="customers"
        ></person-form>
    </el-dialog>
</template>

<script>
import PersonForm from "../../tenant/persons/form.vue";
export default {
    props: ["showDialog", "recordId"],
    components: {
        PersonForm,
    },
    data() {
        return {
            form: {},
            customers: [],
            input_person: {},
            loading_search: false,
            timer: null,
            types: [],
            showDialogNewPerson: false,
        };
    },
    created() {
        this.$eventHub.$on("reloadDataPersons", (customer_id) => {
            this.reloadDataCustomers(customer_id);
        });
        this.getTypes();
        this.$http.get(`/documents/data-table/customers`).then((response) => {
            this.customers = response.data.customers;
        });
    },
    methods: {
        async reloadDataCustomers(customer_id) {
            await this.$http
                .get(`/documents/search/customer/${customer_id}`)
                .then((response) => {
                    this.customers = response.data.customers;
                    this.form.customer_id = customer_id;
                });
        },
        getTypes() {
            this.$http.get(`/canchas/types/records`).then((response) => {
                this.types = response.data.data;
            });
        },
        searchRemoteCustomers(input) {
            if (timer) {
                clearTimeout(timer);
            }
            this.timer = setTimeout(() => {
                if (input.length > 0) {
                    this.loading_search = true;
                    let parameters = `input=${input}`;

                    this.$http
                        .get(`/documents/data-table/customers?${parameters}`)
                        .then((response) => {
                            this.customers = response.data.customers;
                            this.input_person.number =
                                this.customers.length == 0 ? input : null;
                            this.loading_search = false;
                        });
                }
            }, 500);
        },
        initForm() {
            this.form = {
                customer_id: null,
                type_id: null,
                date: null,
                time: null,
                duration: null,
            };
        },
        validate() {
            let {
                customer_id,
                name,
                location,
                description,
                type_id,
                date,
                time,
                duration,
            } = this.form;
            if (!customer_id || !type_id || !date || !time || !duration) {
                this.$message.error("Todos los campos son requeridos");
                return false;
            }
            return true;
        },

        open() {
            this.initForm();
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        save() {
            if (!this.validate()) return;
            this.loading = true;
            this.$http
                .post(`/canchas`, this.form)
                .then((response) => {
                    this.loading = false;
                    this.$emit("update:showDialog", false);
                    this.$eventHub.$emit("reloadData");
                })
                .catch((error) => {
                    this.$message.error(error.response.data.message);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>

<style></style>
