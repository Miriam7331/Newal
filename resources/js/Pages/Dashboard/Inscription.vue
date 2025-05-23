<script setup>
import { Head } from "@inertiajs/vue3"
import FormDialog from "@/Components/Inscription/FormDialog.vue"
import DestroyDialog from "@/Components/DestroyDialog.vue"
import RestoreDialog from "@/Components/RestoreDialog.vue"
import DestroyPermanentDialog from "@/Components/DestroyPermanentDialog.vue"
import LoadingOverlay from "@/Components/LoadingOverlay.vue"
import StudentFormDialog from "@/Components/Student/FormDialog.vue"
import useTableServer from "@/Composables/useTableServer"
import useDialogs from "@/Composables/useDialogs"
import { exportToExcel } from "@/Utils/excel"
import { usePage, router } from "@inertiajs/vue3"
import { computed, ref, onMounted } from "vue"

const {
  endPoint,
  loading,
  itemsPerPageOptions,
  updateItems,
  loadItems,
  tableData,
  resetTable,
} = useTableServer()

const {
  showFormDialog,
  formDialogType,
  showDestroyDialog,
  showRestoreDialog,
  showDestroyPermanentDialog,
  item,
  openDialog,
} = useDialogs()

const headers = [
  { title: "Id", key: "id", align: "center" },
  { title: "Curso", key: "course_name", align: "center" },
  {
    title: "Página",
    key: "web",
    align: "center",
  },
  {
    title: "Isla",
    key: "island",
    align: "center",
  },

  { title: "Nombre", key: "name", align: "center" },
  { title: "Teléfono", key: "phone", align: "center" },
  { title: "Email", key: "email", align: "center" },
  { title: "Publicidad", key: "advertising", align: "center" },
  { title: "Fecha", key: "created_at", align: "center" },
  {
    title: "Acciones",
    key: "actions",
    align: "center",
    sortable: false,
    exportable: false,
  },
]

const modifiedRows = {
  advertising: (value) => (value ? "Si" : "No"),
  created_at: (value) => new Date(value).toLocaleDateString(),
}

const students = computed(() => {
  return usePage().props.students
})

const student = ref({})
const showStudentFormDialog = ref(false)
const studentFormDialogType = ref("create")

const openStudentFormDialog = (type, item) => {
  studentFormDialogType.value = type
  student.value = item
  showStudentFormDialog.value = true
}

const userIsAdmin = computed(() => {
  return usePage().props?.auth.user.admin
})

endPoint.value = "/dashboard/inscriptions"
</script>

<template>
  <Head title="Inscripciones" />

  <student-form-dialog
    :show="showStudentFormDialog"
    @closeDialog="showStudentFormDialog = false"
    @reloadItems="loadItems()"
    :type="studentFormDialogType"
    :item="student"
    :fromInscription="true"
    endPoint="/dashboard/students"
  />

  <form-dialog
    :show="showFormDialog"
    @closeDialog="showFormDialog = false"
    @reloadItems="loadItems()"
    :type="formDialogType"
    v-model:item="item"
    :endPoint="endPoint"
  />
  <destroy-dialog
    :show="showDestroyDialog"
    @closeDialog="showDestroyDialog = false"
    @reloadItems="loadItems()"
    :item="item"
    :endPoint="endPoint"
  />
  <restore-dialog
    :show="showRestoreDialog"
    @closeDialog="showRestoreDialog = false"
    @reloadItems="loadItems()"
    :item="item"
    :endPoint="endPoint"
  />
  <destroy-permanent-dialog
    :show="showDestroyPermanentDialog"
    @closeDialog="showDestroyPermanentDialog = false"
    @reloadItems="loadItems()"
    :item="item"
    :endPoint="endPoint"
  />

  <v-card elevation="6" class="ma-5" variant="outlined">
    <v-data-table-server
      :loading="loading"
      multi-sort
      :items-per-page-options="itemsPerPageOptions"
      v-model:items-per-page="tableData.itemsPerPage"
      v-model:sort-by="tableData.sortBy"
      v-model:page="tableData.page"
      :headers="headers"
      :items-length="tableData.itemsLength"
      :items="tableData.items"
      @update:options="loadItems()"
    >
      <template v-slot:top>
        <v-toolbar :class="{ 'bg-red-lighten-2': tableData.deleted }" flat>
          <v-toolbar-title>
            Inscripciones
            <span v-if="tableData.deleted"> - ELIMINADOS</span>
          </v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <div v-if="!tableData.deleted">
            <v-btn icon="mdi-refresh" @click="resetTable"> </v-btn>
            <v-btn icon="mdi-file-plus-outline" @click="openDialog('create')">
            </v-btn>
            <v-btn
              icon="mdi-file-excel-outline"
              @click="exportToExcel(endPoint, headers, modifiedRows)"
            >
            </v-btn>
          </div>
          <v-btn
            v-if="userIsAdmin"
            :active="tableData.deleted"
            icon="mdi-delete-variant"
            @click="tableData.deleted = !tableData.deleted"
          >
          </v-btn>
        </v-toolbar>
      </template>

      <template v-slot:thead>
        <tr>
          <td
            v-for="header in headers.filter(
              (header) => header.key != 'actions'
            )"
            :key="header.key"
          >
            <v-text-field
              v-model="tableData.search[header.key]"
              @input="updateItems"
              type="text"
              class="px-1"
              variant="underlined"
            ></v-text-field>
          </td>
        </tr>
      </template>

      <template v-slot:item="{ item }">
        <tr>
          <td
            class="text-center"
            :class="
              item.student
                ? item.student.formative_actions.find(
                    (formativeAction) =>
                      formativeAction.pivot.status == 'Matriculado'
                  )
                  ? 'bg-yellow-lighten-3'
                  : 'bg-green-lighten-3'
                : 'bg-white'
            "
            v-for="header in headers"
            :key="header.key"
          >
            <span v-if="typeof modifiedRows[header.key] === 'function'">
              {{ modifiedRows[header.key](item[header.key]) }}
            </span>
            <span v-else-if="header.key === 'actions'">
              <div v-if="item.student">
                <v-btn
                  density="compact"
                  variant="text"
                  icon="mdi-account"
                  @click="openStudentFormDialog('edit', item.student)"
                ></v-btn>
              </div>
              <div v-else>
                <v-btn
                  density="compact"
                  variant="text"
                  icon="mdi-account-plus"
                  @click="openStudentFormDialog('create', item)"
                ></v-btn>
              </div>
              <div v-if="!tableData.deleted">
                <v-btn
                  density="compact"
                  variant="text"
                  icon="mdi-pencil"
                  @click="openDialog('edit', item)"
                ></v-btn>
                <v-btn
                  density="compact"
                  variant="text"
                  icon="mdi-delete"
                  @click="openDialog('destroy', item)"
                ></v-btn>
              </div>
              <div v-if="tableData.deleted">
                <v-btn
                  density="compact"
                  variant="text"
                  icon="mdi-restore"
                  @click="openDialog('restore', item)"
                ></v-btn>
                <v-btn
                  density="compact"
                  variant="text"
                  icon="mdi-delete-alert"
                  @click="openDialog('destroyPermanent', item)"
                ></v-btn></div
            ></span>
            <span v-else>{{ item[header.key] }}</span>
          </td>
        </tr>
      </template>
    </v-data-table-server>
    <loading-overlay v-if="loading" />
  </v-card>
</template>
