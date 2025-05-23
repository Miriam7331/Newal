<script setup>
import { Head, usePage, router } from "@inertiajs/vue3"
import { useDisplay, useTheme } from "vuetify"
import AppLayout from "@/Layouts/App.vue"
import NavBar from "@/Components/NavBar.vue"
import NavBarMobile from "@/Components/NavBarMobile.vue"
import { watch, ref, computed } from "vue"

const page = usePage()

const { mobile } = useDisplay()
const theme = useTheme()

const themeFromStorage = localStorage.getItem("theme")
const themeMode = ref(themeFromStorage ?? "customLight")

watch(themeMode, (value) => {
  theme.global.name.value = value
  localStorage.setItem("theme", value)
})

const childs = computed(() => {
  return page.props.nav.projects.map((project) => {
    return {
      value: project.name,
      route: "dashboard.formative-actions",
      projectId: project.id,
    }
  })
})

const getFormativeActions = (projectId) => {
  router.get(`/dashboard/formative-actions/project/${projectId}`)
}
</script>

<template>
  <Head title="Dashboard" />
  <app-layout>
    <nav-bar-mobile v-model:theme="themeMode" v-if="mobile" />
    <nav-bar v-model:theme="themeMode" v-else>
      <template #formative-actions="{ pageRoute, openDrawer }">
        <v-list-group :value="pageRoute.value">
          <template #activator="{ props }">
            <v-list-item
              v-bind="props"
              :title="pageRoute.value"
              :prepend-icon="pageRoute.icon"
              :active="$page.url.includes(pageRoute.path)"
              @click="openDrawer"
            >
            </v-list-item>
          </template>
          <v-tooltip
            :text="child.value"
            v-for="child in childs"
            :key="child.value"
          >
            <template v-slot:activator="{ props }">
              <v-list-item
                v-bind="props"
                :title="child.value"
                :prepend-icon="child.icon"
                :active="page.props.project?.name === child.value"
                @click="getFormativeActions(child.projectId)"
              >
              </v-list-item>
            </template>
          </v-tooltip>
        </v-list-group>
      </template>
    </nav-bar>
    <slot />
  </app-layout>
</template>
