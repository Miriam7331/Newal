<script setup>
import { router, usePage } from "@inertiajs/vue3"
import { computed, watch, ref } from "vue"
import RemoveStudentDialog from "@/Components/FormativeAction/RemoveStudentDialog.vue"
import StudentFormDialog from "@/Components/Student/FormDialog.vue"
import rules from "@/Utils/rules"
import AddDiplomaDialog from "@/Components/Diploma/AddDiplomaDialog.vue"
import RemoveDiplomaDialog from "../Diploma/RemoveDiplomaDialog.vue"
import useAutocompleteServer from "@/Composables/useAutocompleteServer"

const { loadAutocompleteItems, loading, items, endPoint } =
  useAutocompleteServer()

endPoint.value = "/dashboard/students"

const props = defineProps(["show", "item", "type", "endPoint"])
const emit = defineEmits(["closeDialog", "reloadItems"])

const dialogState = computed({
  get: () => props.show,
  set: (value) => {
    emit("closeDialog", value)
  },
})

const item = ref()
const selectedStudent = ref()

const students = computed(() =>
  items.value?.filter((student) => {
    return !item.value.students.some((item) => item.id === student.id)
  })
)

const selectedStudentDialog = ref()
const showRemoveStudentDialog = ref(false)

watch(
  () => usePage().props.flash,
  (flash) => {
    if (flash.item) {
      if (flash.itemType === "formativeAction") {
        item.value = flash.item
      }
    }
  },
  { deep: true }
)

watch(dialogState, (value) => {
  if (value) {
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

const addStudent = () => {
  router.post(
    `${props.endPoint}/${selectedStudent.value}/add-formative-action`,
    {
      formative_actions_id: item.value.id,
      status: "Matriculado",
      origin: "formativeAction",
    },
    {
      only: ["flash", "errors"],
      onSuccess: () => {
        selectedStudent.value = null
      },
    }
  )
}

const openRemoveStudentFormDialog = (studentId) => {
  selectedStudentDialog.value = studentId
  showRemoveStudentDialog.value = true
}

const changeFormativeActionStatus = (student, status) => {
  router.post(
    `${props.endPoint}/${student.id}/change-formative-action-status`,
    {
      formative_actions_id: item.value.id,
      status: status,
      origin: "formativeAction",
    },
    {
      only: ["flash", "errors"],
      onSuccess: () => {
        if (item.value.modules_id && item.value.end_date) {
          const module = item.value.module
          changeModuleStatus(item.value, module, status)
        }
      },
    }
  )
}
const changeModuleStatus = (student, module, status = null) => {
  router.post(
    `${props.endPoint}/${student.id}/change-module-status`,
    {
      formative_actions_id: item.value.id,
      modules_id: module.id,
      status: status,
      origin: "formativeAction",
    },
    {
      only: ["flash", "errors"],
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

const updatedStudents = computed(() => {
  return item.value.students
    .map((student) => {
      if (item.value.course) {
        return {
          ...student,
          course: {
            ...item.value.course,
            modules: mergeCourseModulesWithStatus(item.value, student),
          },
        }
      } else if (item.value.module) {
        return {
          ...student,
          module: mergeModuleWithStatus(item.value, student),
        }
      }
    })
    .sort((a, b) => b.pivot.id - a.pivot.id)
})

const showStudentFormDialog = ref(false)
const studentFormDialogType = ref("create")

const showAddDiplomaDialog = ref(false)

const openAddDiplomaDialog = (student) => {
  showAddDiplomaDialog.value = true
  selectedStudentDialog.value = student
}

const downloadDiploma = (student) => {
  window.open(
    `students/download-diploma/${student.id}/${item.value.id}`,
    "_blank"
  )
}

const showRemoveDiplomaDialog = ref(false)

const openRemoveDiplomaDialog = (student) => {
  showRemoveDiplomaDialog.value = true
  selectedStudentDialog.value = student
}
</script>
<template>
  <student-form-dialog
    :show="showStudentFormDialog"
    @closeDialog="showStudentFormDialog = false"
    :type="studentFormDialogType"
    endPoint="/dashboard/students"
  />

  <remove-student-dialog
    :show="showRemoveStudentDialog"
    :studentId="selectedStudentDialog"
    :formativeActionId="item?.id"
    @closeDialog="showRemoveStudentDialog = false"
  />

  <add-diploma-dialog
    :show="showAddDiplomaDialog"
    :student="selectedStudentDialog"
    :item="item"
    origin="formativeAction"
    end-point="/dashboard/students"
    @closeDialog="showAddDiplomaDialog = false"
  />
  <remove-diploma-dialog
    :show="showRemoveDiplomaDialog"
    :student-id="selectedStudentDialog?.id"
    :formative-action-id="item?.id"
    origin="formativeAction"
    @closeDialog="showRemoveDiplomaDialog = false"
  />
  <v-dialog scrollable v-model="dialogState" width="1024">
    <v-card>
      <v-card-title>
        <span class="text-h5">Alumnos de la acción formativa</span>
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
                label="Añadir alumno"
                v-model="selectedStudent"
                @update:modelValue="addStudent"
                :items="students"
                :item-title="(item) => item.name + ' - ' + item.dni"
                item-value="id"
                :rules="[rules.ruleRequired]"
                @update:search="loadAutocompleteItems"
                :loading="loading"
                hide-details
                clearable
              >
                <template v-slot:prepend>
                  <v-btn
                    icon="mdi-plus-circle"
                    @click="showStudentFormDialog = true"
                  ></v-btn>
                </template>
              </v-autocomplete>
            </v-col>
          </v-row>
          <v-divider></v-divider>
          <v-row
            v-if="item && item.students"
            class="align-center justify-center text-center my-3 mx-1 pa-2"
          >
            <v-col cols="12">
              <span class="text-h5">Estudiantes</span>
            </v-col>
          </v-row>
          <div
            v-if="updatedStudents"
            v-for="student in updatedStudents"
            :key="student.id"
          >
            <div
              class="elevation-6 rounded"
              :class="{
                'bg-green': student.pivot.status == 'Apto',
                'bg-red': student.pivot.status == 'No apto',
                'bg-orange': student.pivot.status == 'Convalidado',
                'bg-blue-grey': student.pivot.status == 'Abandonado',
              }"
            >
              <v-row class="ma-0 mb-3 px-2 pt-2 pt-md-0">
                <v-col cols="12" md="3" class="d-flex align-center">
                  <v-row class="py-3">
                    <v-col class="pa-0" cols="12">
                      <v-chip
                        v-if="student.module?.status != 'Convalidable'"
                        class="w-100 justify-center"
                      >
                        <v-icon start icon="mdi-certificate"></v-icon>
                        {{ student.pivot.status }}
                      </v-chip>
                      <v-chip
                        v-else
                        class="w-100 justify-center"
                        color="orange"
                      >
                        <v-icon start icon="mdi-certificate"></v-icon>
                        {{ student.module.status }}
                      </v-chip>
                    </v-col>
                  </v-row>
                </v-col>
                <v-col>
                  <div
                    class="d-flex flex-column h-100 align-center justify-center text-center"
                  >
                    <div class="w-100">
                      {{ student.name }} -
                      {{ student.dni }}
                    </div>
                    <v-btn
                      density="compact"
                      color="green"
                      text="Convalidar"
                      class="mt-2"
                      v-if="student.module?.status == 'Convalidable'"
                      @click="
                        changeFormativeActionStatus(student, 'Convalidado')
                      "
                    >
                    </v-btn>
                  </div>
                </v-col>
                <v-col cols="12" md="2">
                  <div class="d-flex justify-center h-100 align-center">
                    <v-btn
                      icon
                      v-if="student.pivot.status == 'Matriculado'"
                      density="compact"
                      variant="text"
                      @click="changeFormativeActionStatus(student, 'Apto')"
                    >
                      <v-icon>mdi-check</v-icon>
                      <v-tooltip activator="parent">Aprobar</v-tooltip>
                    </v-btn>
                    <v-btn
                      icon
                      v-if="student.pivot.status == 'Matriculado'"
                      density="compact"
                      variant="text"
                      @click="changeFormativeActionStatus(student, 'No apto')"
                    >
                      <v-icon>mdi-close</v-icon>
                      <v-tooltip activator="parent">Suspender</v-tooltip>
                    </v-btn>
                    <v-btn
                      icon
                      v-if="student.pivot.status == 'Matriculado'"
                      density="compact"
                      variant="text"
                      @click="
                        changeFormativeActionStatus(student, 'Abandonado')
                      "
                    >
                      <v-icon>mdi-exit-run</v-icon>
                      <v-tooltip activator="parent">Abandonado</v-tooltip>
                    </v-btn>
                    <v-btn
                      v-if="!student.pivot.file"
                      icon
                      density="compact"
                      variant="text"
                      @click="openAddDiplomaDialog(student)"
                    >
                      <v-icon>mdi-file-document-plus</v-icon>
                      <v-tooltip activator="parent">Añadir diploma</v-tooltip>
                    </v-btn>
                    <v-btn
                      v-if="student.pivot.file"
                      icon
                      density="compact"
                      variant="text"
                      @click="downloadDiploma(student)"
                    >
                      <v-icon>mdi-file-download</v-icon>
                      <v-tooltip activator="parent"
                        >Descargar diploma</v-tooltip
                      >
                    </v-btn>
                    <v-btn
                      v-if="student.pivot.file"
                      icon
                      density="compact"
                      variant="text"
                      @click="openRemoveDiplomaDialog(student)"
                    >
                      <v-icon>mdi-file-document-remove</v-icon>
                      <v-tooltip activator="parent">Quitar diploma</v-tooltip>
                    </v-btn>

                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      @click="openRemoveStudentFormDialog(student.id)"
                    >
                      <v-icon>mdi-delete</v-icon>
                      <v-tooltip activator="parent">Eliminar</v-tooltip>
                    </v-btn>

                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      v-if="student.course?.modules"
                      @click="toggleModules(student.id)"
                    >
                      <v-icon v-if="showModules[student.id]"
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
              v-if="student.course?.modules"
              v-for="module in student.course.modules"
              v-show="showModules[student.id]"
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
                      student.pivot.status == 'Matriculado' &&
                      module.status == 'Convalidable'
                    "
                    @click="changeModuleStatus(student, module, 'Convalidado')"
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
                      student.pivot.status == 'Matriculado' &&
                      (!module.status || module.status == 'Convalidable')
                    "
                  >
                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      @click="changeModuleStatus(student, module, 'Apto')"
                    >
                      <v-icon>mdi-check</v-icon>
                      <v-tooltip activator="parent">Aprobar</v-tooltip>
                    </v-btn>
                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      @click="changeModuleStatus(student, module, 'No apto')"
                    >
                      <v-icon>mdi-close</v-icon>
                      <v-tooltip activator="parent">Suspender</v-tooltip>
                    </v-btn>
                  </div>
                  <div
                    class="d-flex justify-center h-100 align-center"
                    v-else-if="student.pivot.status == 'Matriculado'"
                  >
                    <v-btn
                      icon
                      density="compact"
                      variant="text"
                      @click="changeModuleStatus(student, module, null)"
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
