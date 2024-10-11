<template>
    <el-dialog
        :close-on-click-modal="false"
        :visible="showDialog"
        @open="open"
        @close="close"
        :title="title"
    >
        <div class="row mt-2">
            <div class="col-md-12">
                <label for="comment">Se envia cuando</label>
                <el-input
                    type="textarea"
                    v-model="form.comment"
                    placeholder="Se envia cuando"
                    rows="2"
                ></el-input>
            </div>
            <div class="col-md-12">
                <label for="message">Mensaje</label>
                <!-- <el-input
                    type="textarea"
                    v-model="form.message"
                    placeholder="Mensaje"
                    rows="2"
                ></el-input> -->
                <!-- <vue-ckeditor
                    v-model="form.message"
                    :editors="editors"
                    type="classic"
                ></vue-ckeditor> -->
                <!-- <Editor
                    v-model="form.message"
                    :disabled="false"
                    ref="editor"
                    :init="{
                        language: 'es',
                        spellchecker_language: 'es',
                        selector: 'textarea#basic-example',
                        height: 500,
                        plugins: [
                            'advlist',
                            'autolink',
                            'lists',
                            'link',
                            'image',
                            'charmap',
                            'preview',
                            'anchor',
                            'searchreplace',
                            'visualblocks',
                            'code',
                            'fullscreen',
                            'insertdatetime',
                            'media',
                            'table',
                            'help',
                            'wordcount',
                        ],
                        toolbar:
                            'undo redo | blocks | ' +
                            'bold italic backcolor | alignleft aligncenter ' +
                            'alignright alignjustify | bullist numlist outdent indent | ' +
                            'removeformat | help',
                        content_style:
                            'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
                        readonly: false
                    }"
                    tinymce-script-url="/tinymce/tinymce.min.js"
                ></Editor> -->
                <div>
                    <!-- Otros elementos del formulario -->
                    <editor
                        v-model="form.message"
                        tinymce-script-src="/tinymce/js/tinymce/tinymce.min.js"
                        :init="{
                            language_url: '/tinymce/langs/es.js',
                            language: 'es',
                            spellchecker_language: 'es',
                            selector: 'textarea#basic-example',
                            height: 500,
                            plugins: [
                                'advlist',
                                'autolink',
                                'lists',
                                'link',
                                'image',
                                'charmap',
                                'preview',
                                'anchor',
                                'searchreplace',
                                'visualblocks',
                                'code',
                                'fullscreen',
                                'insertdatetime',
                                'media',
                                'table',
                                'help',
                                'wordcount',
                            ],
                            zindex: 2000,
                            toolbar:
                                'undo redo | blocks | ' +
                                'bold italic backcolor | alignleft aligncenter ' +
                                'alignright alignjustify | bullist numlist outdent indent | ' +
                                'removeformat | help',
                            content_style:
                                'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
                        }"
                    />
                </div>
                <br>
                <div>
                    <label for="active">Activado</label>
                    <br>
                    <el-switch
                        v-model="form.active"
                        active-color="#13ce66"
                        inactive-color="#ff4949"
                        active-text="Si"
                        inactive-text="No"
                    ></el-switch>
                </div>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button @click="close">Cancelar</el-button>
            <el-button type="primary" @click="submit">Guardar</el-button>
        </span>
    </el-dialog>
</template>
<style>
.tox-promotion {
    display: none !important;
}
.tox-tinymce-aux {
    z-index: 99999999999 !important;
}

.tox-statusbar__branding {
    display: none !important;
}
.tox-statusbar{
    display: none !important;

}
</style>
<script>
// import Editor from "@tinymce/tinymce-vue";
import Editor from "@tinymce/tinymce-vue";
export default {
    props: ["showDialog", "recordId"],
    components: {
        Editor,
        // "vue-ckeditor": VueCkeditor.component,
        // Editor
    },
    data() {
        return {
            // editors: {
            //     classic: ClassicEditor,
            // },
            showAgencyModal: false,
            fileList2: [],
            defaultFileList2: [],
            form: {
                name: "",
                message: "",
                active: false,
            },
            title: "EDITAR MENSAJE",
            resource: "message-integrate-system",
        };
    },
    mounted() {},
    methods: {
        async open() {
            this.form = {
                id: null,
                trigger_event: null,
                comment: null,
                message: null,
                active: false,
            };
            this.getRecord();
        },
        async getRecord() {
            const response = await this.$http.get(
                `/${this.resource}/record/${this.recordId}`
            );
            if (response.status == 200) {
                this.form = response.data;
                this.form.active = this.form.active == 1 ? true : false;
            }
        },
        close() {
            this.$emit("update:showDialog", false);
        },
        async submit() {
            this.loading = true;
            const response = await this.$http.post(
                `/${this.resource}`,
                this.form
            );
            if (response.status == 200) {
                this.$message({
                    type: "success",
                    message: "Se actualizó correctamente",
                });
                this.$emit("getRecords");
                this.$emit("update:showDialog", false);
            }
            this.loading = false;
        },
    },
};
</script>
