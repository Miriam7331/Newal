<script setup>
import { ref, onBeforeMount, computed } from "vue"
import { usePage } from "@inertiajs/vue3"
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

const rail = ref(false)
const open = ref([])
const lastOpen = ref([])

onBeforeMount(() => {
  routes.value.forEach((e) => {
    if (e.hasOwnProperty("childs") && page.url.includes(e.path)) {
      open.value.push(e.value)
    }
  })

  // Ejemplo de "set" en tu computed
  routes.value = { newValue: page.props?.auth.user.name }
})

const closeAll = () => {
  if (!rail.value) {
    lastOpen.value = open.value
    open.value = []
    rail.value = true
  } else {
    rail.value = false
    open.value = lastOpen.value
  }
}

const openDrawer = () => {
  if (rail.value) {
    rail.value = false
  }
}
</script>

<template>
  <v-navigation-drawer theme="appTheme" elevation="6" :rail="rail" permanent>
    <v-list>
      <v-list-item>
        <v-img
          v-if="!rail"
          aspect-ratio="16/9"
          width="70%"
          class="mx-auto"
          cover
          :src="appLogo"
        />
      </v-list-item>
    </v-list>

    <!-- Iteramos las rutas -->
    <template v-for="pageRoute in routes" :key="pageRoute.value">
      <v-divider />

      <!-- 1) Si NO tiene route/path ni childs, simplemente pintamos un <v-list-item> normal (caso genérico) -->
      <v-list
        v-if="
          !pageRoute.hasOwnProperty('route') &&
          !pageRoute.hasOwnProperty('path')
        "
        nav
      >
        <!-- Aquí igual se quiere un slot dinámico también, si procediera -->
        <v-list-item :title="pageRoute.value" :prepend-icon="pageRoute.icon" />
      </v-list>

      <!-- 2) Si la ruta puede tener childs: -->
      <template v-else-if="pageRoute.hasOwnProperty('childs')">
        <v-list v-model:opened="open" nav>
          <v-list-group :value="pageRoute.value">
            <template #activator="{ props }">
              <!-- Comprobar si hay slot con el nombre del padre -->
              <template v-if="$slots[pageRoute.id]">
                <!-- slot personalizado para el padre -->
                <slot
                  :name="pageRoute.id"
                  :pageRoute="pageRoute"
                  :props="props"
                  :openDrawer="openDrawer"
                />
              </template>
              <!-- Si NO hay slot, uso el comportamiento por defecto -->
              <template v-else>
                <v-list-item
                  v-bind="props"
                  :title="pageRoute.value"
                  :prepend-icon="pageRoute.icon"
                  :active="$page.url.includes(pageRoute.path)"
                  @click="openDrawer"
                />
              </template>
            </template>

            <!-- Pintamos cada uno de los childs -->
            <v-list-item
              v-for="child in pageRoute.childs"
              :key="child.value"
              :prepend-icon="child.icon"
              :title="child.value"
              :active="checkRoute(child.route)"
              :to="route(child.route)"
            >
            </v-list-item>
          </v-list-group>
        </v-list>
      </template>

      <!-- 3) Ruta normal con 'route' -->
      <template v-else>
        <v-list nav>
          <!-- Checamos si hay un slot con el nombre de pageRoute.value -->
          <template v-if="$slots[pageRoute.id]">
            <slot
              :name="pageRoute.id"
              :pageRoute="pageRoute"
              :openDrawer="openDrawer"
            />
          </template>
          <!-- Si no existe un slot, dibujamos el item por defecto -->
          <template v-else>
            <v-list-item
              :title="pageRoute.value"
              :prepend-icon="pageRoute.icon"
              :active="checkRoute(pageRoute.route)"
              :to="route(pageRoute.route)"
            />
          </template>
        </v-list>
      </template>
    </template>
  </v-navigation-drawer>

  <!-- AppBar normal -->
  <v-app-bar elevation="1">
    <v-app-bar-nav-icon @click="closeAll" />
    <slot name="top">
      <v-toolbar-title />
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
      />
    </slot>
  </v-app-bar>
</template>
