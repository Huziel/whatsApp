<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card shadow p-4">
          <h3 class="text-center mb-4">Iniciar Sesión</h3>

          <form @submit.prevent="login">
            <div class="mb-3">
              <label for="email" class="form-label">Correo</label>
              <input type="email" v-model="email" class="form-control" id="email" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" v-model="password" class="form-control" id="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
              {{ loading ? "Ingresando..." : "Ingresar" }}
            </button>
          </form>

          <div v-if="error" class="alert alert-danger mt-3">
            {{ error }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios"

export default {
  name: "LoginForm",
  data() {
    return {
      email: "",
      password: "",
      loading: false,
      error: null
    }
  },
  methods: {
    async login() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post("http://127.0.0.1:8000/api/v1/auth/login", {
          email: this.email,
          password: this.password
        })

        // Guardar token en localStorage
        localStorage.setItem("token", response.data.access_token)

        await this.$router.push({ name: "Dashboard" })
      } catch (err) {
        this.error = "Credenciales incorrectas"
      } finally {
        this.loading = false
      }
    }
  }
}
</script>
