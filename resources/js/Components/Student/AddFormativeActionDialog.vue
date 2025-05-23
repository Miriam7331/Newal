<script setup>
import { router, usePage } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"
import rules from "@/Utils/rules"
import RemoveFormativeActionDialog from "@/Components/Student/RemoveFormativeActionDialog.vue"
import AddDiplomaDialog from "@/Components/Diploma/AddDiplomaDialog.vue"
import RemoveDiplomaDialog from "../Diploma/RemoveDiplomaDialog.vue"

const props = defineProps(["show", "item", "type", "endPoint"])
const emit = defineEmits(["closeDialog", "reloadItems"])
const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("closeDialog", value)
  },
})
const item = ref()
const selectedFormativeAction = ref()
const formativeActions = computed(() =>
  usePage().props.formativeActions?.filter((formativeAction) => {
    return !item.value.formative_actions.some(
      (item) => item.id === formativeAction.id
    )
  })
)

const selectedFormativeActionDialog = ref()
const showRemoveFormativeActionDialog = ref(false)

const openRemoveFormativeActionFormDialog = (formativeActionId) => {
  selectedFormativeActionDialog.value = formativeActionId.id
  showRemoveFormativeActionDialog.value = true
}

const loadItems = () => {
  router.reload({
    only: ["formativeActions", "flash", "errors"],
  })
}

watch(
  () => usePage().props.flash,
  (flash) => {
    if (flash.item) {
      if (flash.itemType === "student") {
        item.value = flash.item
      }
    }
  },
  { deep: true }
)
watch(dialogState, (value) => {
  if (value) {
    loadItems()
    item.value = props.item
  } else {
    emit("reloadItems")
  }
})

const showModules = ref({})

const toggleModules = (id) => {
  if (showModules.value[id] === undefined) {
    showModules.value[id] = true
  } else {
    showModules.value[id] = !showModules.value[id]
  }
}

const addFormativeAction = () => {
  router.post(
    `${props.endPoint}/${item.value.id}/add-formative-action`,
    {
      formative_actions_id: selectedFormativeAction.value,
      status: "Matriculado",
    },
    {
      only: ["formativeActions", "flash", "errors"],
      onSuccess: () => {
        selectedFormativeAction.value = null
      },
    }
  )
}
const removeFormativeAction = (formativeAction) => {
  router.post(
    `${props.endPoint}/${item.value.id}/remove-formative-action`,
    {
      formative_actions_id: formativeAction.id,
    },
    {
      only: ["formativeActions", "flash", "errors"],
    }
  )
}
const changeFormativeActionStatus = (formativeAction, status) => {
  router.post(
    `${props.endPoint}/${item.value.id}/change-formative-action-status`,
    {
      formative_actions_id: formativeAction.id,
      status: status,
    },
    {
      only: ["formativeActions", "flash", "errors"],
      onSuccess: () => {
        if (formativeAction.modules_id && formativeAction.end_date) {
          const module = formativeAction.module
          changeModuleStatus(formativeAction, module, status)
        }
      },
    }
  )
}
const changeModuleStatus = (formativeAction, module, status = null) => {
  router.post(
    `${props.endPoint}/${item.value.id}/change-module-status`,
    {
      formative_actions_id: formativeAction.id,
      modules_id: module.id,
      status: status,
    },
    {
      only: ["formativeActions", "flash", "errors"],
    }
  )
}
const mergeCourseModulesWithStatus = (formativeAction, student) => {
  return formativeAction.course.modules.map((module) => {
    let studentModule = student.modules.find(
      (studentModule) =>
        studentModule.id === module.id &&
        studentModule.pivot.formative_actions_id === formativeAction.id
    )
    if (studentModule) {
      return { ...module, status: studentModule.pivot.status }
    } else {
      studentModule = student.modules.find(
        (studentModule) =>
          studentModule.id === module.id &&
          studentModule.pivot.formative_actions_id !== formativeAction.id &&
          studentModule.pivot.status === "Apto"
      )
      if (studentModule) {
        return { ...module, status: "Convalidable" }
      }
    }
    return module
  })
}

const mergeModuleWithStatus = (formativeAction, student) => {
  let studentModule = student.modules.find(
    (studentModule) =>
      studentModule.id === formativeAction.module.id &&
      studentModule.pivot.formative_actions_id === formativeAction.id
  )
  if (studentModule) {
    return { ...formativeAction.module, status: studentModule.pivot.status }
  } else {
    studentModule = student.modules.find(
      (studentModule) =>
        studentModule.id === formativeAction.module.id &&
        studentModule.pivot.formative_actions_id !== formativeAction.id &&
        studentModule.pivot.status === "Apto"
    )
    if (studentModule) {
      return { ...formativeAction.module, status: "Convalidable" }
    }
  }
  return formativeAction.module
}

const updatedFormativeActions = computed(() => {
  return item.value.formative_actions
    .map((formativeAction) => {
      if (formativeAction.course) {
        return {
          ...formativeAction,
          course: {
            ...formativeAction.course,
            modules: mergeCourseModulesWithStatus(formativeAction, item.value),
          },
        }
      } else if (formativeAction.module) {
        return {
          ...formativeAction,
          module: mergeModuleWithStatus(formativeAction, item.value),
        }
      }
    })
    .sort((a, b) => b.pivot.id - a.pivot.id)
})

const showAddDiplomaDialog = ref(false)

const openAddDiplomaDialog = (formativeAction) => {
  showAddDiplomaDialog.value = true
  selectedFormativeActionDialog.value = formativeAction
}

const downloadDiploma = (formativeAction) => {
  window.open(
    `students/download-diploma/${item.value.id}/${formativeAction.id}`,
    "_blank"
  )
}

const showRemoveDiplomaDialog = ref(false)

const openRemoveDiplomaDialog = (formativeAction) => {
  showRemoveDiplomaDialog.value = true
  selectedFormativeActionDialog.value = formativeAction
}
</script>
<template>
  <remove-formative-action-dialog
    :show="showRemoveFormativeActionDialog"
    :studentId="item?.id"
    :formativeActionId="selectedFormativeActionDialog"
    @closeDialog="showRemoveFormativeActionDialog = false"
    @reloadItems="loadItems()"
  />
  <add-diploma-dialog
    :show="showAddDiplomaDialog"
    :student="item"
    :item="selectedFormativeActionDialog"
    :end-point="endPoint"
    @closeDialog="showAddDiplomaDialog = false"
    @reloadItems="loadItems()"
  />
  <remove-diploma-dialog
    :show="showRemoveDiplomaDialog"
    :student-id="item?.id"
    :formative-action-id="selectedFormativeActionDialog?.id"
    @closeDialog="showRemoveDiplomaDialog = false"
    @reloadItems="loadItems()"
  />
  <v-dialog scrollable v-model="dialogState" width="1024">
    <v-card>
      <v-card-title>
        <span class="text-h5">Acciones formativas del alumno</span>
      </v-card-title>
      <v-divider></v-divider>
      <v-card-text>
        <v-container>
          <v-row
            class="align-center justify-center my-3 mx-1 elevation-6 rounded pa-5'"
          >
            <v-col cols="12">
              <v-autocomplete
                class="ma-3"
                label="Añadir acción formativa"
                v-model="selectedFormativeAction"
                @update:modelValue="addFormativeAction"
                :items="formativeActions"
                :item-title="
                  (item) =>
                    (item.course?.name ?? item.module?.name) +
                    ' (' +
                    (item.course?.code ?? item.module?.code) +
                    ') - ' +
                    item.code
                "
                item-value="id"
                :rules="[rules.ruleRequired]"
                hide-details
                clearable
              >
              </v-autocomplete>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row
            v-if="item && item.formative_actions"
            class="align-center justify-center text-center my-3 mx-1 pa-2"
          >
            <v-col cols="12">
              <span class="text-h5">Acciones formativas</span>
            </v-col>
          </v-row>
          <div
            v-if="updatedFormativeActions"
            v-for="formativeAction in updatedFormativeActions"
            :key="formativeAction.id"
          >
            <div
              class="elevation-6 rounded"
              :class="{
                'bg-green': formativeAction.pivot.status == 'Apto',
                'bg-red': formativeAction.pivot.status == 'No apto',
                'bg-orange': formativeAction.pivot.status == 'Convalidado',
                'bg-blue-grey': formativeAction.pivot.status == 'Abandonado',
              }"
            >
              <v-row class="ma-0 mb-3 px-2 pt-2 pt-md-0">
                <v-col cols="12" md="3" class="d-flex align-center">
                  <v-row class="py-3">
                    <v-col class="pa-0" cols="12">
                      <v-chip
                        v-if="formativeAction.module?.status != 'Convalidable'"
                        class="w-100 justify-center"
                      >
                        <v-icon start icon="mdi-certificate"></v-icon>
                        {{ formativeAction.pivot.status }}
                      </v-chip>
                      <v-chip
                        v-else
                        class="w-100 justify-center"
                        color="orange"
                      >
                        <v-icon start icon="mdi-certificate"></v-icon>
                        {{ formativeAction.module.status }}
                      </v-chip>
                    </v-col>
                    <v-col
                      class="pa-0 pt-1"
                      cols="12"
                      v-if="formativeAction.pivot.status != 'Matriculado'"
                    >
                      <v-chip class="w-100 justify-center">
                        <v-icon start icon="mdi-calendar"></v-icon>
                        {{ formativeAction.end_date }}
                      </v-chip>
                    </v-col>
                  </v-row>
                </v-col>
                <v-col>
                  <div
                    class="d-flex flex-column h-100 align-center justify-center text-center"
                  >
                    <div class="w-100">
                      {{
                        formativeAction.course?.name ??
                        formativeAction.module?.name
                      }}
                      ({{
                        formativeAction.course?.code ??
                        formativeAction.module?.code
                      }}) -
                      {{ formativeAction.code }}
                    </div>
                    <v-btn
                      density="compact"
                      color="green"
                      text="Convalidar"
                      class="mt-2"
                      v-if="formativeAction.module?.status == 'Convalidable'"
                      @click="
                        changeFormativeActionStatus(
                          formativeAction,
                          'Convalidado'
                        )
                      "
                    >
                    </v-btn>
                  </div>
                </v-col>
                <v-col cols="12" md="2">
                  <div class="d-flex justify-center h-100 align-center">
                    <v-btn
                      icon
                      v-if="formativeAction.pivot.status == 'Matriculado'"
                      density="compact"
                      variant="text"
                      @click="
                        changeFormativeActionStatus(formativeAction, 'Apto')
                      "
                    >
                      <v-icon>mdi-check</v-icon>
                      <v-tooltip activator="parent">Aprobar</v-tooltip>
                    </v-btn>
                    <v-btn
                      icon
                      v-if="formativeAction.pivot.status == 'Matriculado'"
                      density="compact"
                      variant="text"
                      @click="
                        changeFormativeActionStatus(formativeAction, 'No apto')
                      "
                    >
                      <v-icon>mdi-close</v-icon>
                      <v-tooltip activator="parent">Suspender</v-tooltip>
                    </v-btn>
                    <v-btn
                      icon
                      v-if="formativeAction.pivot.status == 'Matriculado'"
                      density="compact"
                      variant="text"
                      @click="
                        changeFormativeActionStatus(
                          formativeAction,
                          'Abandonado'
                        )
                      "
                    >
                      <v-icon>mdi-exit-run</v-icon>
                      <v-tooltip activator="parent">Abandonado</v-tooltip>
                    </v-btn>
                    <v-btn
                      v-if="!formativeAction.pivot.file"
                      icon
                      density="compact"
                      variant="text"
                      @click="openAddDiplomaDialog(formativeAction)"
                    >
                      <v-icon>mdi-file-document-plus</v-icon>
                      <v-tooltip activator="parent">Añadir diploma</v-tooltip>
                    </v-btn>
                    <v-btn
                      v-if="formativeAction.pivot.file"
                      icon
                      density="compact"
                      variant="text"
                      @click="downloadDiploma(formativeAction)"
                    >
                      <v-icon>mdi-file-download</v-icon>
                      <v-tooltip activator="parent"
                        >Descargar diploma</v-tooltip
                      >
                    </v-btn>
                    <v-btn
                      v-if="formativeAction.pivot.file"
                      icon
                      density="compact"
                      variant="text"
                      @click="openRemoveDiplomaDialog(formativeAction)"
                    >
                      <v-icon>mdi-file-document-remove</v-icon>
                      <v-tooltip activator="parent">Quitar diploma</v-tooltip>
                    </v-btn>

                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      @click="
                        openRemoveFormativeActionFormDialog(formativeAction)
                      "
                    >
                      <v-icon>mdi-delete</v-icon>
                      <v-tooltip activator="parent">Eliminar</v-tooltip>
                    </v-btn>

                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      v-if="formativeAction.course?.modules"
                      @click="toggleModules(formativeAction.id)"
                    >
                      <v-icon v-if="showModules[formativeAction.id]"
                        >mdi-chevron-up</v-icon
                      >
                      <v-icon v-else>mdi-chevron-down</v-icon>

                      <v-tooltip activator="parent">Ver módulos</v-tooltip>
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </div>
            <div
              v-if="formativeAction.course?.modules"
              v-for="module in formativeAction.course.modules"
              v-show="showModules[formativeAction.id]"
              class="elevation-6 rounded"
              :class="{
                'bg-green': module.status == 'Apto',
                'bg-red': module.status == 'No apto',
                'bg-orange': module.status == 'Convalidado',
              }"
            >
              <v-row class="ma-0 mb-3 px-2 pt-2 pt-md-0">
                <v-col cols="12" md="3">
                  <p>{{ module.name + "(" + module.code + ")" }}</p>
                </v-col>
                <v-col class="text-center">
                  <v-btn
                    density="compact"
                    color="green"
                    text="Convalidar"
                    v-if="
                      formativeAction.pivot.status == 'Matriculado' &&
                      module.status == 'Convalidable'
                    "
                    @click="
                      changeModuleStatus(formativeAction, module, 'Convalidado')
                    "
                  >
                  </v-btn>
                  <div
                    v-else-if="module.status != 'Convalidable'"
                    class="d-flex flex-column h-100 align-center justify-center text-center"
                  >
                    <div class="w-100">{{ module.status }}</div>
                  </div>
                </v-col>
                <v-col cols="12" md="2">
                  <div
                    class="d-flex justify-center h-100 align-center"
                    v-if="
                      formativeAction.pivot.status == 'Matriculado' &&
                      (!module.status || module.status == 'Convalidable')
                    "
                  >
                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      @click="
                        changeModuleStatus(formativeAction, module, 'Apto')
                      "
                    >
                      <v-icon>mdi-check</v-icon>
                      <v-tooltip activator="parent">Aprobar</v-tooltip>
                    </v-btn>
                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      @click="
                        changeModuleStatus(formativeAction, module, 'No apto')
                      "
                    >
                      <v-icon>mdi-close</v-icon>
                      <v-tooltip activator="parent">Suspender</v-tooltip>
                    </v-btn>
                  </div>
                  <div
                    class="d-flex justify-center h-100 align-center"
                    v-else-if="formativeAction.pivot.status == 'Matriculado'"
                  >
                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      @click="changeModuleStatus(formativeAction, module, null)"
                    >
                      <v-icon>mdi-restart</v-icon>
                      <v-tooltip activator="parent">Resetear</v-tooltip>
                    </v-btn>
                  </div>
                </v-col>
              </v-row>
            </div>
          </div>
        </v-container>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions class="d-flex justify-center">
        <v-btn color="red-darken-1" variant="text" @click="dialogState = false">
          Cerrar
        </v-btn>
        <v-btn
          v-if="type == 'create'"
          color="blue-darken-1"
          :disabled="!form"
          variant="text"
          @click="submit"
        >
          Guardar
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
