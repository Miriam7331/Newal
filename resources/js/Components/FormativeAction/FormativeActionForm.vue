<script setup>
import SectorFormDialog from "@/Components/Settings/Sector/FormDialog.vue"
import TeacherFormDialog from "@/Components/Teacher/FormDialog.vue"
import { ref, watch, computed } from "vue"
import { router, useForm, usePage } from "@inertiajs/vue3"
import rules from "@/Utils/rules"
import { locationItems, typeItems, typeReceivers } from "@/Utils/arrays"
import AutocompleteServer from "@/Components/AutocompleteServer.vue"

const props = defineProps(["show", "item", "type", "endPoint"])
const emit = defineEmits(["closeDialog", "reloadItems", "update:type"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => emit("closeDialog", value),
})

const type = computed({
  get: () => props.type,
  set: (value) => emit("update:type", value),
})

const selectedModule = ref(null)
const selectedCourse = ref(null)
const selectedUser = ref(null)
const selectedModuleComp = ref(null)
const ModuleSelector = ref(false)
const sectors = ref([])
const teachers = ref([])
const centers = ref([])
const entities = ref([])
const projects = ref([])

const item = ref()

const form = ref(false)

const loadItems = () => {
  router.reload({
    only: [
      "users",
      "sectors",
      "teachers",
      "centers",
      "entities",
      "flash",
      "errors",
      "projects",
    ],
    onSuccess: (pageProps) => {
      sectors.value = pageProps.props.sectors
      teachers.value = pageProps.props.teachers
      centers.value = pageProps.props.centers
      entities.value = pageProps.props.entities
      projects.value = pageProps.props.projects
    },
  })
}

const users = computed(() => {
  return usePage().props.users?.filter((user) => {
    return !item.value?.users.some((item) => item.id === user.id)
  })
})
const userIsAdmin = computed(() => {
  return usePage().props?.auth.user.admin
})

const userHasCenters = computed(() => {
  return usePage().props?.auth.user.centers?.length > 0 || userIsAdmin.value
})

const addUser = () => {
  if (selectedUser.value) {
    router.post(
      `${props.endPoint}/${item.value.id}/add-user`,
      {
        users_id: selectedUser.value,
      },
      {
        only: ["entities", "users", "flash", "errors"],
        onSuccess: () => {
          selectedUser.value = null
        },
      }
    )
  }
}

const addModuleComp = () => {
  if (selectedModuleComp.value) {
    router.post(
      `${props.endPoint}/${item.value.id}/add-module`,
      {
        modules_id: selectedModuleComp.value.id,
      },
      {
        only: ["entities", "users", "flash", "errors"],
        onSuccess: () => {
          selectedModuleComp.value = null
        },
      }
    )
  }
}

const removeUser = (user) => {
  router.post(
    `${props.endPoint}/${item.value.id}/remove-user`,
    {
      users_id: user.id,
    },
    {
      only: ["entities", "users", "flash", "errors"],
    }
  )
}

const removeModuleComp = (module) => {
  router.post(
    `${props.endPoint}/${item.value.id}/remove-module`,
    {
      modules_id: module.id,
    },
    {
      only: ["entities", "users", "flash", "errors"],
    }
  )
}

const formData = useForm({
  code: "",
  islands: null,
  courses_id: null,
  modules_id: null,
  sectors_id: null,
  teachers_id: null,
  centers_id: null,
  entities_id: null,
  projects_id: null,
  min_quota: null,
  min_quota_to_end: null,
  max_quota: null,
  schedule: "",
  start_date: "",
  max_inscription_date: "",
  end_date: "",
  price: 0,
  type: "",
  requirements: "",
  receiver: "",
})

if (dialogState) {
  loadItems()
  if (type.value === "edit") {
    item.value = props.item
    Object.assign(formData, item.value)
    selectedModule.value = item.value.module
    selectedCourse.value = item.value.course
    if (formData.modules_id) {
      ModuleSelector.value = true
    } else {
      ModuleSelector.value = false
    }
    formData.islands = formData.islands.split(",")
  } else if (type.value === "create") {
    formData.code = ""
    formData.islands = null
    formData.courses_id = null
    formData.modules_id = null
    formData.sectors_id = null
    formData.teachers_id = null
    formData.centers_id = null
    formData.min_quota = null
    formData.min_quota_to_end = null
    formData.max_quota = null
    formData.schedule = ""
    formData.start_date = ""
    formData.max_inscription_date = ""
    formData.end_date = ""
    formData.price = 0
    formData.type = ""
    formData.receiver = ""
    formData.requirements = ""
    formData.entities_id = null
    formData.projects_id = null

    selectedModule.value = null
    selectedCourse.value = null
    ModuleSelector.value = false
  }
}

watch(
  () => usePage().props.flash,
  (flash) => {
    if (flash.item) {
      if (flash.itemType === "formativeAction") {
        item.value = flash.item
        Object.assign(formData, item.value)
        formData.islands = formData.islands.split(",")
      }

      if (flash.itemType === "course") {
        formData.courses_id = flash.item.id
      }

      if (flash.itemType === "module") {
        formData.modules_id = flash.item.id
      }

      if (flash.itemType === "sector") {
        formData.sectors_id = flash.item.id
      }

      if (flash.itemType === "teacher") {
        formData.teachers_id = flash.item.id
      }
    }
  },
  { deep: true }
)

const submit = () => {
  formData.islands = formData.islands.join(",")

  if (selectedCourse.value) {
    formData.modules_id = null
    formData.courses_id = selectedCourse.value.id
  } else {
    formData.modules_id = selectedModule.value.id
    formData.courses_id = null
  }

  if (type.value === "edit") {
    formData.put(`${props.endPoint}/${item.value.id}`, {
      only: [
        "users",
        "sectors",
        "teachers",
        "centers",
        "entities",
        "flash",
        "errors",
        "projects",
      ],
    })
  } else if (type.value === "create") {
    formData.post(props.endPoint, {
      only: [
        "users",
        "sectors",
        "teachers",
        "centers",
        "entities",
        "flash",
        "errors",
        "projects",
      ],
      onSuccess: () => {
        type.value = "edit"
      },
    })
  }
}

const showSectorFormDialog = ref(false)
const showTeacherFormDialog = ref(false)
</script>
<template>
  <sector-form-dialog
    :show="showSectorFormDialog"
    @closeDialog="showSectorFormDialog = false"
    type="create"
    @reloadItems="loadItems()"
    endPoint="/dashboard/sectors"
  />
  <teacher-form-dialog
    :show="showTeacherFormDialog"
    @closeDialog="showTeacherFormDialog = false"
    type="create"
    @reloadItems="loadItems()"
    endPoint="/dashboard/teachers"
  />

  <v-form v-model="form" @submit.prevent="submit">
    <v-row>
      <v-col cols="12" sm="6">
        <v-text-field
          label="Código*"
          v-model="formData.code"
          :rules="[(v) => rules.ruleMaxLength(v, 45), rules.ruleRequired]"
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6">
        <v-autocomplete
          clearable
          label="Islas*"
          v-model="formData.islands"
          :rules="[rules.ruleRequired]"
          :items="locationItems"
          multiple
        ></v-autocomplete>
      </v-col>
      <v-col cols="12" sm="6">
        <autocomplete-server
          v-if="!ModuleSelector"
          :item-title="(item) => item.name + ' (' + item.code + ')'"
          label="Curso*"
          :rules="[rules.ruleRequired]"
          v-model="selectedCourse"
          end-point="/dashboard/courses"
        >
          <template v-slot:append v-if="userHasCenters">
            <v-switch
              v-model="ModuleSelector"
              label="Curso"
              @update:model-value="selectedCourse = null"
              hide-details
            ></v-switch>
          </template>
        </autocomplete-server>
        <autocomplete-server
          v-else
          :item-title="(item) => item.name + ' (' + item.code + ')'"
          label="Módulo*"
          :rules="[rules.ruleRequired]"
          v-model="selectedModule"
          end-point="/dashboard/modules"
        >
          <template v-slot:append v-if="userHasCenters">
            <v-switch
              v-model="ModuleSelector"
              label="Módulo"
              @update:model-value="selectedModule = null"
              hide-details
            ></v-switch>
          </template>
        </autocomplete-server>
      </v-col>
      <v-col cols="12" sm="6">
        <v-autocomplete
          clearable
          item-value="id"
          item-title="name"
          label="Sector*"
          v-model="formData.sectors_id"
          :rules="[rules.ruleRequired]"
          :items="sectors"
        >
          <template v-slot:prepend v-if="userHasCenters">
            <v-btn
              icon="mdi-plus-circle"
              @click="showSectorFormDialog = true"
            ></v-btn>
          </template>
        </v-autocomplete>
      </v-col>
      <v-col cols="12" sm="6">
        <v-autocomplete
          clearable
          item-value="id"
          item-title="name"
          label="Centro*"
          v-model="formData.centers_id"
          :rules="[rules.ruleRequired]"
          :items="centers"
        >
        </v-autocomplete>
      </v-col>
      <v-col cols="12" sm="6">
        <v-autocomplete
          clearable
          item-value="id"
          item-title="name"
          label="Entidad*"
          v-model="formData.entities_id"
          :rules="[rules.ruleRequired]"
          :items="entities"
        >
        </v-autocomplete>
      </v-col>
      <v-col cols="12" sm="6">
        <v-autocomplete
          clearable
          item-value="id"
          :item-title="(item) => item.name + ' ' + item.surnames"
          label="Profesor"
          v-model="formData.teachers_id"
          :items="teachers"
        >
          <template v-slot:prepend v-if="userHasCenters">
            <v-btn
              icon="mdi-plus-circle"
              @click="showTeacherFormDialog = true"
            ></v-btn>
          </template>
        </v-autocomplete>
      </v-col>
      <v-col cols="12" sm="6">
        <v-select
          label="Tipo*"
          v-model="formData.type"
          :rules="[(v) => rules.ruleMaxLength(v, 45), rules.ruleRequired]"
          :items="typeItems"
          required
        ></v-select>
      </v-col>
      <v-col cols="12" sm="6">
        <v-select
          label="Dirigido a*"
          v-model="formData.receiver"
          :rules="[(v) => rules.ruleMaxLength(v, 45), rules.ruleRequired]"
          :items="typeReceivers"
          required
        ></v-select>
      </v-col>
      <v-col cols="12" sm="6">
        <v-autocomplete
          clearable
          item-value="id"
          item-title="name"
          label="Proyecto*"
          v-model="formData.projects_id"
          :rules="[rules.ruleRequired]"
          :items="projects"
        ></v-autocomplete>
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          label="Fecha de inicio*"
          v-model="formData.start_date"
          :rules="[rules.ruleRequired]"
          type="date"
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          label="Fecha máxima de matriculación*"
          v-model="formData.max_inscription_date"
          :rules="[rules.ruleRequired]"
          type="date"
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          label="Fecha de finalización*"
          v-model="formData.end_date"
          :rules="[rules.ruleRequired]"
          type="date"
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          label="Cupo mínimo*"
          v-model="formData.min_quota"
          :rules="[rules.ruleRequired]"
          type="number"
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          label="Cupo mínimo para finalizar*"
          v-model="formData.min_quota_to_end"
          :rules="[rules.ruleRequired]"
          type="number"
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          label="Cupo máximo*"
          v-model="formData.max_quota"
          :rules="[rules.ruleRequired]"
          type="number"
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          label="Horario*"
          v-model="formData.schedule"
          :rules="[(v) => rules.ruleTimeRange(v), rules.ruleRequired]"
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6">
        <v-text-field
          label="Precio"
          v-model="formData.price"
          :rules="[rules.ruleNumber, rules.ruleRequired]"
        ></v-text-field>
      </v-col>

      <v-col cols="12">
        <v-textarea
          label="Requisitos"
          v-model="formData.requirements"
        ></v-textarea>
      </v-col>
    </v-row>
  </v-form>
  <div class="d-flex justify-center">
    <v-btn
      color="blue-darken-1"
      :disabled="!form"
      variant="text"
      @click="submit"
    >
      Guardar
    </v-btn>
  </div>

  <template v-if="type == 'edit'">
    <v-divider :thickness="3" class="mt-2"></v-divider>

    <v-row class="align-center justify-center my-3">
      <v-col class="justify-center align-center text-center" cols="12">
        <span class="text-h5">Coordinadoras/es</span>
      </v-col>
    </v-row>

    <v-row
      class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-5'"
    >
      <v-col cols="12">
        <v-autocomplete
          class="ma-3"
          label="Añadir usuario"
          v-model="selectedUser"
          :items="users"
          item-title="name"
          item-value="id"
          :rules="[]"
          @update:modelValue="addUser"
          hide-details
        >
        </v-autocomplete>
      </v-col>
    </v-row>

    <v-row
      v-if="item && item.users"
      v-for="user in item.users"
      class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-2'"
    >
      <v-col cols="12" md="10" class="my-3">
        {{ user.name }}
      </v-col>
      <v-col cols="12" md="1" class="text-center">
        <v-btn icon density="compact" variant="text" @click="removeUser(user)">
          <v-icon>mdi-delete</v-icon>
          <v-tooltip activator="parent">Eliminar</v-tooltip>
        </v-btn>
      </v-col>
    </v-row>

    <v-divider :thickness="3" class="mt-2"></v-divider>

    <v-row class="align-center justify-center my-3">
      <v-col class="justify-center align-center text-center" cols="12">
        <span class="text-h5">Módulos complementarios</span>
      </v-col>
    </v-row>

    <v-row
      class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-5'"
    >
      <v-col cols="12">
        <autocomplete-server
          :item-title="(item) => item.name + ' (' + item.code + ')'"
          label="Módulo*"
          v-model="selectedModuleComp"
          end-point="/dashboard/modules"
          @update:model-value="addModuleComp"
          hide-details
        >
        </autocomplete-server>
      </v-col>
    </v-row>

    <v-row
      v-if="item && item.modules"
      v-for="module in item.modules"
      class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-2'"
    >
      <v-col cols="12" md="10" class="my-3">
        ({{ module.code }}) {{ module.name }}
      </v-col>
      <v-col cols="12" md="1" class="text-center">
        <v-btn
          icon
          density="compact"
          variant="text"
          @click="removeModuleComp(module)"
        >
          <v-icon>mdi-delete</v-icon>
          <v-tooltip activator="parent">Eliminar</v-tooltip>
        </v-btn>
      </v-col>
    </v-row>
  </template>
</template>
