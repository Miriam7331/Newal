<script setup>
import FormDialog from "@/Components/FormativeAction/FormDialog.vue"
import DestroyDialog from "@/Components/DestroyDialog.vue"
import RestoreDialog from "@/Components/RestoreDialog.vue"
import DestroyPermanentDialog from "@/Components/DestroyPermanentDialog.vue"
import AddStudentDialog from "@/Components/FormativeAction/AddStudentDialog.vue"
import LoadingOverlay from "@/Components/LoadingOverlay.vue"
import ExpandableText from "@/Components/ExpandableText.vue"
import useTableServer from "@/Composables/useTableServer"
import useDialogs from "@/Composables/useDialogs"
import HistoryDialog from "@/Components/HistoryDialog.vue"
import { exportToExcel } from "@/Utils/excel"
import { usePage } from "@inertiajs/vue3"
import { computed, ref } from "vue"

const page = usePage()

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
  { title: "Código", key: "code", align: "center" },
  { title: "Curso/Módulo", key: "course_or_module_name", align: "center" },
  { title: "Centro", key: "center_name", align: "center" },
  { title: "Entidad", key: "entity_name", align: "center" },
  { title: "Sector", key: "sector_name", align: "center" },
  { title: "Inicio", key: "start_date", align: "center" },
  { title: "Fin", key: "end_date", align: "center" },
  { title: "Horas", key: "schedule", align: "center" },
  { title: "Cupo mín", key: "min_quota", align: "center" },
  { title: "Cupo máx", key: "max_quota", align: "center" },
  { title: "Tipo", key: "type", align: "center" },
  { title: "Islas", key: "islands", align: "center" },
  {
    title: "Acciones",
    key: "actions",
    align: "center",
    sortable: false,
    exportable: false,
  },
]

const modifiedRows = {
  min_quota: (item) => item.students.length + " / " + item.min_quota,
  max_quota: (item) => item.students.length + " / " + item.max_quota,
}

const showInternshipForm = ref(false)

const showAddFormativeActionDialog = ref(false)

const openAddStudentDialog = (newItem) => {
  item.value = newItem
  showAddFormativeActionDialog.value = true
}

const userIsAdmin = computed(() => {
  return page.props?.auth.user.admin
})

const userHasCenters = computed(() => {
  return page.props?.auth.user.centers?.length > 0 || userIsAdmin.value
})

console.log(page.props.project.name)

tableData.search.project_name = page.props.project.name

endPoint.value = "/dashboard/formative-actions"

const showHistoryDialog = ref(false)

const openHistoryDialog = (historyItem) => {
  item.value = historyItem
  showHistoryDialog.value = true
}

const modalMode = ref("FORM")
</script>

<template>
  <add-student-dialog
    :show="showAddFormativeActionDialog"
    @closeDialog="showAddFormativeActionDialog = false"
    @reloadItems="loadItems()"
    :item="item"
    endPoint="/dashboard/students"
  />

  <form-dialog
    :show="showFormDialog"
    @closeDialog="showFormDialog = false"
    @reloadItems="loadItems()"
    :type="formDialogType"
    v-model:item="item"
    :endPoint="endPoint"
    v-model:modalMode="modalMode"
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
  <history-dialog
    :show="showHistoryDialog"
    @closeDialog="showHistoryDialog = false"
    :records="item?.records"
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
            Acciones formativas
            <span v-if="tableData.deleted"> - ELIMINADOS</span>
          </v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <div v-if="!tableData.deleted">
            <v-btn icon="mdi-refresh" @click="resetTable"> </v-btn>
            <v-btn
              v-if="userHasCenters"
              icon="mdi-file-plus-outline"
              @click="openDialog('create')"
            >
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

      <template
        v-for="(modifier, key) in modifiedRows"
        v-slot:[`item.${key}`]="{ item }"
      >
        {{ modifier(item) }}
      </template>

      <template v-slot:item.description="{ item }">
        <expandable-text :text="item.description" />
      </template>

      <template v-slot:item.actions="{ item }">
        <div v-if="!tableData.deleted">
          <v-btn
            v-if="item.records?.length > 0"
            density="compact"
            variant="text"
            icon
            @click="openHistoryDialog(item)"
          >
            <v-icon>mdi-history</v-icon>
            <v-tooltip activator="parent">Historial</v-tooltip>
          </v-btn>
          <v-btn
            density="compact"
            variant="text"
            icon="mdi-pencil"
            @click="openDialog('edit', item)"
          ></v-btn>
          <v-btn
            density="compact"
            variant="text"
            icon="mdi-book-education"
            @click="openAddStudentDialog(item)"
          ></v-btn>
          <v-tooltip text="Ver prácticas" location="top">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                density="compact"
                variant="text"
                icon="mdi-briefcase-edit"
                @click="
                  () => {
                    modalMode = 'PRACTS'
                    openDialog('edit', item)
                  }
                "
              ></v-btn>
            </template>
          </v-tooltip>

          <v-btn
            density="compact"
            variant="text"
            icon="mdi-delete"
            @click="openDialog('destroy', item)"
          ></v-btn>
        </div>
        <div v-if="tableData.deleted">
          <v-btn
            v-if="item.records?.length > 0"
            density="compact"
            variant="text"
            icon
            @click="openHistoryDialog(item)"
          >
            <v-icon>mdi-history</v-icon>
            <v-tooltip activator="parent">Historial</v-tooltip>
          </v-btn>
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
          ></v-btn>
        </div>
      </template>
    </v-data-table-server>
    <loading-overlay v-if="loading" />
  </v-card>
</template>
