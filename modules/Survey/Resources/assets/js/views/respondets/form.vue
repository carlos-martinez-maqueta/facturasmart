<template>
    <el-dialog
        :title="title"
        :visible="showDialog"
        width="50%"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        v-loading="loading"
        @open="open"
        @close="close"
    >
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Número <span class="text-danger">*</span></label>

                    <x-input-service
                        v-model="form.number"
                        :identity_document_type_id="
                            form.identity_document_type_id
                        "
                        @search="searchNumber"
                    ></x-input-service>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="name"
                        >Nombre
                        <span class="text-danger">*</span>
                    </label>
                    <el-input
                        v-model="form.name"
                        placeholder="Nombre"
                        clearable
                    >
                    </el-input>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email"
                        >Correo
                        <span class="text-danger">*</span>
                    </label>
                    <el-input
                        v-model="form.email"
                        placeholder="Correo"
                        clearable
                    >
                    </el-input>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="phone">Teléfono </label>
                    <el-input
                        v-model="form.phone"
                        placeholder="Teléfono"
                        clearable
                    >
                    </el-input>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="sex"
                        >Sexo
                        <span class="text-danger">*</span>
                    </label>
                    <el-select v-model="form.sex" placeholder="Sexo" filterable>
                        <el-option
                            v-for="item in sexOptions"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value"
                        ></el-option>
                    </el-select>
                </div>
            </div>
            <div class="col-md-6" v-if="!recordId">
                <div class="form-group">
                    <label for="password"
                        >Contraseña
                        <span class="text-danger">*</span>
                    </label>
                    <el-input
                        v-model="form.password"
                        placeholder="Contraseña"
                        clearable
                    >
                        <el-button
                            slot="append"
                            @click="createPassword"
                            icon="el-icon-refresh"
                        ></el-button>
                    </el-input>
                </div>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="save">Guardar</el-button>
        </span>
    </el-dialog>
</template>

<script>
import { serviceNumber } from "@mixins/functions";

export default {
    props: ["showDialog", "resource", "recordId"],
    mixins: [serviceNumber],
    data() {
        return {
            errors: [],
            form: {},
            loading: false,
            title: "Nuevo participante",
            sexOptions: [
                { value: "male", label: "Masculino" },
                { value: "female", label: "Femenino" },
            ],
        };
    },
    methods: {
        generatePassword(length) {
            let result = "";
            let characters =
                "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            let charactersLength = characters.length;
            for (let i = 0; i < length; i++) {
                result += characters.charAt(
                    Math.floor(Math.random() * charactersLength)
                );
            }
            return result;
        },

        createPassword() {
            this.form.password = this.generatePassword(10);
        },
        initForm() {
            this.form = {
                title: "",
                password: "123456",
                description: "",
                identity_document_type_id: "1",
                number: "",
                sex: "male",
                name: "",
            };
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        getRecord() {
            this.$http
                .get(`/${this.resource}/record/${this.recordId}`)
                .then((response) => {
                    this.form = response.data;
                    this.form.identity_document_type_id = "1";
                });
        },
        searchNumber(data) {
            //cambios apiperu
            this.form.name = data.name;
            this.form.trade_name = data.trade_name;
            this.form.location_id = data.location_id;
            this.form.address =
                data.address == "" || data.address == null ? "-" : data.address;
        },
        open() {
            if (this.recordId) {
                this.title = "Editar Participante";
                this.getRecord();
            }
            this.initForm();
            // this.$emit("update:showDialog", true);
        },
        save() {
            let { number, name, email } = this.form;
            if (!number || !name || !email) {
                this.$message.error("Complete los campos obligatorios");
                return;
            }
            if (this.form.password == "" && !this.recordId) {
                this.$message.error("La contraseña es requerida");
                return;
            }
            if (this.recordId) {
                delete this.form.password;
            }
            this.loading = true;
            this.$http
                .post(`/${this.resource}`, this.form)
                .then((response) => {
                    let message = this.recordId
                        ? "Participante actualizado correctamente"
                        : "Participante guardado correctamente";
                    this.$message.success(message);
                    this.$emit("update:showDialog", false);
                    this.$eventHub.$emit("reloadData");
                })
                .catch((error) => {
                    this.$message.error("Error al guardar la encuesta");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>
