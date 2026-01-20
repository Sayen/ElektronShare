import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/HomeView.vue')
  },
  {
    path: '/admin/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue')
  },
  {
    path: '/admin',
    component: () => import('../views/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
        {
            path: '',
            name: 'AdminDashboard',
            redirect: { name: 'AdminFiles' } // Redirect to file manager by default
        },
        {
            path: 'files',
            name: 'AdminFiles',
            component: () => import('../views/admin/FileManager.vue')
        },
        {
            path: 'push',
            name: 'AdminPush',
            component: () => import('../views/admin/PushConsole.vue')
        }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation Guard (Mock implementation, will be replaced with real auth check)
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth) {
    const isAuthenticated = localStorage.getItem('authToken'); // Simple token check
    if (!isAuthenticated) {
      next({ name: 'Login' });
    } else {
      next();
    }
  } else {
    next();
  }
})

export default router
