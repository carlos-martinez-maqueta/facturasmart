<template>
    <div>
        <div v-loading="loading">
            <div class="page-header pr-0">
                <h2>
                    <a href="/dashboard"
                        ><i class="fas fa-tachometer-alt"></i
                    ></a>
                </h2>
                <ol class="breadcrumbs">
                    <li class="active"><span>Comisiones</span></li>
                </ol>
                <div class="right-wrapper pull-right">
                    <a
                        href="#"
                        @click.prevent="clickCreate()"
                        class="btn btn-custom btn-sm mt-2 mr-2"
                        ><i class="fa fa-plus-circle"></i> Nuevo</a
                    >
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <data-table
                                :resource="resource"
                                :configuration="configuration"
                            >
                                <tr slot="heading" width="100%">
                                    <th>#</th>
                                    <th>Comisión</th>
                                    <th >Margen</th>
                                    <th></th>
                                </tr>
                                <tr></tr>
                                <tr slot-scope="{ index, row }">
                                    <td>{{ index }}</td>
                                    <td>
                                        {{ row.percentage }} %
                                    </td>
                                    <td>
                                        {{ row.margin }}
                                    </td>
                                    <td>
                                        <a
                                            href="#"
                                            @click.prevent="clickCreate(row.id)"
                                            class="btn btn-custom btn-sm"
                                            ><i class="fa fa-edit
                                            "></i
                                        ></a>
                                    </td>
                                </tr>
                            </data-table>
                        </div>
                    </div>
                </div>
            </div>
            <comission-form
                :showDialog.sync="showDialog"
                :recordId="recordId"
            ></comission-form>
        </div>
    </div>
</template>

<script>
import DataTable from "@components/DataTable.vue";
import ComissionForm from "./form.vue";
export default {
    props: ["configuration"],

    components: {
        DataTable,
        ComissionForm,
    },
    data() {
        return {
            resource: "seller/comission",
            loading: false,
            records: [],
            recordId: null,
            total: {
                total_documents: 0,
                total_sales: 0,
            },
            showDialog: false,
        };
    },
    methods: {
        clickCreate(id) {
            this.recordId = id;
            this.showDialog = true;
        },
    },
};
</script>

<style></style>
