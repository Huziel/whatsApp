<script setup>
import { useRouter } from "vue-router"

const router = useRouter()

function logout() {
  localStorage.removeItem("token")
  router.push({ name: "Login" })
}
</script>

<template>
  <main class="container py-5">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h3 mb-0">Dashboard</h1>
      <button class="btn btn-outline-secondary" type="button" @click="logout">
        Cerrar sesión
      </button>
    </div>
    <p class="mt-4">Has iniciado sesión correctamente.</p>
  </main>
</template>
