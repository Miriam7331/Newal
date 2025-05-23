<script setup>
import { usePage } from "@inertiajs/vue3"
import { ref, onBeforeMount, computed } from "vue"
import { checkRoute } from "@/Utils/url"
import { routes } from "@/Utils/routes"
import appLogo from "@/../assets/Logo_Newal_blanco.png"

const page = usePage()
const props = defineProps(["theme"])
const emit = defineEmits(["update:theme"])

const theme = computed({
  get: () => props.theme,
  set: (value) => {
    emit("update:theme", value)
  },
})

const drawer = ref(false)
const open = ref([])

onBeforeMount(() => {
  routes.value.forEach((e) => {
    if (e.hasOwnProperty("childs") && usePage().url.includes(e.path)) {
      open.value.push(e.value)
    }
  })
  routes.value = { newValue: usePage().props?.auth.user.name }
})
</script>

<template>
  <v-navigation-drawer theme="appTheme" v-model="drawer" temporary>
    <v-list>
      <v-list-item>
        <v-img
          aspect-ratio="16/9"
          width="70%"
          class="mx-auto"
          cover
          :src="appLogo"
        ></v-img>
      </v-list-item>
    </v-list>
    <template v-for="pageRoute in routes">
      <v-divider></v-divider>
      <v-list
        v-if="
          !pageRoute.hasOwnProperty('route') &&
          !pageRoute.hasOwnProperty('path')
        "
        nav
      >
        <v-list-item
          :title="pageRoute.value"
          :prepend-icon="pageRoute.icon"
        ></v-list-item>
      </v-list>
      <template v-else>
        <v-list
          v-if="pageRoute.hasOwnProperty('childs')"
          v-model:opened="open"
          nav
        >
          <v-list-group :value="pageRoute.value">
            <template v-slot:activator="{ props }">
              <v-list-item
                v-bind="props"
                :title="pageRoute.value"
                :prepend-icon="pageRoute.icon"
                :active="$page.url.includes(pageRoute.path)"
              ></v-list-item>
            </template>
            <v-list-item
              v-for="child in pageRoute.childs"
              :prepend-icon="child.icon"
              :title="child.value"
              :active="checkRoute(child.route)"
              :to="route(child.route)"
            ></v-list-item>
          </v-list-group>
        </v-list>
        <v-list v-else nav>
          <v-list-item
            :title="pageRoute.value"
            :prepend-icon="pageRoute.icon"
            :active="checkRoute(pageRoute.route)"
            :to="route(pageRoute.route)"
          ></v-list-item>
        </v-list>
      </template>
    </template>
  </v-navigation-drawer>
  <v-app-bar elevation="1">
    <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
    <slot name="top">
      <v-toolbar-title></v-toolbar-title>
    </slot>
    <slot name="top-right">
      <v-switch
        inset
        color="info"
        v-model="theme"
        false-value="customLight"
        true-value="customDark"
        class="mr-5"
        hide-details
      ></v-switch>
    </slot>
  </v-app-bar>
</template>
