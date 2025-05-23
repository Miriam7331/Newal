import { computed } from "vue"
import { usePage } from "@inertiajs/vue3"

const page = usePage()

/*
 * Parent routes will have "path" parameters instead or "route" ones
 * "routes" will be used in their childs
 */

// Route with childs example:
/*
  {
    value: "title",
    icon: "icon",
    path: "relative-path",
    childs: [
      {
        value: "title",
        route: "route name",
        icon: "icon",
      },
      {
        value: "title",
        route: "route name",
        icon: "icon",
      },
    ],
  },
  */

const routesArray = [
  {
    value: "Usuario",
    icon: "mdi-account-circle",
  },
  {
    value: "Inscripciones",
    route: "dashboard.inscriptions",
    icon: "mdi-account-check",
  },
  {
    value: "Alumnos",
    route: "dashboard.students",
    icon: "mdi-account-multiple",
  },
  {
    value: "Acciones Formativas",
    route: "dashboard.formative-actions",
    icon: "mdi-book-education",
    id: "formative-actions",
  },
  {
    value: "Profesores",
    route: "dashboard.teachers",
    icon: "mdi-account-tie",
    admin: true,
  },
  {
    value: "Entidades",
    route: "dashboard.entities",
    icon: "mdi-office-building",
    admin: true,
  },
  {
    value: "Empresas",
    route: "dashboard.companies",
    icon: "mdi-account-hard-hat",
  },
  {
    value: "Usuarios",
    route: "dashboard.users",
    icon: "mdi-account-group",
    admin: true,
  },
  {
    value: "Configuración",
    route: "dashboard.settings",
    icon: "mdi-cog",
    admin: true,
  },
  {
    value: "Cerrar sesión",
    icon: "mdi-logout-variant",
    route: "logout",
  },
]

export const routes = computed({
  get() {
    if (!page.props?.auth.user.admin) {
      return routesArray.filter((route) => {
        if (route.admin) {
          return false
        }
        return true
      })
    }
    return routesArray
  },
  set({
    newValue,
    element = 1,
    key = "value",
    child = false,
    childElement = 1,
  }) {
    let handler = routesArray

    if (child) {
      handler[element - 1].childs[childElement - 1][key] = newValue
    } else {
      handler[element - 1][key] = newValue
    }

    return handler
  },
})
