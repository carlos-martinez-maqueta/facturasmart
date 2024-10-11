<template>
    <el-dialog
        :visible="showDialog"
        @open="open"
        @close="close"
        :title="title"
        v-loading="loading"
        append-to-body
        width="50%"
    >
        <div class="row">
            <div class="col-12 m-1">
                <el-input
                    v-model="form.description"
                    placeholder="Descripción de la lista"
                ></el-input>
            </div>
            <div class="col-12 m-1">
                <el-input
                    v-model="form.number"
                    placeholder="Número de orden de despacho"
                >
                    <el-button
                        slot="append"
                        icon="el-icon-search"
                        @click="search"
                    ></el-button>
                </el-input>
            </div>
        </div>
        <div class="row">
            <template v-if="list.length > 0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Ordenar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in list" :key="index">
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.number }}</td>
                                <td>
                                    {{ item.customer.number }} -
                                    {{ item.customer.name }}

                                    <br />
                                    <span class="text-primary"
                                        >{{ item.location }}
                                    </span>
                                </td>
                                <td>
                                    <el-button-group>
                                        <el-button
                                            icon="el-icon-arrow-up"
                                            @click="moveItemUp(index)"
                                            :disabled="index === 0"
                                        ></el-button>
                                        <el-button
                                            icon="el-icon-arrow-down"
                                            @click="moveItemDown(index)"
                                            :disabled="
                                                index === list.length - 1
                                            "
                                        ></el-button>
                                    </el-button-group>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
            <template v-else>
                <div class="col-12">
                    <div class="alert alert-warning" role="alert">
                        Lista vacía
                    </div>
                </div>
            </template>
            <span slot="footer" class="dialog-footer">
                <el-button @click="close">Cancelar</el-button>
                <el-button type="primary" @click="create">Guardar</el-button>
            </span>
        </div>
    </el-dialog>
</template>

<script>
export default {
    props: ["showDialog"],
    data() {
        return {
            form: {
                number: "",
                description: "",
            },
            list: [],
            loading: false,
            title: "Crear lista de despacho",
        };
    },
    methods: {
        initForm() {
            this.form = {
                number: "",
                description: "",
            };
            this.list = [];
        },
        moveItemUp(index) {
            let item = this.list[index];
            this.list.splice(index, 1);
            this.list.splice(index - 1, 0, item);
        },
        moveItemDown(index) {
            let item = this.list[index];
            this.list.splice(index, 1);
            this.list.splice(index + 1, 0, item);
        },
        async create() {
            if (this.list.length === 0) {
                this.$message.error("La lista está vacía");
                return;
            }
            if (!this.form.description) {
                this.$message.error("Ingrese una descripción");
                return;
            }
            try {
                this.loading = true;
                const response = await this.$http.post(
                    "/dispatch-order/save-route",
                    {
                        description: this.form.description,
                        items: this.list.map((item, idx) => ({
                            dispatch_order_id: item.id,
                            order: idx,
                        })),
                    }
                );
                console.log("🚀 ~ create ~ response:", response);
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
        open() {
            this.initForm();
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        search() {
            if (!this.form.number) {
                this.$message.error("Ingrese un número de orden de despacho");
                return;
            }
            this.loading = true;
            this.$http(`/dispatch-order/search?number=${this.form.number}`)
                .then((response) => {
                    console.log("🚀 ~ this.$http ~ response:", response);
                    let { data } = response;
                    if (data) {
                        if (data.success) {
                            this.list.push(data.data);
                            this.form.number = "";
                            this.close();
                        } else {
                            this.$message.error(data.message);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.loading = false;
                });
        },
    },
};
</script>

<style></style>
