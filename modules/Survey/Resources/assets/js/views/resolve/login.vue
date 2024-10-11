<template>
    <div class="login-container">
        <div class="row">
            <div
                class="col-md-12 col-lg-12 col-sm-12 col-12 text-center m-2"
                v-if="image_url != 'no found'"
            >
                <img
                    width="200px"
                    :src="image_url"
                    alt="Imagen de la encuesta"
                    class="img-fluid"
                />
            </div>
            <div class="col-md-12 col-lg-12 col-sm-12 col-12">
                <h1 class="text-center">{{ title }}</h1>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <el-form
                            @submit.native.prevent="login"
                            class="login-form"
                            ref="loginForm"
                        >
                            <el-form-item label="Correo electrónico">
                                <el-input
                                    v-model="loginForm.email"
                                    type="email"
                                    placeholder="Correo electrónico"
                                ></el-input>
                            </el-form-item>
                            <el-form-item label="Contraseña">
                                <el-input
                                    v-model="loginForm.password"
                                    type="password"
                                    placeholder="Contraseña"
                                ></el-input>
                            </el-form-item>
                            <el-form-item>
                                <el-button
                                    :disabled="isCompleted"
                                    type="primary"
                                    long
                                    @click="login"
                                    >Iniciar sesión</el-button
                                >
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["title", "uuid", "image_url"],
    data() {
        return {
            loginForm: {
                email: "",
                password: "",
            },
            recordId: null,
            loading: false,
            showDialog: false,
            showDialogPassword: false,
            isCompleted: false,
        };
    },
    created() {
        console.log("🚀 ~ created ~ this.uuid:", this.uuid);
    },
    methods: {
        login() {
            this.loginForm.uuid = this.uuid;
            this.$http
                .post("/survey-resolve/resolve", this.loginForm)
                .then((response) => {
                    console.log("🚀 ~ .then ~ response:", response);
                    // let {data} = response.data;
                    if (response.status == 200) {
                        let { is_completed } = response.data;
                        if (!is_completed) {
                            this.$message.success("Inicio de sesión exitoso");
                            window.location.href = "/resolve/fill/" + this.uuid;
                        } else {
                            this.isCompleted = true;
                            this.$message.error(
                                "La encuesta ya fue completada"
                            );
                        }
                        // window.location.href = "/resolve/fill/" + this.uuid;
                    } else {
                        this.$message.error(
                            "Error al iniciar sesión, verifique sus credenciales"
                        );
                    }
                })
                .catch((error) => {
                    let { data } = error.response;
                    if (data) {
                        this.$message.error(data.message);
                    }
                });
        },
    },
};
</script>

<style>
.login-container {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-form {
    max-width: 400px;
    margin: auto;
}
</style>
