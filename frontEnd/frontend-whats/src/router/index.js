import { createRouter, createWebHistory } from "vue-router"

import LoginForm from "../components/login/Login.vue"
import Dashboard from "../views/Dashboard.vue"

const routes = [
  {
    path: "/",
    redirect: "/login"
  },
  {
    path: "/login",
    name: "Login",
    component: LoginForm
  },
  {
    path: "/dashboard",
    name: "Dashboard",
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: "/:pathMatch(.*)*",
    redirect: "/login"
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to) => {
  const token = localStorage.getItem("token")

  if (to.meta.requiresAuth && !token) {
    return { name: "Login" }
  }
})

export default router
