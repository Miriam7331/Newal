<script setup>
import FormDialog from "@/Components/Student/FormDialog.vue"
import DestroyDialog from "@/Components/DestroyDialog.vue"
import RestoreDialog from "@/Components/RestoreDialog.vue"
import DestroyPermanentDialog from "@/Components/DestroyPermanentDialog.vue"
import AddFormativeActionDialog from "@/Components/Student/AddFormativeActionDialog.vue"
import LoadingOverlay from "@/Components/LoadingOverlay.vue"
import ExpandableText from "@/Components/ExpandableText.vue"
import HistoryDialog from "@/Components/HistoryDialog.vue"
import useTableServer from "@/Composables/useTableServer"
import useDialogs from "@/Composables/useDialogs"
import { exportToExcel } from "@/Utils/excel"
import { ref, computed, watch } from "vue"
import { usePage } from "@inertiajs/vue3"

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
  { title: "Nombre", key: "name", align: "center" },
  { title: "DNI", key: "dni", align: "center" },
  { title: "Correo", key: "email", align: "center" },
  {
    title: "Acciones formativas",
    key: "formative_actions",
    align: " d-none",
  },
  {
    title: "Acciones",
    key: "actions",
    align: "center",
    sortable: false,
    exportable: false,
  },
]

const modifiedRows = {}

endPoint.value = "/dashboard/students"

const showAddFormativeActionDialog = ref(false)
const showHistoryDialog = ref(false)

const openAddFormativeActionDialog = (newItem) => {
  item.value = newItem
  showAddFormativeActionDialog.value = true
}

const openHistoryDialog = (newItem) => {
  item.value = newItem
  showHistoryDialog.value = true
}

const userIsAdmin = computed(() => {
  return usePage().props?.auth.user.admin
})

const userHasCenters = computed(() => {
  return usePage().props?.auth.user.centers?.length > 0 || userIsAdmin.value
})

const tab = ref(1)

tableData.search = {
  formative_actions: "sin",
}

watch(tab, () => {
  switch (tab.value) {
    case 1:
      tableData.search = {
        formative_actions: "sin",
      }
      loadItems()
      break
    case 2:
      tableData.search = {
        formative_actions: "con",
      }
      loadItems()
      break

    case 3:
      tableData.search = {}
      loadItems()
      break
  }
})
</script>

<template>
  <history-dialog
    :show="showHistoryDialog"
    @closeDialog="showHistoryDialog = false"
    :records="item?.records"
  />
  <add-formative-action-dialog
    :show="showAddFormativeActionDialog"
    @closeDialog="showAddFormativeActionDialog = false"
    @reloadItems="loadItems()"
    :item="item"
    :endPoint="endPoint"
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

  <v-tabs v-model="tab" align-tabs="center">
    <v-tab :value="1">Personas</v-tab>
    <v-tab :value="2">Alumnos</v-tab>
    <v-tab :value="3">Todos</v-tab>
  </v-tabs>

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
            Alumnos
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
            v-if="userHasCenters"
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
              (header) =>
                header.key != 'actions' && header.key != 'formative_actions'
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
        {{ modifier(item[key]) }}
      </template>

      <template v-slot:item.info="{ item }">
        <expandable-text :text="item.info" />
      </template>

      <template v-slot:item.actions="{ item }">
        <div v-if="!tableData.deleted">
          <v-btn
            v-if="item.records?.length > 0"
            density="compact"
            variant="text"
            icon="mdi-history"
            @click="openHistoryDialog(item)"
          >
            <v-icon>mdi-history</v-icon>
            <v-tooltip activator="parent">Historial</v-tooltip>
          </v-btn>
          <v-btn
            density="compact"
            variant="text"
            :href="`/dashboard/students/${item.id}/export-pdf`"
            target="_blank"
            icon="mdi-file-pdf-box"
          >
            <v-icon>mdi-file-pdf-box</v-icon>
            <v-tooltip activator="parent">Exportar a PDF</v-tooltip>
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
            @click="openAddFormativeActionDialog(item)"
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
            v-if="
              item.records?.length > 0
            "
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
