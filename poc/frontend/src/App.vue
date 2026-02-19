<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isMenuOpen = ref(false)

const navItems = [
  { path: '/dashboard', label: 'Dashboard', icon: '📊' },
  { path: '/cabinets', label: 'Cabinets', icon: '🗄️' }
]

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}
</script>

<template>
  <div class="app">
    <nav class="sidebar">
      <div class="logo">
        <span class="logo-icon">⚡</span>
        <span class="logo-text">DCIM PoC</span>
      </div>

      <ul class="nav-menu">
        <li v-for="item in navItems" :key="item.path">
          <router-link :to="item.path" class="nav-link" active-class="active">
            <span class="nav-icon">{{ item.icon }}</span>
            <span class="nav-label">{{ item.label }}</span>
          </router-link>
        </li>
      </ul>

      <div class="sidebar-footer">
        <div class="version">v1.0.0</div>
      </div>
    </nav>

    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<style>
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
  background: #f3f4f6;
}

.app {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 240px;
  background: #1f2937;
  color: white;
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
}

.logo {
  padding: 24px 20px;
  display: flex;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid #374151;
}

.logo-icon {
  font-size: 24px;
}

.logo-text {
  font-size: 20px;
  font-weight: 600;
}

.nav-menu {
  list-style: none;
  padding: 16px 0;
  margin: 0;
  flex: 1;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  color: #9ca3af;
  text-decoration: none;
  transition: all 0.2s;
}

.nav-link:hover {
  background: #374151;
  color: white;
}

.nav-link.active {
  background: #3b82f6;
  color: white;
}

.nav-icon {
  font-size: 18px;
}

.nav-label {
  font-size: 14px;
  font-weight: 500;
}

.sidebar-footer {
  padding: 16px 20px;
  border-top: 1px solid #374151;
}

.version {
  font-size: 12px;
  color: #6b7280;
}

.main-content {
  flex: 1;
  margin-left: 240px;
  min-height: 100vh;
}

@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    position: relative;
    height: auto;
  }

  .main-content {
    margin-left: 0;
  }

  .app {
    flex-direction: column;
  }
}
</style>