<template>
    <el-dialog
        :visible="showDialog"
        title="Reemplazar productos individuales"
        append-to-body
        @open="resetForm"
        @close="close"
    >
        <div class="row">
            <div class="col-md-4">
                <el-button
                    type="primary"
                    @click="viewHistory"
                    :loading="loading"
                >
                    <i class="fas fa-history"></i>
                    Historial
                </el-button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label for="item_id"> Producto a reemplazar </label>
                <el-select
                    v-model="form.item_id"
                    @change="changeItem"
                    filterable
                    remote
                    placeholder="Buscar"
                    popper-class="el-select-items"
                    slot="prepend"
                    id="select-width"
                    :remote-method="searchRemoteItems"
                    :loading="loading_search"
                >
                    <el-option
                        v-for="option in items"
                        :key="option.id"
                        :value="option.id"
                        :label="option.full_description"
                    >
                    </el-option>
                </el-select>
            </div>
            <div class="col-md-4">
                <label for="replace_item_id"> Producto de reemplazo </label>
                <el-select
                    v-model="form.replace_item_id"
                    @change="changeItemReplace"
                    filterable
                    remote
                    placeholder="Buscar"
                    popper-class="el-select-items"
                    slot="prepend"
                    id="select-width"
                    :remote-method="searchRemoteItemsReplace"
                    :loading="loading_search"
                >
                    <el-option
                        v-for="option in items_replace"
                        :key="option.id"
                        :value="option.id"
                        :label="option.full_description"
                    >
                    </el-option>
                </el-select>
            </div>
            <div class="col-md-4">
                <br />
                <el-button
                    :disabled="!form.item_id || !form.replace_item_id"
                    type="primary"
                    @click="save"
                    :loading="loading"
                >
                    <i class="fas fa-save"></i>
                    Reemplazar
                </el-button>
            </div>
        </div>
        <modal-history :dialogVisible.sync="showHistory"></modal-history>
    </el-dialog>
</template>

<script>
import ModalHistory from "./modal_history.vue";
export default {
    props: ["showDialog"],
    components: {
        ModalHistory,
    },
    data() {
        return {
            form: {
                item_id: null,
                replace_item_id: null,
                count: 0,
            },
            loading_search: false,
            items: [],
            loading: false,
            items_replace: [],
            showHistory: false,
        };
    },
    methods: {
        viewHistory() {
            this.showHistory = true;
        },
        async save() {
            let item = this.items.find((item) => item.id === this.form.item_id);
            let replace_item = this.items_replace.find(
                (item) => item.id === this.form.replace_item_id
            );
            if (!item || !replace_item) {
                this.$message.error(
                    "No se encontraron los productos seleccionados."
                );
                return;
            }
            let message = `¿Está seguro de reemplazar el producto ${item.full_description} por ${replace_item.full_description}?`;
            try {
                await this.$confirm(message, "Confirmación", {
                    confirmButtonText: "Sí",
                    cancelButtonText: "No",
                    type: "warning",
                });

                try {
                    const response = await this.$http(
                        `/item-sets/replace/${item.id}/${replace_item.id}`
                    );
                    if (response.status === 200) {
                        let data = response.data;
                        let message = data.message;
                        let success = data.success;
                        if (!success) {
                            this.$message.error(message);
                            return;
                        }
                        this.$message.success(message);
                        this.$emit("update:showDialog", false);
                    } else {
                        this.$message.error(
                            "Ocurrió un error al reemplazar los productos"
                        );
                    }
                } catch (error) {
                    this.$message.error(
                        "Ocurrió un error al reemplazar los productos"
                    );
                }
            } catch (error) {
                return;
            }
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        async searchRemoteItems(input) {
            await this.searchRemote(input);
        },
        async searchRemoteItemsReplace(input) {
            await this.searchRemote(input, true);
        },
        async searchRemote(input, replace = false) {
            if (input.length > 1) {
                this.loading_search = true;
                let parameters = `input=${input}`;
                let url = `/item-sets/records-individuals?${parameters}`;
                if (replace) {
                    url = `/item-sets/records-individuals-not-set?${parameters}`;
                }
                await this.$http.get(url).then((response) => {
                    if (replace) {
                        this.items_replace = response.data.records;
                    } else {
                        this.items = response.data.records;
                    }
                    this.loading_search = false;
                });
            }
        },
        changeItem() {},
        changeItemReplace() {},
        resetForm() {
            this.form = {
                item_id: null,
                replace_item_id: null,
                count: 0,
            };
        },
    },
};
</script>

<style></style>
